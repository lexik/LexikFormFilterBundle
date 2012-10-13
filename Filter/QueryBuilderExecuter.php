<?php

namespace Lexik\Bundle\FormFilterBundle\Filter;

use Doctrine\ORM\QueryBuilder;

use Lexik\Bundle\FormFilterBundle\Filter\Expr;

class QueryBuilderExecuter implements QueryBuilderExecuterInterface
{
    /**
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var string
     */
    protected $alias;

    /**
     * @var Expr
     */
    protected $expr;

    /**
     * @var array
     */
    protected $parts;

    /**
     * Construct.
     *
     * @param QueryBuilder $queryBuilder
     * @param string $alias
     * @param Expr $expr
     * @param array $parts
     */
    public function __construct(QueryBuilder $queryBuilder, $alias, Expr $expr, array & $parts = array())
    {
        $this->queryBuilder = $queryBuilder;
        $this->alias        = $alias;
        $this->expr         = $expr;
        $this->parts        = & $parts;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * {@inheritdoc}
     */
    public function getParts()
    {
        return $this->parts;

    }

    /**
     * {@inheritdoc}
     */
    public function addOnce($join, $alias, \Closure $callback)
    {
        if (isset($this->parts[$join])) {
            return null;
        }

        $this->parts[$join] = $alias;

        return $callback($this->queryBuilder, $this->alias, $alias, $this->expr);
    }
}
