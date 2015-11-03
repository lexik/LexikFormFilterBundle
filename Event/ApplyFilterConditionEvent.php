<?php

namespace Lexik\Bundle\FormFilterBundle\Event;

use Lexik\Bundle\FormFilterBundle\Filter\Condition\ConditionBuilderInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event class to compute the WHERE clause from the conditions.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class ApplyFilterConditionEvent extends Event
{
    /**
     * @var mixed
     */
    private $queryBuilder;

    /**
     * @var ConditionBuilderInterface
     */
    private $conditionBuilder;

    /**
     * @param mixed                     $queryBuilder
     * @param ConditionBuilderInterface $conditionBuilder
     */
    public function __construct($queryBuilder, ConditionBuilderInterface $conditionBuilder)
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
