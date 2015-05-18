<?php

namespace Lexik\Bundle\FormFilterBundle\Filter;

/**
 * @author Cédric Girard <c.girard@lexik.fr>
 */
interface FilterBuilderExecuterInterface
{
    /**
     * Add a join.
     *
     * @param string $join
     * @param string $alias
     * @param \Closure $callback
     */
    public function addOnce($join, $alias, \Closure $callback);

    /**
     * @return string
     */
    public function getAlias();

    /**
     * @return array
     */
    public function getParts();
}
