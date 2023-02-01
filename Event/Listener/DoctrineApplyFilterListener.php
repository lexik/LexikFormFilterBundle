<?php

namespace Lexik\Bundle\FormFilterBundle\Event\Listener;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Doctrine\ORM\Query\Expr\Composite;
use Lexik\Bundle\FormFilterBundle\Event\ApplyFilterConditionEvent;
use Lexik\Bundle\FormFilterBundle\Filter\Condition\ConditionInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Condition\ConditionNodeInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Doctrine\DoctrineQueryBuilderAdapter;
use Lexik\Bundle\FormFilterBundle\Filter\Doctrine\Expression\ExpressionParameterValue;

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
                if ($value instanceof ExpressionParameterValue) {
                    $qbAdapter->setParameter($name, $value->value, $value->type);
                } elseif (is_array($value) && count($value) === 2 && Type::hasType($value[1])) {
                    // that could be deprecated in favor of the ExpressionParameterValue class above
                    // as it is kind of a hacky solution for a legacy architectural decision
                    $qbAdapter->setParameter($name, $value[0], $value[1]);
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
