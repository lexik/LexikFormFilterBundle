<?php

namespace Lexik\Bundle\FormFilterBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Lexik\Bundle\FormFilterBundle\Filter\Condition\Condition;
use Lexik\Bundle\FormFilterBundle\Filter\Condition\ConditionInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;

/**
 * @author Jeremy Barthe <j.barthe@lexik.fr>
 */
class GetFilterConditionEvent extends Event
{
    /**
     * @var QueryInterface $filterQuery
     */
    private $filterQuery;

    /**
     * @var string $field
     */
    private $field;

    /**
     * @var array $values
     */
    private $values;

    /**
     * @var ConditionInterface
     */
    private $condition;

    /**
     * Construct.
     *
     * @param QueryInterface $filterQuery
     * @param string         $field
     * @param array          $values
     */
    public function __construct(QueryInterface $filterQuery, $field, $values)
    {
        $this->filterQuery = $filterQuery;
        $this->field       = $field;
        $this->values      = $values;
    }

    /**
     * @return QueryInterface
     */
    public function getFilterQuery()
    {
        return $this->filterQuery;
    }

    /**
     * @return object
     */
    public function getQueryBuilder()
    {
        return $this->filterQuery->getQueryBuilder();
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @param string $expression
     * @param array  $parameters
     */
    public function setCondition($expression, array $parameters = array())
    {
        $this->condition = new Condition($expression, $parameters);
    }

    /**
     * @return ConditionInterface
     */
    public function getCondition()
    {
        return $this->condition;
    }
}
