<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Doctrine;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Lexik\Bundle\FormFilterBundle\Filter\Condition\Condition;
use Lexik\Bundle\FormFilterBundle\Filter\Doctrine\Expression\ExpressionBuilder;
use Lexik\Bundle\FormFilterBundle\Filter\Doctrine\Expression\ORMExpressionBuilder;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;

/**
 * @author Jeremy Barthe <j.barthe@lexik.fr>
 */
class ORMQuery implements QueryInterface
{
    /**
     * @var QueryBuilder $queryBuilder
     */
    private $queryBuilder;

    /**
     * @var ORMExpressionBuilder $expr
     */
    private $expressionBuilder;

    /**
     * Constructor.
     *
     * @param QueryBuilder $queryBuilder
     * @param boolean      $forceCaseInsensitivity
     * @param string|null  $encoding
     */
    public function __construct(QueryBuilder $queryBuilder, $forceCaseInsensitivity = false, $encoding = null)
    {
        $this->queryBuilder = $queryBuilder;
        $this->expressionBuilder = new ORMExpressionBuilder(
            $this->queryBuilder->expr(),
            $forceCaseInsensitivity,
            $encoding
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getEventPartName(): string
    {
        return 'orm';
    }

    /**
     * {@inheritDoc}
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    /**
     * {@inheritDoc}
     */
    public function createCondition($expression, array $parameters = [])
    {
        return new Condition($expression, $parameters);
    }

    /**
     * Get QueryBuilder expr.
     *
     * @return Expr
     */
    public function getExpr()
    {
        return $this->queryBuilder->expr();
    }

    /**
     * {@inheritDoc}
     */
    public function getRootAlias()
    {
        $aliases = $this->queryBuilder->getRootAliases();

        return $aliases[0] ?? '';
    }

    /**
     * {@inheritDoc}
     */
    public function hasJoinAlias($joinAlias): bool
    {
        $joinParts = $this->queryBuilder->getDQLPart('join');

        /* @var \Doctrine\ORM\Query\Expr\Join $join */
        foreach ($joinParts as $rootAlias => $joins) {
            foreach ($joins as $join) {
                if ($join->getAlias() === $joinAlias) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get expr class.
     *
     * @return ExpressionBuilder
     */
    public function getExpressionBuilder()
    {
        return $this->expressionBuilder;
    }
}
