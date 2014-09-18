<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Condition;

class ConditionNode implements \ArrayAccess
{
    const EXPR_AND = 'and';
    const EXPR_OR  = 'or';

    /**
     * @var string
     */
    private $operator;

    /**
     * @var ConditionNode
     */
    private $parent;

    /**
     * @var array
     */
    private $children;

    /**
     * @var array
     */
    private $fields;

    public function __construct($operator, $parent)
    {
        $this->operator = $operator;
        $this->parent = $parent;
        $this->children = array();
        $this->fields = array();
    }

    public function orX($name)
    {
        $node = new static(self::EXPR_OR, $this);

        $this->children[$name] = $node;

        return $node;
    }

    public function andX($name)
    {
        $node = new static(self::EXPR_AND, $this);

        $this->children[$name] = $node;

        return $node;
    }

    public function end()
    {
        return $this->parent;
    }

    public function field($name)
    {
        $this->fields[$name] = null;

        return $this;
    }

    /**
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @return array
     */
    public function getChildren()
    {
        return $this->children;
    }

    public function getChild($name)
    {
        return isset($this->children[$name]) ? $this->children[$name] : null;
    }

    public function getValue($field)
    {
        return isset($this->fields[$field]) ? $this->fields[$field] : null;
    }

    /**
     * {@inheritDoc}
     */
    public function offsetExists($offset)
    {
        if (array_key_exists($offset, $this->fields)) {
            return true;
        }

        if (array_key_exists($offset, $this->children)) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function offsetGet($offset)
    {
        if (array_key_exists($offset, $this->fields)) {
            return $this->fields[$offset];
        }

        if (array_key_exists($offset, $this->children)) {
            return $this->children[$offset];
        }

        return null;
    }

    /**
    /**
     * {@inheritDoc}
     */
    public function offsetSet($offset, $value)
    {
        if (array_key_exists($offset, $this->fields)) {
            $this->fields[$offset] = $value;
        }

        if (array_key_exists($offset, $this->children)) {
            $this->children[$offset] = $value;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function offsetUnset($offset)
    {
        if (array_key_exists($offset, $this->fields)) {
            unset($this->fields[$offset]);
        }

        if (array_key_exists($offset, $this->children)) {
            unset($this->children[$offset]);
        }
    }
}
