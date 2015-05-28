<?php

namespace Lexik\Bundle\FormFilterBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;

/**
 * Get alias and expression builder for filter builder
 *
 * @author Jeremy Barthe <j.barthe@lexik.fr>
 */
class PrepareEvent extends Event
{
    /**
     * @var object $queryBuilder
     */
    private $queryBuilder;

    /**
     * @var object $filterQuery
     */
    private $filterQuery;

    /**
     * Construct
     *
     * @param object $queryBuilder
     */
    public function __construct($queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * Get query builder
     *
     * @return object
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    /**
     * Set filter query
     *
     * @param QueryInterface $filterQuery
     */
    public function setFilterQuery(QueryInterface $filterQuery)
    {
        $this->filterQuery = $filterQuery;
    }

    /**
     * Get filter query
     *
     * @return QueryInterface
     */
    public function getFilterQuery()
    {
        return $this->filterQuery;
    }
}
