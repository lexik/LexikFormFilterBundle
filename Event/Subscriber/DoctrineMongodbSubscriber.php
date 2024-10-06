<?php

namespace Lexik\Bundle\FormFilterBundle\Event\Subscriber;

use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Query\Expr;
use Lexik\Bundle\FormFilterBundle\Event\GetFilterConditionEvent;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\BooleanFilterType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class DoctrineMongodbSubscriber implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            // Lexik form filter types
            'lexik_form_filter.apply.mongodb.filter_boolean' => ['filterBoolean'],
            'lexik_form_filter.apply.mongodb.filter_checkbox' => ['filterCheckbox'],
            'lexik_form_filter.apply.mongodb.filter_choice' => ['filterValue'],
            'lexik_form_filter.apply.mongodb.filter_date' => ['filterDate'],
            'lexik_form_filter.apply.mongodb.filter_date_range' => ['filterDateRange'],
            'lexik_form_filter.apply.mongodb.filter_datetime' => ['filterDateTime'],
            'lexik_form_filter.apply.mongodb.filter_datetime_range' => ['filterDateTimeRange'],
            'lexik_form_filter.apply.mongodb.filter_number' => ['filterNumber'],
            'lexik_form_filter.apply.mongodb.filter_number_range' => ['filterNumberRange'],
            'lexik_form_filter.apply.mongodb.filter_text' => ['filterText'],
            'lexik_form_filter.apply.mongodb.filter_document' => ['filterDocument'],
            // Symfony types
            'lexik_form_filter.apply.mongodb.text' => ['filterText'],
            'lexik_form_filter.apply.mongodb.email' => ['filterValue'],
            'lexik_form_filter.apply.mongodb.integer' => ['filterValue'],
            'lexik_form_filter.apply.mongodb.money' => ['filterValue'],
            'lexik_form_filter.apply.mongodb.number' => ['filterValue'],
            'lexik_form_filter.apply.mongodb.percent' => ['filterValue'],
            'lexik_form_filter.apply.mongodb.search' => ['filterValue'],
            'lexik_form_filter.apply.mongodb.url' => ['filterValue'],
            'lexik_form_filter.apply.mongodb.choice' => ['filterValue'],
            'lexik_form_filter.apply.mongodb.country' => ['filterValue'],
            'lexik_form_filter.apply.mongodb.language' => ['filterValue'],
            'lexik_form_filter.apply.mongodb.locale' => ['filterValue'],
            'lexik_form_filter.apply.mongodb.timezone' => ['filterValue'],
            'lexik_form_filter.apply.mongodb.date' => ['filterDate'],
            'lexik_form_filter.apply.mongodb.datetime' => ['filterDate'],
            'lexik_form_filter.apply.mongodb.birthday' => ['filterDate'],
            'lexik_form_filter.apply.mongodb.checkbox' => ['filterValue'],
            'lexik_form_filter.apply.mongodb.radio' => ['filterValue'],
        ];
    }

    /**
     * @param GetFilterConditionEvent $event
     */
    public function filterValue(GetFilterConditionEvent $event)
    {
        /** @var Expr $expr */
        $expr = $event->getFilterQuery()->getExpr();
        $values = $event->getValues();

        if ('' !== $values['value'] && null !== $values['value']) {
            if (is_array($values['value']) && sizeof($values['value']) > 0) {
                $event->setCondition($expr->field($event->getField())->in($values['value']));
            } elseif (!is_array($values['value'])) {
                $event->setCondition($expr->field($event->getField())->equals($values['value']));
            }
        }
    }

    /**
     * @param GetFilterConditionEvent $event
     */
    public function filterBoolean(GetFilterConditionEvent $event)
    {
        /** @var Expr $expr */
        $expr = $event->getFilterQuery()->getExpr();
        $values = $event->getValues();

        if (!empty($values['value'])) {
            $value = (bool) (BooleanFilterType::VALUE_YES == $values['value']);

            $event->setCondition($expr->field($event->getField())->equals($value));
        }
    }

    /**
     * @param GetFilterConditionEvent $event
     */
    public function filterCheckbox(GetFilterConditionEvent $event)
    {
        /** @var Expr $expr */
        $expr = $event->getFilterQuery()->getExpr();
        $values = $event->getValues();

        if (!empty($values['value'])) {
            $event->setCondition($expr->field($event->getField())->equals($values['value']));
        }
    }

    /**
     * @param GetFilterConditionEvent $event
     */
    public function filterDate(GetFilterConditionEvent $event)
    {
        /** @var Expr $expr */
        $expr = $event->getFilterQuery()->getExpr();
        $values = $event->getValues();

        if ($values['value'] instanceof \DateTime) {
            $event->setCondition($expr->field($event->getField())->equals($values['value']));
        }
    }

    /**
     * @param GetFilterConditionEvent $event
     */
    public function filterDateRange(GetFilterConditionEvent $event)
    {
        /** @var Builder $qb */
        $qb = $event->getFilterQuery()->getQueryBuilder();
        $values = $event->getValues();
        $value = $values['value'];

        if (isset($value['left_date'][0]) && isset($value['right_date'][0])) {
            $expression = $qb->expr()->field($event->getField())->range(
                $value['left_date'][0],
                $value['right_date'][0]
            );
        } elseif (isset($value['left_date'][0])) {
            $expression = $qb->expr()->field($event->getField())->gte($value['left_date'][0]);
        } elseif (isset($value['right_date'][0])) {
            $expression = $qb->expr()->field($event->getField())->lte($value['right_date'][0]);
        }

        if (isset($expression)) {
            $event->setCondition($expression);
        }
    }

    /**
     * @param GetFilterConditionEvent $event
     */
    public function filterDateTime(GetFilterConditionEvent $event)
    {
        /** @var Expr $expr */
        $expr = $event->getFilterQuery()->getExpr();
        $values = $event->getValues();

        if ($values['value'] instanceof \DateTime) {
            $event->setCondition($expr->field($event->getField())->equals($values['value']));
        }
    }

    /**
     * @param GetFilterConditionEvent $event
     */
    public function filterDateTimeRange(GetFilterConditionEvent $event)
    {
        /** @var Builder $qb */
        $qb = $event->getFilterQuery()->getQueryBuilder();
        $values = $event->getValues();
        $value = $values['value'];

        if (isset($value['left_datetime'][0]) && isset($value['right_datetime'][0])) {
            $expression = $qb->expr()->field($event->getField())->range(
                $value['left_datetime'][0],
                $value['right_datetime'][0]
            );
        } elseif (isset($value['left_datetime'][0])) {
            $expression = $qb->expr()->field($event->getField())->gte($value['left_datetime'][0]);
        } elseif (isset($value['right_datetime'][0])) {
            $expression = $qb->expr()->field($event->getField())->lte($value['right_datetime'][0]);
        }

        if (isset($expression)) {
            $event->setCondition($expression);
        }
    }

    /**
     * @param GetFilterConditionEvent $event
     */
    public function filterNumber(GetFilterConditionEvent $event)
    {
        /** @var Expr $expr */
        $expr = $event->getFilterQuery()->getExpr();
        $values = $event->getValues();

        if ('' !== $values['value'] && null !== $values['value']) {
            $op = empty($values['condition_operator']) ? FilterOperands::OPERATOR_EQUAL : $values['condition_operator'];
            $method = $this->getExprOperatorMethod($op);

            $event->setCondition($expr->field($event->getField())->{$method}($values['value']));
        }
    }

    /**
     * @param GetFilterConditionEvent $event
     */
    public function filterNumberRange(GetFilterConditionEvent $event)
    {
        /** @var Builder $qb */
        $qb = $event->getFilterQuery()->getQueryBuilder();
        $values = $event->getValues();
        $value = $values['value'];

        if (isset($value['left_number'][0])) {
            $hasSelector = (FilterOperands::OPERAND_SELECTOR === $value['left_number']['condition_operator']);

            if (!$hasSelector && isset($value['left_number'][0])) {
                $leftValue = $value['left_number'][0];
                $leftOp = $value['left_number']['condition_operator'];
            } elseif ($hasSelector && isset($value['left_number'][0]['text'])) {
                $leftValue = $value['left_number'][0]['text'];
                $leftOp = $value['left_number'][0]['condition_operator'];
            }
        }

        if (isset($value['right_number'][0])) {
            $hasSelector = (FilterOperands::OPERAND_SELECTOR === $value['right_number']['condition_operator']);

            if (!$hasSelector && isset($value['right_number'][0])) {
                $rightValue = $value['right_number'][0];
                $rightOp = $value['right_number']['condition_operator'];
            } elseif ($hasSelector && isset($value['right_number'][0]['text'])) {
                $rightValue = $value['right_number'][0]['text'];
                $rightOp = $value['right_number'][0]['condition_operator'];
            }
        }

        if (isset($leftValue, $leftOp, $rightValue, $rightOp)) {
            /** @var Expr $expr */
            $expression = $qb->expr()
                ->field($event->getField())
                ->operator('$' . $leftOp, $leftValue)
                ->operator('$' . $rightOp, $rightValue);

            $event->setCondition($expression);
        } elseif (isset($leftValue, $leftOp)) {
            $method = $this->getExprOperatorMethod($leftOp);

            $event->setCondition(
                $qb->expr()->field($event->getField())->{$method}($leftValue)
            );
        } elseif (isset($rightValue, $rightOp)) {
            $method = $this->getExprOperatorMethod($rightOp);

            $event->setCondition(
                $qb->expr()->field($event->getField())->{$method}($rightValue)
            );
        }
    }

    /**
     * @param GetFilterConditionEvent $event
     */
    public function filterText(GetFilterConditionEvent $event)
    {
        /** @var Expr $expr */
        $expr = $event->getFilterQuery()->getExpr();
        $values = $event->getValues();

        if ('' !== $values['value'] && null !== $values['value']) {
            $pattern = $values['condition_pattern'] ?? FilterOperands::STRING_CONTAINS;

            $patternValues = [FilterOperands::STRING_STARTS => new \MongoRegex('/^' . $values['value'] . '.*/i'), FilterOperands::STRING_ENDS => new \MongoRegex('/.*' . $values['value'] . '$/i'), FilterOperands::STRING_CONTAINS => new \MongoRegex('/.*' . $values['value'] . '.*/i'), FilterOperands::STRING_EQUALS => $values['value']];

            if (!isset($patternValues[$pattern])) {
                throw new \InvalidArgumentException('Wrong type constant in string like expression mapper.');
            }

            $value = $patternValues[$pattern];

            $event->setCondition($expr->field($event->getField())->equals($value));
        }
    }

    /**
     * @param GetFilterConditionEvent $event
     */
    public function filterDocument(GetFilterConditionEvent $event)
    {
        /** @var Expr $expr */
        $expr = $event->getFilterQuery()->getExpr();
        $values = $event->getValues();

        if (is_array($values['value']) && count($values['value']) > 0 && is_object($values['value'][0])) {
            $values['value'] = new ArrayCollection($values['value']);
        }

        if (is_object($values['value'])) {
            $field = $event->getField();
            $multipleLevels = (false !== strpos($field, '.'));

            if ($multipleLevels) {
                // replace the form field name by the referenced document name
                $parts = explode('.', $field);
                $parts[count($parts) - 1] = $values['reference_name'];
                $field = implode('.', $parts);
            }

            if ($values['value'] instanceof Collection) {
                $ids = [];

                foreach ($values['value'] as $object) {
                    $ids[] = new \MongoId($object->getId());
                }

                if (count($ids) > 0) {
                    $event->setCondition($expr->field($field . '.$id')->in($ids));
                }
            } elseif ($multipleLevels) {
                $id = new \MongoId($values['value']->getId());
                $event->setCondition($expr->field($field . '.$id')->equals($id));
            } else {
                if ('one' === $values['reference_type']) {
                    $condition = $expr->field($field)->references($values['value']);
                } else {
                    $condition = $expr->field($field)->includesReferenceTo($values['value']);
                }

                $event->setCondition($condition);
            }
        }
    }

    /**
     * @param string $operator
     * @return string
     */
    private function getExprOperatorMethod($operator)
    {
        $methods = ['eq' => 'equals', 'gt' => 'gt', 'gte' => 'gte', 'lt' => 'lt', 'lte' => 'lte'];

        if (!isset($methods[$operator])) {
            throw new \InvalidArgumentException('Wrong type constant for number operator.');
        }

        return $methods[$operator];
    }
}
