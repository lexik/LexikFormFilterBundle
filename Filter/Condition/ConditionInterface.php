<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Condition;

/**
 * @author Cédric Girard <c.girard@lexik.fr>
 */
interface ConditionInterface
{
    /**
     * Set the name to map the condition on the ConditionBuilder instance.
     *
     * @param string $name
     */
    public function setName($name);

    /**
     * Get condition path.
     *
     * @return string
     */
    public function getName();

    /**
     * Set the condition expression.
     *
     * @param string $expression
     */
    public function setExpression($expression);

    /**
     * Get the condition expression.
     *
     * @return string
     */
    public function getExpression();

    /**
     * Set expression parameters.
     *
     * @param array $parameters
     */
    public function setParameters(array $parameters);

    /**
     * Get expression parameters.
     *
     * @return array
     */
    public function getParameters();
}
