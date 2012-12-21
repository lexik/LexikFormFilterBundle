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

    /**
     * Apply filter: orm version of signature
     *
     * @param QueryBuilder $filterBuilder
     * @param Expr         $expr
     * @param string       $field
     * @param array        $values
     *
     * @return void
     */
    abstract protected function apply(QueryBuilder $filterBuilder, Expr $expr, $field, array $values);

    /**
     * {@inheritDoc}
     */
    public function applyFilter($filterBuilder, $expr, $field, array $values)
    {
        return $this->apply($filterBuilder, $expr, $field, $values);
    }
}
