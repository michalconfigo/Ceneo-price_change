<?php

namespace Ceneo\Domain\Model;

class Category {

    private $id;
    private $name;
    private $parent;
    private $children;
    private $attributes;
    private $keyAttributes;
    private $optionalAttributes;

    /**
     * @return mixed
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param mixed $attributes
     */
    public function setAttributes($attributes): void
    {
        $this->attributes = $attributes;
    }

    /**
     * @param mixed $attribute
     */
    public function addAttribute($attribute): void
    {
        $this->attributes[] = $attribute;
    }

	/**
	 * @return mixed
	 */
	public function getKeyAttributes()
	{
		return $this->keyAttributes;
	}

	/**
	 * @param mixed $keyAttributes
	 */
	public function setKeyAttributes($keyAttributes): void
	{
		$this->keyAttributes = $keyAttributes;
	}

	/**
	 * @param mixed $keyAttribute
	 */
	public function addKeyAttribute($keyAttribute): void
	{
		$this->keyAttributes[] = $keyAttribute;
	}

	/**
	 * @return mixed
	 */
	public function getOptionalAttributes()
	{
		return $this->optionalAttributes;
	}

	/**
	 * @param mixed $optionalAttributes
	 */
	public function setOptionalAttributes($optionalAttributes): void
	{
		$this->optionalAttributes = $optionalAttributes;
	}

	/**
	 * @param mixed $optionalAttribute
	 */
	public function addOptionalAttribute($optionalAttribute): void
	{
		$this->optionalAttributes[] = $optionalAttribute;
	}

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
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
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param mixed $parent
     */
    public function setParent($parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @return mixed
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param mixed $children
     */
    public function setChildren($children): void
    {
        $this->children = $children;
    }

    /**
     * @param mixed $child
     */
    public function addChild($child): void
    {
        $this->children[] = $child;
    }

}
