<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Extension\Type;

/**
 * Some filter type can implement this interface to apply the filter to the query.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
interface FilterTypeInterface
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
