<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Query;

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
}
