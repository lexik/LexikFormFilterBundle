<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Extension\Type;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;

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
     * @param Doctrine\ORM\QueryBuilder $queryBuilder
     * @param Expr $e
     * @param string $field
     * @param array $values
     */
    public function applyFilter(QueryBuilder $queryBuilder, Expr $e, $field, $values);

    /**
     * Return service id used to transforme values
     *
     * @return string
     */
    public function getTransformerId();
}