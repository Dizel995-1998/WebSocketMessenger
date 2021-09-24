<?php

use Lib\Database\Hydrator\Hydrator;
use Lib\Database\MetaData\MetaDataEntity;
use Lib\Database\Query\QueryBuilder;
use \Lib\Database\Relations\OneToMany;
use Lib\Database\Column\IntegerColumn;
use Lib\Database\Column\StringColumn;

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


class Picture
{
    /**
     * @StringColumn({"name":"FILE_ID"})
     * @var string
     */
    protected $file_id;

    /**
     * @StringColumn({"name":"PATH"})
     * @var string
     */
    protected $path;

    /**
     * @StringColumn({"name":"MIME_TYPE"})
     * @var string
     */
    protected $mime_type;

    /**
     * @StringColumn({"name":"EXTENSION"})
     * @var string
     */
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
    /**
     * @StringColumn({"name":"NAME"})
     * @var string
     */
    protected string $name;

    /**
     * @StringColumn({"name":"LAST_NAME"})
     * @var string
     */
    protected string $last_name;

    /**
     * @IntegerColumn({"name":"ID"})
     * @var int|null
     */
    protected ?int $id;

    /**
     * @OneToMany({"sourceColumn":"ID", "sourceTable":"users", "targetColumn":"user_id", "targetTable":"pictures", "targetClassName":"Picture"})
     * @var
     */
    protected $pictures;

    public function getPictures()
    {
        return $this->pictures;
    }
}


$arMockUser = [
    'NAME' => 'John',
    'LAST_NAME' => 'FRank',
    'ID' => 123
];

$res = Hydrator::getEntity(new MetaDataEntity(User::class), (new QueryBuilder($arMockUser))->exec());
var_dump($res);

foreach ($res->getPictures() as $picture) {
    var_dump($picture);
}