<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Condition;

/**
 * Represent a filter condition to ba added on a query builder.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class Condition implements ConditionInterface
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    private $expression;

    /**
     * @var array
     *
     * array(
     *     'param_name_1' => $value,
     *     'param_nema_2  => array($value, $type),
     * )
     */
    private $parameters;

    /**
     * @param string $expression
     * @param array  $parameters
     */
    public function __construct($expression, array $parameters = array())
    {
        $this->expression = $expression;
        $this->parameters = $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setExpression($expression)
    {
        $this->expression = $expression;
    }

    /**
     * {@inheritdoc}
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * {@inheritdoc}
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}
