<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Extension\Type;

use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderExecuterInterface;

/**
 * Some filter type can implement this interface to apply the filter to the query.
 */
interface FilterTypeSharedableInterface
{
    /**
     * Add condition(s) to the query builder for the current type.
     *
     * @param  FilterBuilderExecuterInterface $qbe
     */
    public function addShared(FilterBuilderExecuterInterface $qbe);
}
