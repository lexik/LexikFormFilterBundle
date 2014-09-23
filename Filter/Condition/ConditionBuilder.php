<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Condition;

/**
 * Used to build a condition nodes hierarchy to defined condition pattern.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class ConditionBuilder implements ConditionBuilderInterface
{
    /**
     * @var ConditionNodeInterface
     */
    private $root;

    /**
     * {@inheritdoc}
     */
    public function root($operator)
    {
        $operator = strtolower($operator);

        if (!in_array($operator, array(ConditionNodeInterface::EXPR_AND, ConditionNodeInterface::EXPR_OR))) {
            throw new \RuntimeException(sprintf('Invalid operator "%s", allowed values: and, or', $operator));
        }

        $this->root = new ConditionNode($operator, null);

        return $this->root;
    }

    /**
     * {@inheritdoc}
     */
    public function addCondition(ConditionInterface $condition)
    {
        if (false === $this->root->setCondition($condition->getName(), $condition)) {
            throw new \RuntimeException(sprintf('Can\'t set condition object for: "%s"', $condition->getName()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRoot()
    {
        return $this->root;
    }
}
