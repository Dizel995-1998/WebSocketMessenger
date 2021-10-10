<?php

namespace Service\NestedSets;

use InvalidArgumentException;
use RuntimeException;

class NestedSets
{
    const ROOT_LEVEL = 1;

    const ROOT_LEFT_MARGIN = 1;

    /**
     * @var Section[]
     */
    protected array $sections;

    public function __construct(array $sections = [])
    {
        $this->sections = $sections;
    }

    protected function getMaxRightMargin() : int
    {
        if (!$this->sections) {
            throw new RuntimeException('Sections are empty');
        }

        $max = 0;

        foreach ($this->sections as $section) {
            if ($section->getRightMargin() > $max) {
                $max = $section->getRightMargin();
            }
        }

        return $max;
    }

    /**
     * @param int $sectionId
     * @return Section|null
     */
    protected function findSection(int $sectionId) : ?Section
    {
        foreach ($this->sections as $item) {
            if ($item->getId() == $sectionId) {
                return $item;
            }
        }

        return null;
    }

    protected function updateParentsSection(int $leftMargin) : void
    {
        foreach ($this->sections as $section) {
            // fixme насколько поминаю уже не нужна
            if ($section->getLevel() == self::ROOT_LEVEL) {
                $section->setRightMargin($section->getRightMargin() + 2);
                continue;
            }

            if ($section->getLeftMargin() < $leftMargin && $section->getRightMargin() >= $leftMargin) {
                $section->setRightMargin($section->getRightMargin() + 2);
            }

            if ($section->getLeftMargin() >= $leftMargin && $section->getRightMargin() < $this->getMaxRightMargin()) {
                $section->setLeftMargin($section->getLeftMargin() + 2);
                $section->setRightMargin($section->getRightMargin() + 2);
            }
        }
    }

    public function addNode(int $parentId, Section $node) : self
    {
        if (!$parentNode = $this->findSection($parentId)) {
            throw new RuntimeException('Parent node was not found in inside collection');
        }

        if ($node->getLevel() === null) {
            throw new InvalidArgumentException('Node must have level');
        }

        if ($node->getLevel() != ($parentNode->getLevel() + 1)) {
            throw new InvalidArgumentException('node must be more down than parent section');
        }

        $node->setLeftMargin($parentNode->getRightMargin());
        $node->setRightMargin($parentNode->getRightMargin() + 1);
        $this->updateParentsSection($node->getLeftMargin());

        $this->sections[] = $node;
        return $this;
    }

    public function addRoot(Section $rootSection) : self
    {
        if ($rootSection->getLevel() !== self::ROOT_LEVEL) {
            throw new InvalidArgumentException('Root section must have level = 0, given ' . $rootSection->getLevel());
        }

        $rootSection->setLeftMargin(self::ROOT_LEFT_MARGIN);

        $this->sections[] = $rootSection;
        return $this;
    }

    /**
     * Левый ключ ВСЕГДА меньше правого;
     * Наименьший левый ключ ВСЕГДА равен 1;
     * Наибольший правый ключ ВСЕГДА равен двойному числу узлов;
     * Разница между правым и левым ключом ВСЕГДА нечетное число;
     * Если уровень узла нечетное число то тогда левый ключ ВСЕГДА нечетное число, то же самое и для четных чисел;
     * Ключи ВСЕГДА уникальны, вне зависимости от того правый он или левый;
     */
    protected function validateSections() : void
    {
        foreach ($this->sections as $section) {
            if ($section->getLeftMargin() >= $section->getRightMargin()) {
                throw new RuntimeException('Left margin cant be more than right margin');
            }

            // ...
        }
    }

    /**
     * Сохраняет коллекцию разделов в БД
     * @return array
     */
    public function save() : array
    {
        $this->validateSections();
        return $this->sections;
    }
}