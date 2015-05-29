<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Doctrine;

use Lexik\Bundle\FormFilterBundle\Filter\Condition\Condition;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Doctrine\Expression\DBALExpressionBuilder;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * @author Jeremy Barthe <j.barthe@lexik.fr>
 */
class DBALQuery implements QueryInterface
{
    /**
     * @var QueryBuilder $queryBuilder
     */
    private $queryBuilder;

    /**
     * @var DBALExpressionBuilder $expr
     */
    private $expressionBuilder;

    /**
     * Constructor.
     *
     * @param QueryBuilder $queryBuilder
     * @param boolean      $forceCaseInsensitivity
     */
    public function __construct(QueryBuilder $queryBuilder, $forceCaseInsensitivity = false)
    {
        $this->queryBuilder      = $queryBuilder;
        $this->expressionBuilder = new DBALExpressionBuilder(
            $this->queryBuilder->expr(),
            $forceCaseInsensitivity
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getEventPartName()
    {
        return 'dbal';
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
    public function createCondition($expression, array $parameters = array())
    {
        return new Condition($expression, $parameters);
    }

    /**
     * Get QueryBuilder expr.
     *
     * @return \Doctrine\DBAL\Query\Expression\ExpressionBuilder
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
        $from = $this->queryBuilder->getQueryPart('from');

        return $from[0]['alias'];
    }

    /**
     * {@inheritDoc}
     */
    public function hasJoinAlias($joinAlias)
    {
        $joinParts = $this->queryBuilder->getQueryPart('join');

        foreach ($joinParts as $rootAlias => $joins) {
            foreach ($joins as $join) {
                if ($join['joinAlias'] === $joinAlias) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get expr class.
     *
     * @return \Lexik\Bundle\FormFilterBundle\Filter\Doctrine\Expression\ExpressionBuilder
     */
    public function getExpressionBuilder()
    {
        return $this->expressionBuilder;
    }
}
