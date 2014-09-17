<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Condition;

use Symfony\Component\PropertyAccess\PropertyAccess;

class ConditionBuilder
{
    /**
     * @var ConditionNode
     */
    private $root;

    public function root($operator)
    {
        $operator = strtolower($operator);

        if (!in_array($operator, array(ConditionNode::EXPR_AND, ConditionNode::EXPR_OR))) {
            throw new \RuntimeException(sprintf('Invalid operator "%s", allowed values: and, or', $operator));
        }

        $this->root = new ConditionNode($operator, null);

        return $this->root;
    }

    public function addCondition(Condition $condition)
    {
        PropertyAccess::createPropertyAccessor()->setValue(
            $this->root,
            $condition->getPath(),
            $condition
        );
    }

    /**
     * @return ConditionNode
     */
    public function getRoot()
    {
        return $this->root;
    }
}
