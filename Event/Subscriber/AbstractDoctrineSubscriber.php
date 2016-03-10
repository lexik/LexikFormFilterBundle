<?php

namespace Lexik\Bundle\FormFilterBundle\Event\Subscriber;

use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\BooleanFilterType;
use Lexik\Bundle\FormFilterBundle\Event\GetFilterConditionEvent;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * Provide Doctrine ORM and DBAL filters.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 * @author Jeremy Barthe <j.barthe@lexik.fr>
 */
abstract class AbstractDoctrineSubscriber
{
    /**
     * @param GetFilterConditionEvent $event
     */
    public function filterValue(GetFilterConditionEvent $event)
    {
        $expr   = $event->getFilterQuery()->getExpr();
        $values = $event->getValues();

        if ('' !== $values['value'] && null !== $values['value']) {
            $paramName = $this->generateParameterName($event->getField());

            if (is_array($values['value']) && sizeof($values['value']) > 0) {
                $event->setCondition(
                    $expr->in($event->getField(), ':'.$paramName),
                    array($paramName => array($values['value'], Connection::PARAM_STR_ARRAY))
                );
            } elseif (!is_array($values['value'])) {
                $event->setCondition(
                    $expr->eq($event->getField(), ':'.$paramName),
                    array($paramName => array($values['value'], Type::STRING))
                );
            }
        }
    }

    /**
     * @param GetFilterConditionEvent $event
     */
    public function filterBoolean(GetFilterConditionEvent $event)
    {
        $expr   = $event->getFilterQuery()->getExpr();
        $values = $event->getValues();

        if (!empty($values['value'])) {
            $paramName = $this->generateParameterName($event->getField());

            $value = (bool) (BooleanFilterType::VALUE_YES == $values['value']);

            $event->setCondition(
                $expr->eq($event->getField(), ':'.$paramName),
                array($paramName => array($value, Type::BOOLEAN))
            );
        }
    }

    /**
     * @param GetFilterConditionEvent $event
     */
    public function filterCheckbox(GetFilterConditionEvent $event)
    {
        $expr   = $event->getFilterQuery()->getExpr();
        $values = $event->getValues();

        if (!empty($values['value'])) {
            $paramName = $this->generateParameterName($event->getField());

            $event->setCondition(
                $expr->eq($event->getField(), ':'.$paramName),
                array($paramName => array($values['value'], Type::STRING))
            );
        }
    }

    /**
     * @param GetFilterConditionEvent $event
     */
    public function filterDate(GetFilterConditionEvent $event)
    {
        $expr   = $event->getFilterQuery()->getExpr();
        $values = $event->getValues();

        if ($values['value'] instanceof \DateTime) {
            $paramName = $this->generateParameterName($event->getField());

            $event->setCondition(
                $expr->eq($event->getField(), ':'.$paramName),
                array($paramName => array($values['value'], Type::DATE))
            );
        }
    }

    /**
     * @param GetFilterConditionEvent $event
     */
    public function filterDateRange(GetFilterConditionEvent $event)
    {
        $expr   = $event->getFilterQuery()->getExpressionBuilder();
        $values = $event->getValues();
        $value  = $values['value'];

        if (isset($value['left_date'][0]) || isset($value['right_date'][0])) {
            $event->setCondition($expr->dateInRange($event->getField(), $value['left_date'][0], $value['right_date'][0]));
        }
    }

    /**
     * @param GetFilterConditionEvent $event
     */
    public function filterDateTime(GetFilterConditionEvent $event)
    {
        $expr   = $event->getFilterQuery()->getExpr();
        $values = $event->getValues();

        if ($values['value'] instanceof \DateTime) {
            $paramName = $this->generateParameterName($event->getField());

            $event->setCondition(
                $expr->eq($event->getField(), ':'.$paramName),
                array($paramName => array($values['value'], Type::DATETIME))
            );
        }
    }

