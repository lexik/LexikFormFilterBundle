<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Query;

use Lexik\Bundle\FormFilterBundle\Filter\Condition\ConditionInterface;

/**
 * @author Jeremy Barthe <j.barthe@lexik.fr>
 */
interface QueryInterface
{
    /**
     * Get query builder (of ORM, DBAL, ODM, Propel, etc.).
     *
     * @return mixed
     */
    public function getQueryBuilder();

    /**
     * Return a part name of filter events (ex: orm, dbal, propel, etc.).
     *
     * @return string
     */
    public function getEventPartName();

    /**
     * @param string $expression
     * @param array  $parameters
     * @return ConditionInterface
     */
    public function createCondition($expression, array $parameters = array());

    /**
     * Get root alias.
     *
     * @return string
     */
    public function getRootAlias();

    /**
     * @param string $joinAlias
     * @return bool
     */
    public function hasJoinAlias($joinAlias);
}
