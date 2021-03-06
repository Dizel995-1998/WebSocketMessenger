<?php

namespace Lib\Database\Drivers;


use Lib\Database\Column\BaseColumn;
use Lib\Database\Drivers\Exceptions\CannotConnectToDataBase;
use Lib\Database\Drivers\Interfaces\IConnection;
use Lib\Database\Migration\Schema;
use Lib\Database\Migration\Table;
use PDO;

/**
 * fixme: экранирование SQL должно осуществляться на уровне драйвера
 */
class PdoDriver extends BaseDriver
{
    const DB_SQL_TYPE = 'mysql';

    public function __construct(
        protected string $user,
        protected string $password,
        protected string $host,
        protected string $dbName,
        protected int $port
    ) {

    }

    /**
     * @throws CannotConnectToDataBase
     */
    protected function getConnection() : PDO
    {
        static $connection;

        if (!isset($connection)) {
            try {
                $conn = sprintf('%s:host=%s;dbname=%s', self::DB_SQL_TYPE, $this->host, $this->dbName);
                $connection = new PDO($conn, $this->user, $this->password);
                $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $connection->query("set names 'utf8'");
            } catch (\PDOException $PDOException) {
                throw new CannotConnectToDataBase($PDOException->getMessage());
            }
        }

        return $connection;
    }

    public function exec(string $sql): bool
    {
        // todo: обернуть исключения PDO в свои
        return (bool) $this->getConnection()->exec($sql);
    }

    public function query(string $sql): DbResult
    {
        // todo: обернуть исключения PDO в свои
        $db = $this->getConnection()->query($sql);

        return $db ?
            new DbResult($db->fetchAll(PDO::FETCH_ASSOC)) :
            new DbResult([]);
    }

    public function getLastInsertedId(): null|string
    {
        return $this->getConnection()->lastInsertId() ?: null;
    }

    protected function getTablesName(): array
    {
        $res = [];
        $tables = $this->getConnection()->query('SHOW TABLES')->fetchAll();

        foreach ($tables as $table) {
            $res[] = current($table);
        }

        return $res;
    }

    /**
     * @param array $tables
     * @return <string, array>
     * @throws CannotConnectToDataBase
     */
    protected function getColumnsByTables(array $tables) : array
    {
        $res = [];

        // todo: добавить экранирование

        $sql = "SELECT COLUMN_NAME, TABLE_NAME, COLUMN_TYPE
                        FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = '{$this->dbName}' AND TABLE_NAME IN (" . implode(',', array_map(function ($item) {
                return is_string($item) ? "'" . $item . "'" : $item;
            }, $tables)) . ')';

        $columns = $this->getConnection()->query($sql);

        while ($tmp = $columns->fetch(PDO::FETCH_ASSOC)) {
            $res[$tmp['TABLE_NAME']][] = ['name' => $tmp['COLUMN_NAME'], 'type' => $tmp['COLUMN_TYPE']];
        }

        return $res;
    }

    public function getSchema(): Schema
    {
        $schema = new Schema();
        $tablesNames = $this->getTablesName();
        $dbColumns = $this->getColumnsByTables($tablesNames);
        $preparedColumns = [];

        // fixme: дикий хардкод =)
        $convertColumnToObj = function (string $columnName, string $columnsType, string $tableName) : \Lib\Database\Column\BaseColumn {
            return str_contains($columnsType, 'int') ?
                new \Lib\Database\Column\IntegerColumn($columnName, $tableName) :
                new \Lib\Database\Column\StringColumn($columnName, $tableName);
        };

        foreach ($dbColumns as $tableName => $columns) {
            foreach ($columns as $column) {
                $preparedColumns[$tableName][] = $convertColumnToObj($column['name'], $column['type'], $tableName);
            }
        }

        foreach ($tablesNames as $tableName) {
            $table = new Table($tableName, $preparedColumns[$tableName]);
            $schema->addTable($table);
        }

        return $schema;
    }
}