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
    /**
     * Возвращает название класса сущности
     * @return string
     */
    public function getSourceClassName() : string
    {
        return User::class;
    }

    public function getColumns() : array
    {

    }

    public function getProperties() : array
    {

    }

    public function getRelations() : array
    {
        return [
            [
                /** TODO Реализовать в виде коллекции обектов типа OneToMany, OneToOne, ManyToMany */
                /** One To Many */
                'refEntity' => Picture::class,
                'refProperty' => 'user_id',
                'sourceProperty' => 'id',
                'propertySourceCall' => 'pictures'
            ]
        ];
    }

    public function getMapping() : array
    {
        return [
            'id' => 'ID',
            'name' => 'NAME'
        ];
    }
}

class QueryBuilder
{
    public function getSomeData() : array
    {
        return [
            'ID' => 123,
            'NAME' => 'John',
            'LAST_NAME' => 'Franko',
            'AGE' => 18
        ];
    }
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
        return new ArrayIterator([
            [
                'file_id' => 123,
                'path' => '/var/www/html/123.jpg',
                'mime_type' => 'image/jpeg',
                'extension' => 'jpeg'
            ],
            [
                'file_id' => 124,
                'path' => '/var/www/html/124.png',
                'mime_type' => 'image/png',
                'extension' => 'png'
            ]
        ]);
    }
}

class Hydrator
{
    public static function getEntity(MetaDataEntity $metaData, QueryBuilder $queryBuilder) : object
    {
        $reflectionClass = new ReflectionClass($metaData->getSourceClassName());
        $ormEntity = $reflectionClass->newInstanceWithoutConstructor();
        $dbData = $queryBuilder->getSomeData();

        foreach ($metaData->getMapping() as $propertyName => $columnName) {
            $propertyReflector = $reflectionClass->getProperty($propertyName);
            $propertyReflector->setAccessible(true);
            $propertyReflector->setValue($ormEntity, $dbData[$columnName]);
        }

        if ($associations = $metaData->getRelations()) {
            foreach ($associations as $association) {
                $propertyReflector = $reflectionClass->getProperty($association['propertySourceCall']);
                $propertyReflector->setAccessible(true);
                $propertyReflector->setValue($ormEntity, new LazyCollection());
            }
        }

        return $ormEntity;
    }
}

class Picture
{

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


$res = Hydrator::getEntity(new MetaDataEntity(), new QueryBuilder());

foreach ($res->getPictures() as $picture) {
    var_dump($picture);
}