<?php

namespace Lexik\Bundle\FormFilterBundle\Filter;

use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;

/**
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class FilterBuilderExecuter implements FilterBuilderExecuterInterface
{
    /**
     * @var QueryInterface
     */
    protected $filterQuery;

    /**
     * @var string
     */
    protected $alias;

    /**
     * @var array
     */
    protected $parts;

    /**
     * Construct.
     *
     * @param QueryInterface    $filterQuery
     * @param string            $alias
     * @param RelationsAliasBag $parts
     */
    public function __construct(QueryInterface $filterQuery, $alias, RelationsAliasBag $parts)
    {
        $this->filterQuery = $filterQuery;
        $this->alias       = $alias;
        $this->parts       = $parts;
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
    public function getFilterQuery()
    {
        return $this->filterQuery;
    }

    /**
     * {@inheritdoc}
     */
    public function addOnce($join, $alias, \Closure $callback = null)
    {
        if ($this->parts->has($join)) {
            return null;
        }

        $this->parts->add($join, $alias);

        if (!$callback instanceof \Closure) {
            return;
        }

        return $callback($this->filterQuery->getQueryBuilder(), $this->alias, $alias, $this->filterQuery->getExpr());
    }
}
