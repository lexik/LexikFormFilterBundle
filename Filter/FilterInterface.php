<?php

namespace Lexik\Bundle\FormFilterBundle\Filter;

/**
 * Some filter can implement this interface to apply the filter condition to the query.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
interface FilterInterface
{
    /**
     * Add condition(s) to the query builder for the current type.
     *
     * @param object $filterBuilder
     * @param object $expr
     * @param string $field
     * @param array  $values
     */
    public function applyFilter($filterBuilder, $expr, $field, array $values);
}
