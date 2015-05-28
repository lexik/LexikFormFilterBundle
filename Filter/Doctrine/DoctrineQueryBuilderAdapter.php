<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Doctrine;

use Doctrine\ORM\QueryBuilder as ORMQueryBuilder;
use Doctrine\DBAL\Query\QueryBuilder as DBALQueryBuilder;

/**
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class DoctrineQueryBuilderAdapter
{
    /**
     * @var mixed
     */
    private $qb;

    /**
     * @param mixed $qb
     * @throws \RuntimeException
     */
    public function __construct($qb)
    {
        if (! ($qb instanceof ORMQueryBuilder || $qb  instanceof DBALQueryBuilder)) {
            throw new \RuntimeException('Invalid Doctrine query builder instance.');
        }

        $this->qb = $qb;
    }

    /**
     * @return \Doctrine\DBAL\Query\Expression\CompositeExpression|\Doctrine\ORM\Query\Expr\Andx
     */
    public function andX()
    {
        return $this->qb->expr()->andX();
    }

    /**
     * @return \Doctrine\DBAL\Query\Expression\CompositeExpression|\Doctrine\ORM\Query\Expr\Orx
     */
    public function orX()
    {
        return $this->qb->expr()->orX();
    }

    /**
     * @param mixed $where
     */
    public function where($where)
    {
        $this->qb->where($where);
    }

    /**
     * @param mixed $where
     */
    public function andWhere($where)
    {
        $this->qb->andWhere($where);
    }

    /**
     * @param mixed $where
     */
    public function orWhere($where)
    {
        $this->qb->orWhere($where);
    }

    /**
     * @param string      $name
     * @param mixed       $value
     * @param string|null $type
     */
    public function setParameter($name, $value, $type = null)
    {
        $this->qb->setParameter($name, $value, $type);
    }
}
