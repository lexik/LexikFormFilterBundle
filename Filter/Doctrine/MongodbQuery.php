<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Doctrine;

use Doctrine\ODM\MongoDB\Query\Builder;
use Lexik\Bundle\FormFilterBundle\Filter\Condition\Condition;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;

/**
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class MongodbQuery implements QueryInterface
{
    /**
     * @var Builder
     */
    private $queryBuilder;

    /**
     * @var bool
     */
    private $forceCaseInsensitivity;

    /**
     * Constructor.
     *
     * @param Builder $queryBuilder
     * @param boolean $forceCaseInsensitivity
     */
    public function __construct(Builder $queryBuilder, $forceCaseInsensitivity = false)
    {
        $this->queryBuilder = $queryBuilder;
        $this->forceCaseInsensitivity = $forceCaseInsensitivity;
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
    public function getEventPartName()
    {
        return 'mongodb';
    }

    /**
     * {@inheritDoc}
     */
    public function createCondition($expression, array $parameters = array())
    {
        return new Condition($expression, $parameters);
    }

    /**
     * @return \Doctrine\ODM\MongoDB\Query\Expr
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
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function hasJoinAlias($joinAlias)
    {
        return null;
    }
}
