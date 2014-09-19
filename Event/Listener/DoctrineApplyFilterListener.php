<?php

namespace Lexik\Bundle\FormFilterBundle\Event\Listener;

use Doctrine\ORM\QueryBuilder as ORMQueryBuilder;
use Doctrine\ORM\Query\Expr\Composite;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Doctrine\DBAL\Query\QueryBuilder as DBALQueryBuilder;

use Lexik\Bundle\FormFilterBundle\Event\ApplyFilterConditionEvent;
use Lexik\Bundle\FormFilterBundle\Filter\Condition\ConditionInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Condition\ConditionNodeInterface;

/**
 * Add filter conditions on a Doctrine ORM query builder.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class DoctrineApplyFilterListener
{
    /**
     * @var array
     */
    private $parameters;

    /**
     * @param ApplyFilterConditionEvent $event
     */
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
     * @param ORMQueryBuilder|DBALQueryBuilder $queryBuilder
     * @param ConditionNodeInterface           $node
     * @return Composite|CompositeExpression|null
     */
    protected function computeExpression($queryBuilder, ConditionNodeInterface $node)
    {
        if (count($node->getFields()) == 0 && count($node->getChildren()) == 0) {
            return null;
        }

        $method = ($node->getOperator() == ConditionNodeInterface::EXPR_AND) ? 'andX' : 'orX';

        $expression = $queryBuilder->expr()->{$method}();

        foreach ($node->getFields() as $condition) {
            if (null !== $condition) {
                /** @var ConditionInterface $condition */
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
