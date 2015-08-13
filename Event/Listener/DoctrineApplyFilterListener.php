<?php

namespace Lexik\Bundle\FormFilterBundle\Event\Listener;

use Doctrine\ORM\Query\Expr\Composite;
use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Lexik\Bundle\FormFilterBundle\Event\ApplyFilterConditionEvent;
use Lexik\Bundle\FormFilterBundle\Filter\Condition\ConditionInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Condition\ConditionNodeInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Doctrine\DoctrineQueryBuilderAdapter;

/**
 * Add filter conditions on a Doctrine ORM or DBAL query builder.
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
     * @var string
     */
    private $whereMethod;

    /**
     * @param string $whereMethod
     */
    public function __construct($whereMethod)
    {
        $this->whereMethod = empty($whereMethod) ? 'where' : sprintf('%sWhere', strtolower($whereMethod));
    }

    /**
     * @param ApplyFilterConditionEvent $event
     */
    public function onApplyFilterCondition(ApplyFilterConditionEvent $event)
    {
        $qbAdapter = new DoctrineQueryBuilderAdapter($event->getQueryBuilder());
        $conditionBuilder = $event->getConditionBuilder();

        $this->parameters = array();
        $expression = $this->computeExpression($qbAdapter, $conditionBuilder->getRoot());

        if (null !== $expression && $expression->count()) {
            $qbAdapter->{$this->whereMethod}($expression);

            foreach ($this->parameters as $name => $value) {
                if (is_array($value)) {
                    list($value, $type) = $value;
                    $qbAdapter->setParameter($name, $value, $type);
                } else {
                    $qbAdapter->setParameter($name, $value);
                }
            }
        }
    }

    /**
     * @param DoctrineQueryBuilderAdapter $queryBuilder
     * @param ConditionNodeInterface      $node
     * @return Composite|CompositeExpression|null
     */
    protected function computeExpression(DoctrineQueryBuilderAdapter $queryBuilder, ConditionNodeInterface $node)
    {
        if (count($node->getFields()) == 0 && count($node->getChildren()) == 0) {
            return null;
        }

        $method = ($node->getOperator() == ConditionNodeInterface::EXPR_AND) ? 'andX' : 'orX';

        $expression = $queryBuilder->{$method}();

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
