<?php

namespace Lexik\Bundle\FormFilterBundle\Event\Listener;

use Doctrine\MongoDB\Query\Builder;
use Doctrine\MongoDB\Query\Expr;
use Lexik\Bundle\FormFilterBundle\Event\ApplyFilterConditionEvent;
use Lexik\Bundle\FormFilterBundle\Filter\Condition\ConditionInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Condition\ConditionNodeInterface;

/**
 * Add filter conditions on a Doctrine MongoDB query builder.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class DoctrineMongoDBApplyFilterListener
{
    /**
     * @param ApplyFilterConditionEvent $event
     */
    public function onApplyFilterCondition(ApplyFilterConditionEvent $event)
    {
        /** @var Builder $qb */
        $qb = $event->getQueryBuilder();
        $conditionBuilder = $event->getConditionBuilder();

        $this->computeExpression($qb, $conditionBuilder->getRoot());
    }

    /**
     * @param Builder                $queryBuilder
     * @param ConditionNodeInterface $node
     * @param Expr                   $expr
     * @return null
     */
    protected function computeExpression(Builder $queryBuilder, ConditionNodeInterface $node, Expr $expr = null)
    {
        if (count($node->getFields()) == 0 && count($node->getChildren()) == 0) {
            return null;
        }

        $method = ($node->getOperator() === ConditionNodeInterface::EXPR_AND) ? 'addAnd' : 'addOr';

        $expression = (null === $expr) ? $queryBuilder : $expr;
        $count = 0;

        foreach ($node->getFields() as $condition) {
            if (null !== $condition) {
                /** @var ConditionInterface $condition */
                $expression->{$method}($condition->getExpression());
                $count++;
            }
        }

        foreach ($node->getChildren() as $child) {
            $subExpr = $queryBuilder->expr();
            $subCount = $this->computeExpression($queryBuilder, $child, $subExpr);

            if ($subCount > 0) {
                $expression->{$method}($subExpr);
                $count++;
            }
        }

        return $count;
    }
}
