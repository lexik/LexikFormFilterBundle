<?php

namespace Lexik\Bundle\FormFilterBundle\Event\Listener;

use Symfony\Component\EventDispatcher\Event;

use Lexik\Bundle\FormFilterBundle\Event\PrepareEvent;
use Lexik\Bundle\FormFilterBundle\Filter\Query\ORMQuery;
use Lexik\Bundle\FormFilterBundle\Filter\ORM\Expr;

/**
 * Prepare listener event
 */
class PrepareListener
{
    /**
     * Filter builder prepare event
     *
     * @param PrepareEvent $event
     */
    public function onFilterBuilderPrepare(PrepareEvent $event)
    {
        $qb = $event->getQueryBuilder();

        if (class_exists('\Doctrine\ORM\QueryBuilder') && $qb instanceof \Doctrine\ORM\QueryBuilder) {
            $event->setFilterQuery(new ORMQuery($qb));
            $event->stopPropagation();

            return;
        }

        if (class_exists('\Doctrine\DBAL\Query\QueryBuilder') && $qb instanceof \Doctrine\DBAL\Query\QueryBuilder) {
            throw new \RuntimeException('not supported yet...');
        }
    }
}
