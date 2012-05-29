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

    public function __construct(QueryBuilder $queryBuilder, $alias, Expr $expr, array & $parts = array())
    {
        $this->queryBuilder = $queryBuilder;
        $this->alias        = $alias;
        $this->expr         = $expr;
        $this->parts        = & $parts;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    public function getParts()
    {
        return $this->parts;

    }

    public function addOnce($tag, \Callback $callback)
    {
        if (isset($this->parts[$tag])) {
            return null;
        }

        $this->parts[$tag] = true;

        return $callback($this->queryBuilder, $this->alias, $tag, $this->expr);
    }
}
