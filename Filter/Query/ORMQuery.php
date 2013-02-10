<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Query;

use Doctrine\ORM\QueryBuilder;

use Lexik\Bundle\FormFilterBundle\Filter\ORM\Expr;

/**
 * @author Jeremy Barthe <j.barthe@lexik.fr>
 */
class ORMQuery implements QueryInterface
{
    /**
     * @var QueryBuilder $queryBuilder
     */
    private $queryBuilder;

    /**
     * @var Expr $expr
     */
    private $expr;

    /**
     * Constructor.
     *
     * @param QueryBuilder $queryBuilder
     */
    public function __construct(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
        $this->expr         = new Expr();
    }

    /**
     * {@inheritDoc}
     */
    public function getEventPartName()
    {
        return 'orm';
    }

    /**
     * {@inheritDoc}
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    /**
     * Get root alias.
     *
     * @return string
     */
    public function getAlias()
    {
        $aliases = $this->queryBuilder->getRootAliases();

        return isset($aliases[0]) ? $aliases[0] : '';
    }

    /**
     * Get expr class.
     *
     * @return \Lexik\Bundle\FormFilterBundle\Filter\ORM\Expr
     */
    public function getExpr()
    {
        return $this->expr;
    }
}
