<?php
namespace Lexik\Bundle\FormFilterBundle\Filter\ORM;

use Doctrine\ORM\QueryBuilder;

use Lexik\Bundle\FormFilterBundle\Event\GetFilterEvent;
use Lexik\Bundle\FormFilterBundle\Filter\Extension\Type\FilterTypeSharedableInterface;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderExecuterInterface;

abstract class ORMFilterTypeSharedable implements FilterTypeSharedableInterface
{
    /**
     * On filter get event
     *
     * @param GetFilterEvent $event
     *
     * @deprecated Deprecated since version 2.0, to be removed in 2.1. Use EventDispatcher instead.
     */
    public function onFilterGet(GetFilterEvent $event)
    {
        if ($event->getFilterBuilder() instanceof QueryBuilder && $event->getName() === $this->getName()) {
            $event->setFilter($this);
            $event->stopPropagation();
        }
    }

    /**
     * Get filter type name
     *
     * @return string
     *
     * @deprecated Deprecated since version 2.0, to be removed in 2.1. Use EventDispatcher instead.
     */
    abstract public function getName();
}
