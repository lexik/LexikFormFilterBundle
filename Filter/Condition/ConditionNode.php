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

    public function orX()
    {
        $node = new static(self::EXPR_OR, $this);

        $this->children[] = $node;

        return $node;
    }

    public function andX()
    {
        $node = new static(self::EXPR_AND, $this);

        $this->children[] = $node;

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

    /**
     * {@inheritDoc}
     */
    public function offsetExists($offset)
    {
        if (array_key_exists($offset, $this->fields)) {
            return true;
        }

        $exists = false;
        $i = 0;
        $end = count($this->children);

        while ($i<$end && !$exists) {
            $exists = isset($this->children[$i][$offset]);
            $i++;
        }

        return $exists;
    }

    /**
     * {@inheritDoc}
     */
    public function offsetGet($offset)
    {
        if (array_key_exists($offset, $this->fields)) {
            return $this->fields[$offset];
        }

        $value = null;
        $i = 0;
        $end = count($this->children);

        while ($i<$end && null === $value) {
            if (isset($this->children[$i][$offset])) {
                $value = $this->children[$i][$offset];
            }
            $i++;
        }

        return $value;
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

        $set = false;
        $i = 0;
        $end = count($this->children);

        while ($i<$end && !$set) {
            if (isset($this->children[$i][$offset])) {
                $this->children[$i][$offset] = $value;
                $set = true;
            }
            $i++;
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

        $deleted = false;
        $i = 0;
        $end = count($this->children);

        while ($i<$end && null && !$deleted) {
            if (isset($this->children[$i][$offset])) {
                unset($this->children[$i][$offset]);
            }
            $i++;
        }
    }
}
