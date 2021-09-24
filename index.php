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
    protected EntityReader $reader;

    /**
     * @param string $className
     * @param EntityReader $reader
     */
    public function __construct(string $className, EntityReader $reader)
    {
        $this->className = $className;
        $this->reader = $reader;
    }

    /**
     * Возвращает название класса сущности
     * @return string
     */
    public function getSourceClassName(): string
    {
        return $this->className;
    }

    public function getColumns(): array
    {

    }

    public function getProperties(): array
    {

    }

    /**
     * @return <string, BaseRelation>[]
     */
    public function getRelations(): array
    {
        return $this->reader->getEntityRelations();
    }

    public function getMapping(): array
    {
        return $this->reader->getEntityMapping();
    }
}

class QueryBuilder
{
    protected array $arMockData;

    public function __construct(array $arMockData)
    {
        $this->arMockData = $arMockData;
    }

    public function getSomeData(): array
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
    protected string $targetClassName;
    // todo скорее всего не нужно
    protected string $sourceClassName;

    /**
     * TODO можно заменить строковые значениям - двумя обьектами колонками, и в обьект колонки ввести принадлежность таблице
     * @param string $sourceColumn
     * @param string $sourceTable
     * @param string $targetColumn
     * @param string $targetTable
     * @param string $targetClassName
     */
    public function __construct(
        string $sourceColumn,
        string $sourceTable,
        string $targetColumn,
        string $targetTable,
        string $targetClassName
    )
    {
        $this->sourceColumn = $sourceColumn;
        $this->sourceTable = $sourceTable;
        $this->targetColumn = $targetColumn;
        $this->targetTable = $targetTable;
        $this->targetClassName = $targetClassName;
    }

    public function getTargetClassName(): string
    {
        return $this->targetClassName;
    }

    public function getSourceTable(): string
    {
        return $this->sourceTable;
    }

    public function getTargetTable(): string
    {
        return $this->targetTable;
    }

    public function getSourceColumn(): string
    {
        return $this->sourceColumn;
    }

    public function getTargetColumn(): string
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
    public function getSourceTable(): string
    {
        return 'pictures';
    }

    public function getJoinType(): string
    {
        return 'INNER';
    }

    public function getSourceColumn(): string
    {
        return 'user_id';
    }

    public function getTargetColumn(): string
    {
        return 'id';
    }
}

class Property
{
    protected string $name;
    protected ?string $type = null;

    public function __construct(string $name, ?string $type = null)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }
}

class LazyCollection implements IteratorAggregate
{
    protected BaseRelation $relation;

    public function __construct(BaseRelation $relation)
    {
        $this->relation = $relation;
    }

    public function getIterator()
    {
        // todo не думаю что запрос должен строится отсюда, должно идти обращение к сервисному слою для работы с БД для построения JOIN запроса
        $sql = sprintf('SELECT * FROM %s JOIN %s ON %s = %s',
            $this->relation->getSourceTable(),
            $this->relation->getTargetTable(),
            $this->relation->getSourceColumn(),
            $this->relation->getTargetColumn()
        );


        // Данные полученные якобы от БД
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

        // Структура которую мы получили из ридера сущностей
        $pictureMapping = [
            new PropertyMap('path', new IntegerColumn('PATH')),
            new PropertyMap('mime_type', new IntegerColumn('MIME_TYPE')),
            new PropertyMap('file_id', new IntegerColumn('FILE_ID')),
            new PropertyMap('extension', new IntegerColumn('EXTENSION'))
        ];

        $arIterable = [];

        foreach ($arMock as $mock) {
            $arIterable[] = Hydrator::getEntity(new MetaDataEntity($this->relation->getTargetClassName(), $pictureMapping), new QueryBuilder($mock));
        }

        return new ArrayIterator($arIterable);
    }
}

class Hydrator
{
    public static function getEntity(MetaDataEntity $metaData, QueryBuilder $queryBuilder): object
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
                $propertyReflector->setValue($ormEntity, new LazyCollection($association));
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
    protected Property $property;

    protected ?BaseColumn $column = null;

    protected ?BaseRelation $relation = null;

    public function __construct(Property $property, BaseColumn $column = null)
    {
        $this->property = $property;
        $this->column = $column;
    }

    public function setRelation(BaseRelation $relation)
    {
        $this->relation = $relation;
    }

    public function getRelation(): BaseRelation
    {
        return $this->relation;
    }

    public function isRelation(): bool
    {
        return isset($this->relation);
    }

    public function getPropertyName(): string
    {
        return $this->property->getName();
    }

    public function getColumn(): BaseColumn
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

    public function isPrimaryKey(): bool
    {
        return $this->isPrimaryKey;
    }

    public function getName(): string
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
    public function setValue($value): self
    {
        $this->columnValue = $value;
        return $this;
    }

    abstract public function getType(): string;
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

    public function getPath(): string
    {
        return $this->path;
    }

    public function getMimeType(): string
    {
        return $this->mime_type;
    }

    public function getFileId()
    {
        return $this->file_id;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }
}

class User
{
    protected string $name = '';
    protected int $id = 0;
    protected $pictures;

    public function __construct()
    {
        $z = 0;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPictures()
    {
        return $this->pictures;
    }
}

class EntityReader
{
    protected array $entityMapping = [];

    protected array $relations = [];

    /**
     * TODO тестовый метод, дропнуть
     */
    public function setEntityMapping(array $mapping): self
    {
        $this->entityMapping = $mapping;
        return $this;
    }

    /**
     * TODO тестовый метод, удалить
     */
    public function setEntityRelations(array $relations): self
    {
        $this->relations = $relations;
        return $this;
    }

    public function getEntityMapping(): array
    {
        return $this->entityMapping;
    }

    public function getEntityRelations(): array
    {
        return $this->relations;
    }
}

$arMock = [
    'ID' => 123,
    'NAME' => 'John',
    'LAST_NAME' => 'Franko',
    'AGE' => 18
];

$userReader = (new EntityReader())
    ->setEntityMapping([
        new PropertyMap(new Property('id'), new IntegerColumn('ID')),
        new PropertyMap(new Property('name'), new IntegerColumn('NAME'))
    ])
    ->setEntityRelations([
        'pictures' => new OneToMany('id', 'user_table', 'user_id', 'pictures_table', Picture::class)
    ]);

$userMetaData = new MetaDataEntity(User::class, $userReader);

$res = Hydrator::getEntity($userMetaData, new QueryBuilder($arMock));

var_dump($res);

foreach ($res->getPictures() as $picture) {
    var_dump($picture);
}