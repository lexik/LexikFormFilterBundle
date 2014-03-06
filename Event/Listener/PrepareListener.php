<?php

namespace Lexik\Bundle\FormFilterBundle\Event\Listener;

use Lexik\Bundle\FormFilterBundle\Event\PrepareEvent;
use Lexik\Bundle\FormFilterBundle\Filter\Doctrine\ORMQuery;
use Lexik\Bundle\FormFilterBundle\Filter\Doctrine\DBALQuery;
use Doctrine\DBAL\Platforms\PostgreSqlPlatform;

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
            $platform = $qb->getEntityManager()->getConnection()->getDatabasePlatform();
            $event->setFilterQuery(new ORMQuery(
                $qb,
                $platform instanceof PostgreSqlPlatform
            ));
            $event->stopPropagation();

            return;
        }

        if (class_exists('\Doctrine\DBAL\Query\QueryBuilder') && $qb instanceof \Doctrine\DBAL\Query\QueryBuilder) {
            $platform = $qb->getConnection()->getDatabasePlatform();
            $event->setFilterQuery(new DBALQuery(
                $qb,
                $platform instanceof PostgreSqlPlatform));
            $event->stopPropagation();

            return;
        }
    }
}
