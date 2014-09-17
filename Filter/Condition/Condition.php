<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Condition;

/**
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class Condition
{
    /**
     * @var string
     */
    public $path;

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
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $expression
     */
    public function setExpression($expression)
    {
        $this->expression = $expression;
    }

    /**
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}
