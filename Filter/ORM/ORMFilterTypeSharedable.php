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
     */
    abstract public function getName();
}
