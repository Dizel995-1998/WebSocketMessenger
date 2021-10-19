<?php

namespace Lib\Database\Relations;

use Rakit\Validation\Validator;

/**
 * todo ввести параметр обязательности связи
 */
abstract class BaseRelation
{
    const RULES = [
        'source_table' => 'required|alpha_dash',
        'source_column' => 'required|alpha_dash',
        'target_table' => 'required|alpha_dash',
        'target_column' => 'required|alpha_dash'
    ];

    protected Validator $validator;

    protected string $sourceTable;
    protected string $sourceColumn;
    protected string $targetTable;
    protected string $targetColumn;

    public function __construct(array $relationData)
    {
        $this->validator = new Validator();
        $this->validate($relationData);

        $this->sourceTable = $relationData['source_table'];
        $this->sourceColumn = $relationData['source_column'];
        $this->targetTable = $relationData['target_table'];
        $this->targetColumn = $relationData['target_column'];
    }

    protected function validate(array $data) : void
    {
        $validation = $this->validator->validate($data, self::RULES);

        if (!$validation->passes()) {
            throw new \RuntimeException(print_r($validation->errors()->toArray(), true));
        }
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