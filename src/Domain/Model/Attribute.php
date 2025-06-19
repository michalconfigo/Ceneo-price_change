<?php

namespace Ceneo\Domain\Model;

class Attribute {

    private $categoryId;
    private $name;
    private $isKey;
    private $exampleValue;

    /**
     * @return mixed
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * @param mixed $categoryId
     */
    public function setCategoryId($categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getIsKey()
    {
        return $this->isKey;
    }

    /**
     * @param mixed $isKey
     */
    public function setIsKey($isKey): void
    {
        $this->isKey = $isKey;
    }

    /**
     * @return mixed
     */
    public function getExampleValue()
    {
        return $this->exampleValue;
    }

    /**
     * @param mixed $exampleValue
     */
    public function setExampleValue($exampleValue): void
    {
        $this->exampleValue = $exampleValue;
    }
}
