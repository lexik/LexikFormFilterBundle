<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Condition;

/**
 * Defined the operator to use for a list of fields.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class ConditionNode implements \ArrayAccess, ConditionNodeInterface
{
    /**
     * @var string
     */
    private $operator;

    /**
     * @var ConditionNodeInterface
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

    /**
     * @param string                 $operator
     * @param ConditionNodeInterface $parent
     */
    public function __construct($operator, ConditionNodeInterface $parent = null)
    {
        $this->operator = $operator;
        $this->parent = $parent;
        $this->children = array();
        $this->fields = array();
    }

    /**
     * {@inheritDoc}
     */
    public function orX($name)
    {
        $node = new static(self::EXPR_OR, $this);

        $this->children[$name] = $node;

        return $node;
    }

    /**
     * {@inheritDoc}
     */
    public function andX($name)
    {
        $node = new static(self::EXPR_AND, $this);

        $this->children[$name] = $node;

        return $node;
    }

    /**
     * {@inheritDoc}
     */
    public function end()
    {
        return $this->parent;
    }

    /**
     * {@inheritDoc}
     */
    public function field($name)
    {
        $this->fields[$name] = null;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * {@inheritDoc}
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * {@inheritDoc}
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
