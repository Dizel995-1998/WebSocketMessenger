<?php

namespace Service\NestedSets;

class Section
{
    protected int $id;

    protected ?int $level;

    protected ?int $left;

    protected ?int $right;

    public function __construct(int $id, ?int $level = null, ?int $left = null, ?int $right = null)
    {
        $this->id = $id;
        $this->level = $level;
        $this->left = $left;
        $this->right = $right;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function setLevel(int $level) : self
    {
        $this->level = $level;
        return $this;
    }

    public function setLeftMargin(int $margin) : self
    {
        $this->left = $margin;
        return $this;
    }

    public function setRightMargin(int $margin) : self
    {
        $this->right = $margin;
        return $this;
    }

    public function getLevel() : ?int
    {
        return $this->level;
    }

    public function getLeftMargin() : ?int
    {
        return $this->left;
    }

    public function getRightMargin() : ?int
    {
        return $this->right;
    }
}
