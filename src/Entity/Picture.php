<?php

namespace Entity;

/**
 * @ORM\Table({"name":"pictures"})
 */
class Picture
{
    /**
     * @ORM\IntegerColumn({"name":"ID"})
     * @var
     */
    protected $id;

    /**
     * @ORM\IntegerColumn({"name":"USER_ID"})
     * @var string
     */
    protected $file_id;

    /**
     * @ORM\StringColumn({"name":"FILE_NAME"})
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