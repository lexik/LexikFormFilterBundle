<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Condition;

/**
 * @author Cédric Girard <c.girard@lexik.fr>
 */
interface ConditionBuilderInterface
{
    /**
     * Create the root node.
     *
     * @param string $operator
     * @return ConditionNodeInterface
     */
    public function root($operator);

    /**
     * Add a condition to a node.
     *
     * @param ConditionInterface $condition
     */
    public function addCondition(ConditionInterface $condition);

    /**
     * Returns the root node.
     *
     * @return ConditionNodeInterface
     */
    public function getRoot();
}
