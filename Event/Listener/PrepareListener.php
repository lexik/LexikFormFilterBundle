<?php

namespace Lexik\Bundle\FormFilterBundle\Event\Listener;

use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Lexik\Bundle\FormFilterBundle\Event\PrepareEvent;
use Lexik\Bundle\FormFilterBundle\Filter\Doctrine\DBALQuery;
use Lexik\Bundle\FormFilterBundle\Filter\Doctrine\ORMQuery;

/**
 * Prepare listener event.
 */
class PrepareListener
{
    /**
     * @var bool
     */
    protected $forceCaseInsensitivity = null;

    /**
     * @param bool $value
     *
     * @return PrepareListener $this
     *
     * @throws \InvalidArgumentException
     */
    public function setForceCaseInsensitivity($value)
    {
        if (!is_bool($value)) {
            throw new \InvalidArgumentException('Expected a boolean');
        }

        $this->forceCaseInsensitivity = $value;

        return $this;
    }

    /**
     * @param $qb
     *
     * @return bool
     */
    public function getForceCaseInsensitivity($qb)
    {
        if (isset($this->forceCaseInsensitivity)) {
            return $this->forceCaseInsensitivity;
        }

        if (class_exists('\Doctrine\ORM\QueryBuilder') && $qb instanceof \Doctrine\ORM\QueryBuilder) {
            return ($qb->getEntityManager()->getConnection()->getDatabasePlatform() instanceof PostgreSqlPlatform);
        }

        if (class_exists('\Doctrine\DBAL\Query\QueryBuilder') && $qb instanceof \Doctrine\DBAL\Query\QueryBuilder) {
            return ($qb->getConnection()->getDatabasePlatform() instanceof PostgreSqlPlatform);
        }
    }

    /**
     * Filter builder prepare event.
     *
     * @param PrepareEvent $event
     */
    public function onFilterBuilderPrepare(PrepareEvent $event)
    {
        $qb = $event->getQueryBuilder();

        if (class_exists('\Doctrine\ORM\QueryBuilder') && $qb instanceof \Doctrine\ORM\QueryBuilder) {
            $event->setFilterQuery(new ORMQuery(
                $qb,
                $this->getForceCaseInsensitivity($qb)
            ));
            $event->stopPropagation();

            return;
        }

        if (class_exists('\Doctrine\DBAL\Query\QueryBuilder') && $qb instanceof \Doctrine\DBAL\Query\QueryBuilder) {
            $event->setFilterQuery(new DBALQuery(
                $qb,
                $this->getForceCaseInsensitivity($qb)
            ));
            $event->stopPropagation();

            return;
        }
    }
}
