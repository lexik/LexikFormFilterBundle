<?php

namespace Lexik\Bundle\FormFilterBundle\Event\Listener;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Composite;

use Lexik\Bundle\FormFilterBundle\Event\ApplyFilterConditionEvent;
use Lexik\Bundle\FormFilterBundle\Filter\Condition\Condition;
use Lexik\Bundle\FormFilterBundle\Filter\Condition\ConditionNode;

class DoctrineORMApplyFilterListener
{
    /**
     * @var array
     */
    private $parameters;

    public function onApplyFilterCondition(ApplyFilterConditionEvent $event)
    {
        $qb = $event->getQueryBuilder();
        $conditionBuilder = $event->getConditionBuilder();

        $this->parameters = array();
        $expression = $this->computeExpression($qb, $conditionBuilder->getRoot());

        if (null !== $expression && $expression->count()) {
            $qb->where($expression);

            foreach ($this->parameters as $name => $value) {
                if (is_array($value)) {
                    list($value, $type) = $value;
                    $qb->setParameter($name, $value, $type);
                } else {
                    $qb->setParameter($name, $value);
                }
            }
        }
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param ConditionNode $node
     * @return Composite|null
     */
    protected function computeExpression(QueryBuilder $queryBuilder, ConditionNode $node)
    {
        if (count($node->getFields()) == 0 && count($node->getChildren()) == 0) {
            return null;
        }

        $method = ($node->getOperator() == ConditionNode::EXPR_AND) ? 'andX' : 'orX';

        $expression = $queryBuilder->expr()->{$method}();

        foreach ($node->getFields() as $condition) {
            if (null !== $condition) {
                /** @var Condition $condition */
                $expression->add($condition->getExpression());

                $this->parameters = array_merge($this->parameters, $condition->getParameters());
            }
        }

        foreach ($node->getChildren() as $child) {
            $subExpr = $this->computeExpression($queryBuilder, $child);

            if (null !== $subExpr && $subExpr->count()) {
                $expression->add($subExpr);
            }
        }

        return $expression->count() ? $expression : null;
    }
}