    /**
     * @param GetFilterConditionEvent $event
     */
    public function filterDateTimeRange(GetFilterConditionEvent $event)
    {
        $expr   = $event->getFilterQuery()->getExpressionBuilder();
        $values = $event->getValues();
        $value  = $values['value'];

        if (isset($value['left_datetime'][0]) || $value['right_datetime'][0]) {
            $event->setCondition($expr->datetimeInRange($event->getField(), $value['left_datetime'][0], $value['right_datetime'][0]));
        }
    }

    /**
     * @param GetFilterConditionEvent $event
     */
    public function filterNumber(GetFilterConditionEvent $event)
    {
        $expr   = $event->getFilterQuery()->getExpr();
        $values = $event->getValues();

        if ('' !== $values['value'] && null !== $values['value']) {
            $paramName = sprintf('p_%s', str_replace('.', '_', $event->getField()));

            $op = empty($values['condition_operator']) ? FilterOperands::OPERATOR_EQUAL : $values['condition_operator'];

            $event->setCondition(
                $expr->$op($event->getField(), ':'.$paramName),
                array($paramName => array($values['value'], is_int($values['value']) ? Type::INTEGER : Type::FLOAT))
            );
        }
    }

    /**
     * @param GetFilterConditionEvent $event
     */
    public function filterNumberRange(GetFilterConditionEvent $event)
    {
        $expr   = $event->getFilterQuery()->getExpr();
        $values = $event->getValues();
        $value  = $values['value'];

        $expression = $expr->andX();
        $params = array();

        if (isset($value['left_number'][0])) {
            $hasSelector = (FilterOperands::OPERAND_SELECTOR === $value['left_number']['condition_operator']);

            if (!$hasSelector && isset($value['left_number'][0])) {
                $leftValue = $value['left_number'][0];
                $leftCond  = $value['left_number']['condition_operator'];

            } elseif ($hasSelector && isset($value['left_number'][0]['text'])) {
                $leftValue = $value['left_number'][0]['text'];
                $leftCond  = $value['left_number'][0]['condition_operator'];
            }

            if (isset($leftValue, $leftCond)) {
                $leftParamName = sprintf('p_%s_left', str_replace('.', '_', $event->getField()));

                $expression->add($expr->$leftCond($event->getField(), ':'.$leftParamName));
                $params[$leftParamName] = array($leftValue, is_int($leftValue) ? Type::INTEGER : Type::FLOAT);
            }
        }

        if (isset($value['right_number'][0])) {
            $hasSelector = (FilterOperands::OPERAND_SELECTOR === $value['right_number']['condition_operator']);

            if (!$hasSelector && isset($value['right_number'][0])) {
                $rightValue = $value['right_number'][0];
                $rightCond  = $value['right_number']['condition_operator'];

            } elseif ($hasSelector && isset($value['right_number'][0]['text'])) {
                $rightValue = $value['right_number'][0]['text'];
                $rightCond  = $value['right_number'][0]['condition_operator'];
            }

            if (isset($rightValue, $rightCond)) {
                $rightParamName = sprintf('p_%s_right', str_replace('.', '_', $event->getField()));

                $expression->add($expr->$rightCond($event->getField(), ':'.$rightParamName));
                $params[$rightParamName] = array($rightValue, is_int($rightValue) ? Type::INTEGER : Type::FLOAT);
            }
        }

        if ($expression->count()) {
            $event->setCondition($expression, $params);
        }
    }

    /**
     * @param GetFilterConditionEvent $event
     */
    public function filterText(GetFilterConditionEvent $event)
    {
        $expr   = $event->getFilterQuery()->getExpressionBuilder();
        $values = $event->getValues();

        if ('' !== $values['value'] && null !== $values['value']) {
            if (isset($values['condition_pattern'])) {
                $event->setCondition($expr->stringLike($event->getField(), $values['value'], $values['condition_pattern']));
            } else {
                $event->setCondition($expr->stringLike($event->getField(), $values['value']));
            }
        }
    }

    /**
     * @param string $field
     * @return string
     */
    protected function generateParameterName($field)
    {
        return sprintf('p_%s', str_replace('.', '_', $field));
    }
}
