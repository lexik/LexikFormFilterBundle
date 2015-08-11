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
    private $whereMethod;

    /**
     * @param string $whereMethod
     */
    public function __construct($whereMethod)
    {
        $whereMethod = strtolower($whereMethod);

        $this->whereMethod = (null === $whereMethod || 'and' === $whereMethod) ? 'addAnd' : 'addOr';
    }

    /**
     * @param ApplyFilterConditionEvent $event
     */
    public function onApplyFilterCondition(ApplyFilterConditionEvent $event)
    {
        /** @var Builder $qb */
        $qb = $event->getQueryBuilder();
        $conditionBuilder = $event->getConditionBuilder();

        $this->parameters = array();
        $expression = $this->computeExpression($qb, $conditionBuilder->getRoot());

        if (null !== $expression) {
            $qb->{$this->whereMethod}($expression);
        }
    }

    /**
     * @param Builder                $queryBuilder
     * @param ConditionNodeInterface $node
     * @return Expr
     */
    protected function computeExpression(Builder $queryBuilder, ConditionNodeInterface $node)
    {
        if (count($node->getFields()) == 0 && count($node->getChildren()) == 0) {
            return null;
        }

        $method = ($node->getOperator() == ConditionNodeInterface::EXPR_AND) ? 'addAnd' : 'addOr';

        $expression = $queryBuilder->expr(); // create a new expression object
        $expressionsCount = 0;

        foreach ($node->getFields() as $condition) {
            if (null !== $condition) {
                /** @var ConditionInterface $condition */
                $expression->{$method}($condition->getExpression());
                $expressionsCount++;
            }
        }

        foreach ($node->getChildren() as $child) {
            $subExpr = $this->computeExpression($queryBuilder, $child);

            if (null !== $subExpr) {
                $expression->{$method}($subExpr);
                $expressionsCount++;
            }
        }

        return ($expressionsCount > 0) ? $expression : null;
    }
}
