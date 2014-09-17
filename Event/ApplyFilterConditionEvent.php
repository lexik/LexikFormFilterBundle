<?php

namespace Lexik\Bundle\FormFilterBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class ApplyFilterConditionEvent extends Event
{
    private $queryBuilder;

    private $conditionBuilder;

    public function __construct($queryBuilder, $conditionBuilder)
    {
        $this->queryBuilder = $queryBuilder;
        $this->conditionBuilder = $conditionBuilder;
    }

    /**
     * @return mixed
     */
    public function getConditionBuilder()
    {
        return $this->conditionBuilder;
    }

    /**
     * @return mixed
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }
}
