<?php

use Lib\Container\Container;
use Lib\Database\QueryBuilderUpdater;

require_once 'vendor/autoload.php';

ini_set('xdebug.var_display_max_depth', 10);
ini_set('xdebug.var_display_max_children', 256);
ini_set('xdebug.var_display_max_data', 1024);

//$httpRouter = new \Lib\Router\HttpRouter(Container::getService(Lib\Request\Request::class));
//$routeCollection = new \Lib\RouteCollection\RouteCollection();
//$routeCollection->addRoutes(require_once 'src/Routes/v1/routes.php');
//$response = $httpRouter->run($routeCollection);
//
//http_response_code($response->getStatusCode());
//
//foreach ($response->getHeaders() as $headerName => $headerValue) {
//    header($headerName . ': ' . current($headerValue));
//}
//
//echo $response->getBody();

class MetaDataEntity
{
    protected string $className;
    protected array $relations = [];
    protected array $mapping = [];

    /**
     * @param string $className
     * @param PropertyMap[] $mapping
     */
    public function __construct(string $className, array $mapping)
    {
        $this->className = $className;
        $this->mapping = $mapping;
    }

    /**
     * Возвращает название класса сущности
     * @return string
     */
    public function getSourceClassName() : string
    {
        return $this->className;
    }

    public function getColumns() : array
    {

    }

    public function getProperties() : array
    {

    }

    /**
     * @param <string, BaseRelation>[] $relations
     */
    public function setRelations(array $relations)
    {
        $this->relations = $relations;
    }

    /**
     * @return <string, BaseRelation>[]
     */
    public function getRelations() : array
    {
        return $this->relations;
    }

    public function getMapping() : array
    {
        return $this->mapping;
    }
}

class QueryBuilder
{
    protected array $arMockData;

    public function __construct(array $arMockData)
    {
        $this->arMockData = $arMockData;
    }

    public function getSomeData() : array
    {
        return $this->arMockData;
    }
}

abstract class BaseRelation
{
    protected string $sourceTable;
    protected string $targetTable;
    protected string $sourceColumn;
    protected string $targetColumn;

    public function __construct(
        string $sourceColumn,
        string $sourceTable,
        string $targetColumn,
        string $targetTable
    ) {
        $this->sourceColumn = $sourceColumn;
        $this->sourceTable = $sourceTable;
        $this->targetColumn = $targetColumn;
        $this->targetTable = $targetTable;
    }

    public function getSourceTable() : string
    {
        return $this->sourceTable;
    }

    public function getTargetTable() : string
    {
        return $this->targetTable;
    }

    public function getSourceColumn() : string
    {
        return $this->sourceColumn;
    }

    public function getTargetColumn() : string
    {
        return $this->targetColumn;
    }
}

class OneToOne extends BaseRelation
{

}

class OneToMany extends BaseRelation
{

}

class MetaDataRelation
{
    public function getSourceTable() : string
    {
        return 'pictures';
    }

    public function getJoinType() : string
    {
        return 'INNER';
    }

    public function getSourceColumn() : string
    {
        return 'user_id';
    }

    public function getTargetColumn() : string
    {
        return 'id';
    }
}

class LazyCollection implements IteratorAggregate
{
//    public function __construct(MetaDataRelation $metaDataRelation)
//    {
//
//    }

    public function getIterator()
    {
        $arMock = [
            [
                'MIME_TYPE' => 'image/jpeg',
                'PATH' => '/var/www/bitrix/12.jpg',
                'FILE_ID' => 123,
                'EXTENSION' => 'jpeg'
            ],
            [
                'MIME_TYPE' => 'image/jpg',
                'PATH' => '/var/www/bitrix/995.jpg',
                'FILE_ID' => 124,
                'EXTENSION' => 'png'
            ]
        ];

        $pictureMapping = [
            new PropertyMap('path', new IntegerColumn('PATH')),
            new PropertyMap('mime_type', new IntegerColumn('MIME_TYPE')),
            new PropertyMap('file_id', new IntegerColumn('FILE_ID')),
            new PropertyMap('extension', new IntegerColumn('EXTENSION'))
        ];

        $arIterable = [];

        foreach ($arMock as $mock) {
            $arIterable[] = Hydrator::getEntity(new MetaDataEntity(Picture::class, $pictureMapping), new QueryBuilder($mock));
        }

        return new ArrayIterator($arIterable);
    }
}

