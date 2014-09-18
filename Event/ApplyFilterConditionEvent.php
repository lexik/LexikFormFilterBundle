<?php

namespace Lexik\Bundle\FormFilterBundle\Event;

use Lexik\Bundle\FormFilterBundle\Filter\Condition\ConditionBuilder;
use Symfony\Component\EventDispatcher\Event;

/**
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class ApplyFilterConditionEvent extends Event
{
    /**
     * @var mixed
     */
    private $queryBuilder;

    /**
     * @var ConditionBuilder
     */
    private $conditionBuilder;

    /**
     * @param mixed            $queryBuilder
     * @param ConditionBuilder $conditionBuilder
     */
    public function __construct($queryBuilder, ConditionBuilder $conditionBuilder)
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
