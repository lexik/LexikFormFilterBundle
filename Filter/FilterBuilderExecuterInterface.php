<?php

namespace Lexik\Bundle\FormFilterBundle\Filter;

use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;

/**
 * @author Cédric Girard <c.girard@lexik.fr>
 */
interface FilterBuilderExecuterInterface
{
    /**
     * Add a join.
     *
     * @param string   $join
     * @param string   $alias
     * @param \Closure $callback
     */
    public function addOnce($join, $alias, \Closure $callback = null);

    /**
     * @return string
     */
    public function getAlias();

    /**
     * @return RelationsAliasBag
     */
    public function getParts();

    /**
     * @return QueryInterface
     */
    public function getFilterQuery();
}