class Hydrator
{
    public static function getEntity(MetaDataEntity $metaData, QueryBuilder $queryBuilder) : object
    {
        $reflectionClass = new ReflectionClass($metaData->getSourceClassName());
        $ormEntity = $reflectionClass->newInstanceWithoutConstructor();
        $dbData = $queryBuilder->getSomeData();

        foreach ($metaData->getMapping() as $propertyMap) {
            $propertyReflector = $reflectionClass->getProperty($propertyMap->getPropertyName());
            $propertyReflector->setAccessible(true);
            $propertyReflector->setValue($ormEntity, $dbData[$propertyMap->getColumn()->getName()]);
        }

        if ($associations = $metaData->getRelations()) {
            foreach ($associations as $propertyName => $association) {
                $propertyReflector = $reflectionClass->getProperty($propertyName);
                $propertyReflector->setAccessible(true);
                $propertyReflector->setValue($ormEntity, new LazyCollection());
            }
        }

        return $ormEntity;
    }
}

/**
 * TODO может стоить реализовать отдельный класс для свойств? class Property
 */
class PropertyMap
{
    protected string $propertyName;

    protected ?BaseColumn $column = null;

    protected ?BaseRelation $relation = null;

    public function __construct(string $propertyName, BaseColumn $column = null)
    {
        $this->propertyName = $propertyName;
        $this->column = $column;
    }

    public function setRelation(BaseRelation $relation)
    {
        $this->relation = $relation;
    }

    public function getRelation() : BaseRelation
    {
        return $this->relation;
    }

    public function isRelation() : bool
    {
        return isset($this->relation);
    }

    public function getPropertyName() : string
    {
        return $this->propertyName;
    }

    public function getColumn() : BaseColumn
    {
        return $this->column;
    }
}

abstract class BaseColumn
{
    /**
     * @var string
     */
    protected string $columnName;

    /**
     * @var mixed
     */
    protected $columnValue;

    /**
     * @var bool
     */
    protected bool $isPrimaryKey;

    /**
     * TODO правильно ли в колонке хранить значение, ведь значение есть в колонки у строки, а не у абстрактной колонки
     * @param string $columnName
     * @param null $columnValue
     * @param bool $isPrimaryKey
     */
    public function __construct(string $columnName, $columnValue = null, bool $isPrimaryKey = false)
    {
        $this->columnName = $columnName;
        $this->columnValue = $columnValue;
        $this->isPrimaryKey = $isPrimaryKey;
    }

    public function isPrimaryKey() : bool
    {
        return $this->isPrimaryKey;
    }

    public function getName() : string
    {
        return $this->columnName;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
       return $this->columnValue;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function setValue($value) : self
    {
        $this->columnValue = $value;
        return $this;
    }

    abstract public function getType() : string;
}

class IntegerColumn extends BaseColumn
{
    public function getType(): string
    {
        return 'integer';
    }
}

class StringColumn extends BaseColumn
{
    public function getType(): string
    {
        return 'string';
    }
}

class Picture
{
    protected $file_id;
    protected $path;
    protected $mime_type;
    protected $extension;

    public function getPath() : string
    {
        return $this->path;
    }

    public function getMimeType() : string
    {
        return $this->mime_type;
    }

    public function getFileId()
    {
        return $this->file_id;
    }

    public function getExtension() : string
    {
        return $this->extension;
    }
}

class User
{
    protected string $name = '';
    protected int $id = 0;
    protected $pictures;

    public function __constructor()
    {
        $z = 0;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getPictures()
    {
        return $this->pictures;
    }
}

$arMock = [
    'ID' => 123,
    'NAME' => 'John',
    'LAST_NAME' => 'Franko',
    'AGE' => 18
];

$userMapping = [
    new PropertyMap('id', new IntegerColumn('ID')),
    new PropertyMap('name', new IntegerColumn('NAME'))
];

$userMetaData = new MetaDataEntity(User::class, $userMapping);
$userMetaData->setRelations([
    'pictures' => new OneToMany('id', 'user_table', 'user_id', 'pictures_table')
]);

$res = Hydrator::getEntity($userMetaData, new QueryBuilder($arMock));

var_dump($res);

foreach ($res->getPictures() as $picture) {
    var_dump($picture);
}