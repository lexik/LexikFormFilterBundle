<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Condition;

use Symfony\Component\PropertyAccess\PropertyAccess;

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
        PropertyAccess::createPropertyAccessor()->setValue(
            $this->root,
            $condition->getPath(),
            $condition
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getRoot()
    {
        return $this->root;
    }
}
