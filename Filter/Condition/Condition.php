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
     * @var array<string, mixed>
     *
     * array(
     *     'param_name_1' => $value,
     *     'param_name_2  => ExpressionParameterValue($value, $type = null),
     *     'param_name_3  => array($value, $type), // can be deprecated, as it interferes with array values (link for IN() expressions)
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
