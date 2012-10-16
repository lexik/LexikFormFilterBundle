<?php

namespace Lexik\Bundle\FormFilterBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Get alias and expression builder for filter builder
 */
class PrepareEvent extends Event
{
    /**
     * @var object
     */
    protected $filterBuilder;

    /**
     * Alias
     *
     * @var string
     */
    protected $alias;

    /**
     * @var object
     */
    protected $expr;

    /**
     * Construct
     *
     * @param object $filterBuilder
     */
    public function __construct($filterBuilder)
    {
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * Get filter builder
     *
     * @return object
     */
    public function getFilterBuilder()
    {
        return $this->filterBuilder;
    }

    /**
     * Set alias
     *
     * @param string $alias
     *
     * @return PrepareEvent
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * Get alias
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Set expr
     *
     * @param object $expr
     *
     * @return PrepareEvent
     */
    public function setExpr($expr)
    {
        $this->expr = $expr;

        return $this;
    }

    /**
     * Get expr
     *
     * @return object
     */
    public function getExpr()
    {
        return $this->expr;
    }
}
