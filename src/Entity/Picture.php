<?php

namespace Entity;

/**
 * @ORM\Table({"name":"pictures"})
 */
class Picture implements \JsonSerializable
{
    /**
     * @IntegerColumn ({"name":"id","isPrimaryKey":true})
     * @var
     */
    protected $id;

    /**
     * @IntegerColumn({"name":"user_id"})
     * @var
     */
    protected $userId;

    /**
     * @StringColumn({"name":"file_name"})
     * @var string
     */
    protected $fileName;

    /**
     * @StringColumn ({"name":"sub_dir"})
     * @var string
     */
    protected $subDir;


    public function getId() : ?int
    {
        return $this->id;
    }

    public function getFileName() : ?string
    {
        return $this->fileName;
    }

    public function getSubDir() : ?string
    {
        return $this->subDir;
    }

    public function setSubDir(string $subDir) : self
    {
        $this->subDir = $subDir;
        return $this;
    }

    public function setFileName(string $fileName) : self
    {
        $this->fileName = $fileName;
        return $this;
    }

    public function jsonSerialize()
    {
        $res = [];

        $reflection = new \ReflectionClass(static::class);

        foreach ($reflection->getProperties(\ReflectionProperty::IS_PROTECTED) as $property) {
            $property->setAccessible(true);
            $res[$property->getName()] = $property->getValue($this);
        }

        return $res;
    }
}