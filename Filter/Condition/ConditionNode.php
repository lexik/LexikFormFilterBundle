<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Condition;

/**
 * Define the operator to use for a list of fields.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class ConditionNode implements ConditionNodeInterface
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
    public function orX()
    {
        $node = new static(self::EXPR_OR, $this);

        $this->children[] = $node;

        return $node;
    }

    /**
     * {@inheritDoc}
     */
    public function andX()
    {
        $node = new static(self::EXPR_AND, $this);

        $this->children[] = $node;

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
     * Set the condition for the given field name.
     *
     * @param string             $name
     * @param ConditionInterface $condition
     * @return bool
     */
    public function setCondition($name, ConditionInterface $condition)
    {
        if (array_key_exists($name, $this->fields)) {
            $this->fields[$name] = $condition;

            return true;
        }

        $i = 0;
        $end = count($this->children);
        $set = false;

        while ($i < $end && !$set) {
            $set = $this->children[$i]->setCondition($name, $condition);
            $i++;
        }

        return $set;
    }
}
