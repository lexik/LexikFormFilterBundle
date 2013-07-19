<?php
namespace Lexik\Bundle\FormFilterBundle\Filter\ORM;

use Doctrine\ORM\QueryBuilder;

use Lexik\Bundle\FormFilterBundle\Filter\ORM\Expr;
use Lexik\Bundle\FormFilterBundle\Event\GetFilterEvent;
use Lexik\Bundle\FormFilterBundle\Filter\FilterInterface;

abstract class ORMFilter implements FilterInterface
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

    /**
     * Apply filter: orm version of signature
     *
     * @param QueryBuilder $filterBuilder
     * @param Expr         $expr
     * @param string       $field
     * @param array        $values
     *
     * @return void
     *
     * @deprecated Deprecated since version 2.0, to be removed in 2.1. Use EventDispatcher instead.
     */
    abstract protected function apply(QueryBuilder $filterBuilder, Expr $expr, $field, array $values);

    /**
     * {@inheritDoc}
     */
    public function applyFilter($filterBuilder, $expr, $field, array $values)
    {
        trigger_error('Filter through FilterInterface is deprecated since version 2.0 and will be removed in 2.1, use EventDispatcher instead.', E_USER_DEPRECATED);

        return $this->apply($filterBuilder, $expr, $field, $values);
    }
}
