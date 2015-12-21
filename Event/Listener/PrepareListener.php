<?php

namespace Lexik\Bundle\FormFilterBundle\Event\Listener;

use Lexik\Bundle\FormFilterBundle\Event\PrepareEvent;
use Doctrine\DBAL\Platforms\PostgreSqlPlatform;

/**
 * Prepare listener event
 */
class PrepareListener
{
    /**
     * @var boolean
     */
    protected $forceCaseInsensitivity = null;

    /**
     * @var string|null
     */
    protected $encoding;

    /**
     * @param boolean $value
     * @return PrepareListener $this
     * @throws \InvalidArgumentException
     */
    public function setForceCaseInsensitivity($value)
    {
        if (!is_bool($value)) {
            throw new \InvalidArgumentException("Expected a boolean");
        }

        $this->forceCaseInsensitivity = $value;

        return $this;
    }

    /**
     * @param $qb
     * @return boolean
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
     * @return null|string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * @param null|string $encoding
     *
     * @return PrepareListener
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;

        return $this;
    }

    /**
     * Filter builder prepare event
     *
     * @param PrepareEvent $event
     */
    public function onFilterBuilderPrepare(PrepareEvent $event)
    {
        $qb = $event->getQueryBuilder();

        $queryClasses = array(
            'Doctrine\ORM\QueryBuilder'          => 'Lexik\Bundle\FormFilterBundle\Filter\Doctrine\ORMQuery',
            'Doctrine\DBAL\Query\QueryBuilder'   => 'Lexik\Bundle\FormFilterBundle\Filter\Doctrine\DBALQuery',
            'Doctrine\ODM\MongoDB\Query\Builder' => 'Lexik\Bundle\FormFilterBundle\Filter\Doctrine\MongodbQuery',
        );

        foreach ($queryClasses as $builderClass => $queryClass) {
            if (class_exists($builderClass) && $qb instanceof $builderClass) {
                $query = new $queryClass($qb, $this->getForceCaseInsensitivity($qb), $this->encoding);

                $event->setFilterQuery($query);
                $event->stopPropagation();

                return;
            }
        }
    }
}
