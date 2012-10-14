<?php

namespace Lexik\Bundle\FormFilterBundle\Event;

use Symfony\Component\EventDispatcher\Event;

use Lexik\Bundle\FormFilterBundle\Filter\Extension\Type\FilterTypeInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Extension\Type\FilterTypeSharedableInterface;

/**
 * Get filter for filter builder
 */
class GetFilterEvent extends Event
{
    /**
     * @var object
     */
    protected $filterBuilder;

    /**
     * filter
     *
     * @var string
     */
    protected $filter;

    /**
     * Filter type name
     *
     * @var string
     */
    protected $name;

    /**
     * Construct
     *
     * @param object $filterBuilder filter builder
     * @param string $name filter type name
     */
    public function __construct($filterBuilder, $name)
    {
        $this->filterBuilder = $filterBuilder;
        $this->name          = $name;
    }

    /**
     * Get filter type name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
     * Set filter
     *
     * @param FilterTypeInterface|FilterTypeSharedableInterface $filter
     *
     * @return GetFilterEvent
     *
     * @throws \InvalidArgumentException If filter is not instance of FilterTypeInterface|FilterTypeSharedableInterface
     */
    public function setFilter($filter)
    {
        if (!$filter instanceof FilterTypeSharedableInterface && !$filter instanceof FilterTypeInterface) {
            throw new \InvalidArgumentException(sprintf(
                '$filter must be an instance of FilterTypeSharedableInterface or FilterTypeInterface, %s given',
                get_class($filter)
            ));
        }

        $this->filter = $filter;

        return $this;
    }

    /**
     * Get filter
     *
     * @return FilterTypeInterface|FilterTypeSharedableInterface
     */
    public function getFilter()
    {
        return $this->filter;
    }
}
