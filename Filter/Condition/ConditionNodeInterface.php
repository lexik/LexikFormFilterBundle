<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Condition;

/**
 * @author Cédric Girard <c.girard@lexik.fr>
 */
interface ConditionNodeInterface
{
    const EXPR_AND = 'and';
    const EXPR_OR  = 'or';

    /**
     * Start a OR sub expression.
     *
     * @return static
     */
    public function orX();

    /**
     * Start a AND sub expression.
     *
     * @return static
     */
    public function andX();

    /**
     * Returns the parent node.
     *
     * @return ConditionNode
     */
    public function end();

    /**
     * Add a field in the current expression.
     *
     * @param string $name
     * @return $this
     */
    public function field($name);

    /**
     * @return string
     */
    public function getOperator();

    /**
     * @return array
     */
    public function getFields();

    /**
     * @return array
     */
    public function getChildren();
}
