<?php

namespace Service\VirtualClass;

class ClassBuilder
{
    public function __construct(protected VirtualClass $virtualClass)
    {

    }

    public function echoClass() : string
    {
        $output = 'class ' . $this->virtualClass->getName() . ' ';

        if ($this->virtualClass->isExtended()) {
            $output .= 'extends ' . $this->virtualClass->getExtendsFromClass();
        }

        $output .= ' { ' . PHP_EOL;

        foreach ($this->virtualClass->getProperties() as $property) {
            $output .= $property->getModifier() . ' ' . ($property->getType() ?: '') . '$' . $property->getName();
            $output .= ($property->hasDefaultValue() ? ('=' . $property->getDefaultValue()) : ';') . PHP_EOL;
        }

        foreach ($this->virtualClass->getMethods() as $method) {
            $output .= $method->getModified() . ' function ' . $method->getName() . '(';
            foreach ($method->getArgs() as $arg) {
                $output .= ($arg->getType() ?: '') . ' $' . $arg->getName() . ',';
            }

            $output .= ') {' . PHP_EOL;

        }

        // todo реализовать механизм имплементации интерфейса

        return $output;
    }
}