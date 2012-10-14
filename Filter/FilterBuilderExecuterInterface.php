<?php

namespace Lexik\Bundle\FormFilterBundle\Filter;

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
