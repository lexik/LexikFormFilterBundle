<?php

namespace Lexik\Bundle\FormFilterBundle\Event\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Lexik\Bundle\FormFilterBundle\Filter\Doctrine\Expression\ExpressionBuilder;
use Lexik\Bundle\FormFilterBundle\Event\ApplyFilterEvent;
use Lexik\Bundle\FormFilterBundle\Filter\Extension\Type\BooleanFilterType;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Collections\Collection;

/**
 * Provide Doctrine ORM and DBAL filters.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 * @author Jeremy Barthe <j.barthe@lexik.fr>
 */
class DoctrineSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            // Doctrine ORM - filter field types
            'lexik_form_filter.apply.orm.filter_boolean'       => array('filterBoolean'),
            'lexik_form_filter.apply.orm.filter_checkbox'      => array('filterCheckbox'),
            'lexik_form_filter.apply.orm.filter_choice'        => array('filterValue'),
            'lexik_form_filter.apply.orm.filter_date'          => array('filterDate'),
            'lexik_form_filter.apply.orm.filter_date_range'    => array('filterDateRange'),
            'lexik_form_filter.apply.orm.filter_entity'        => array('filterEntity'),
            'lexik_form_filter.apply.orm.filter_number'        => array('filterNumber'),
            'lexik_form_filter.apply.orm.filter_number_range'  => array('filterNumberRange'),
            'lexik_form_filter.apply.orm.filter_text'          => array('filterText'),

            // Doctrine ORM - Symfony2 field types
            'lexik_form_filter.apply.orm.text'                 => array('filterText'),
            'lexik_form_filter.apply.orm.email'                => array('filterValue'),
            'lexik_form_filter.apply.orm.integer'              => array('filterValue'),
            'lexik_form_filter.apply.orm.money'                => array('filterValue'),
            'lexik_form_filter.apply.orm.number'               => array('filterValue'),
            'lexik_form_filter.apply.orm.percent'              => array('filterValue'),
            'lexik_form_filter.apply.orm.search'               => array('filterValue'),
            'lexik_form_filter.apply.orm.url'                  => array('filterValue'),
            'lexik_form_filter.apply.orm.choice'               => array('filterValue'),
            'lexik_form_filter.apply.orm.entity'               => array('filterEntity'),
            'lexik_form_filter.apply.orm.country'              => array('filterValue'),
            'lexik_form_filter.apply.orm.language'             => array('filterValue'),
            'lexik_form_filter.apply.orm.locale'               => array('filterValue'),
            'lexik_form_filter.apply.orm.timezone'             => array('filterValue'),
            'lexik_form_filter.apply.orm.date'                 => array('filterDate'),
            'lexik_form_filter.apply.orm.datetime'             => array('filterDate'),
            'lexik_form_filter.apply.orm.birthday'             => array('filterDate'),
            'lexik_form_filter.apply.orm.checkbox'             => array('filterValue'),
            'lexik_form_filter.apply.orm.radio'                => array('filterValue'),

            // Doctrine DBAL
            'lexik_form_filter.apply.dbal.filter_boolean'      => array('filterBoolean'),
            'lexik_form_filter.apply.dbal.filter_checkbox'     => array('filterCheckbox'),
            'lexik_form_filter.apply.dbal.filter_choice'       => array('filterValue'),
            'lexik_form_filter.apply.dbal.filter_date'         => array('filterDate'),
            'lexik_form_filter.apply.dbal.filter_date_range'   => array('filterDateRange'),
            'lexik_form_filter.apply.dbal.filter_number'       => array('filterNumber'),
            'lexik_form_filter.apply.dbal.filter_number_range' => array('filterNumberRange'),
            'lexik_form_filter.apply.dbal.filter_text'         => array('filterText'),

            // Doctrine DBAL - Symfony2 field types
            'lexik_form_filter.apply.dbal.text'                => array('filterText'),
            'lexik_form_filter.apply.dbal.email'               => array('filterValue'),
            'lexik_form_filter.apply.dbal.integer'             => array('filterValue'),
            'lexik_form_filter.apply.dbal.money'               => array('filterValue'),
            'lexik_form_filter.apply.dbal.number'              => array('filterValue'),
            'lexik_form_filter.apply.dbal.percent'             => array('filterValue'),
            'lexik_form_filter.apply.dbal.search'              => array('filterValue'),
            'lexik_form_filter.apply.dbal.url'                 => array('filterValue'),
            'lexik_form_filter.apply.dbal.choice'              => array('filterValue'),
            'lexik_form_filter.apply.dbal.country'             => array('filterValue'),
            'lexik_form_filter.apply.dbal.language'            => array('filterValue'),
            'lexik_form_filter.apply.dbal.locale'              => array('filterValue'),
            'lexik_form_filter.apply.dbal.timezone'            => array('filterValue'),
            'lexik_form_filter.apply.dbal.date'                => array('filterDate'),
            'lexik_form_filter.apply.dbal.datetime'            => array('filterDate'),
            'lexik_form_filter.apply.dbal.birthday'            => array('filterDate'),
            'lexik_form_filter.apply.dbal.checkbox'            => array('filterValue'),
            'lexik_form_filter.apply.dbal.radio'               => array('filterValue'),
        );
    }

    public function filterValue(ApplyFilterEvent $event)
    {
        $qb     = $event->getQueryBuilder();
        $expr   = $event->getFilterQuery()->getExpr();
        $values = $event->getValues();

        if (!empty($values['value'])) {
            // alias.field -> alias_field
            $fieldName = str_replace('.', '_', $event->getField());

            $qb->andWhere($expr->eq($event->getField(), ':' . $fieldName))
                ->setParameter($fieldName, $values['value']);
        }
    }

    public function filterBoolean(ApplyFilterEvent $event)
    {
        $qb     = $event->getQueryBuilder();
        $expr   = $event->getFilterQuery()->getExpr();
        $values = $event->getValues();

        if (!empty($values['value'])) {
            $value = (int)(BooleanFilterType::VALUE_YES == $values['value']);
            $qb->andWhere($expr->eq($event->getField(), $value));
        }
    }

    public function filterCheckbox(ApplyFilterEvent $event)
    {
        $qb     = $event->getQueryBuilder();
        $expr   = $event->getFilterQuery()->getExpr();
        $values = $event->getValues();

        if (!empty($values['value'])) {
            $qb->andWhere($expr->eq($event->getField(), $values['value']));
        }
    }

    public function filterDate(ApplyFilterEvent $event)
    {
        $qb     = $event->getQueryBuilder();
        $expr   = $event->getFilterQuery()->getExpr();
        $values = $event->getValues();

        if ($values['value'] instanceof \DateTime) {
            $date = $values['value']->format(ExpressionBuilder::SQL_DATE);
            $qb->andWhere($expr->eq($event->getField(), $expr->literal($date)));
        }
    }

    public function filterDateRange(ApplyFilterEvent $event)
    {
        $qb     = $event->getQueryBuilder();
        $expr   = $event->getFilterQuery()->getExpressionBuilder();
        $values = $event->getValues();
        $value  = $values['value'];

        if (isset($value['left_date'][0]) || $value['right_date'][0]) {
            $qb->andWhere($expr->dateInRange($event->getField(), $value['left_date'][0], $value['right_date'][0]));
        }
    }

    public function filterEntity(ApplyFilterEvent $event)
    {
        $qb = $event->getQueryBuilder();
        if ( ! $qb instanceof QueryBuilder) {
            return;
        }

        $expr   = $event->getFilterQuery()->getExpr();
        $values = $event->getValues();

        if (is_object($values['value'])) {
            if ($values['value'] instanceof Collection) {
                $ids = array();

                foreach ($values['value'] as $value) {
                    if (!is_callable(array($value, 'getId'))) {
                        throw new \Exception(sprintf('Can\'t call method "getId()" on an instance of "%s"', get_class($value)));
                    }
                    $ids[] = $value->getId();
                }

                if (count($ids) > 0) {
                    $qb->andWhere($expr->in($event->getField(), $ids));
                }

            } else {
                if (!is_callable(array($values['value'], 'getId'))) {
                    throw new \Exception(sprintf('Can\'t call method "getId()" on an instance of "%s"', get_class($values['value'])));
                }

                $qb->andWhere($expr->eq($event->getField(), $values['value']->getId()));
            }
        }
    }

    public function filterNumber(ApplyFilterEvent $event)
    {
        $qb     = $event->getQueryBuilder();
        $expr   = $event->getFilterQuery()->getExpr();
        $values = $event->getValues();

        if (!empty($values['value'])) {
            $op = $values['condition_operator'];
            $qb->andWhere($expr->$op($event->getField(), $values['value']));
        }
    }

    public function filterNumberRange(ApplyFilterEvent $event)
    {
        $qb     = $event->getQueryBuilder();
        $expr   = $event->getFilterQuery()->getExpr();
        $values = $event->getValues();
        $value  = $values['value'];

        if (isset($value['left_number'][0])) {
            $leftCond   = $value['left_number']['condition_operator'];
            $leftValue  = $value['left_number'][0];

            $qb->andWhere($expr->$leftCond($event->getField(), $leftValue));
        }

        if (isset($value['right_number'][0])) {
            $rightCond  = $value['right_number']['condition_operator'];
            $rightValue = $value['right_number'][0];

            $qb->andWhere($expr->$rightCond($event->getField(), $rightValue));
        }
    }

    public function filterText(ApplyFilterEvent $event)
    {
        $qb     = $event->getQueryBuilder();
        $expr   = $event->getFilterQuery()->getExpressionBuilder();
        $values = $event->getValues();

        if (!empty($values['value'])) {
            if (isset($values['condition_pattern'])) {
                $qb->andWhere($expr->stringLike($event->getField(), $values['value'], $values['condition_pattern']));
            } else {
                $qb->andWhere($expr->stringLike($event->getField(), $values['value']));
            }
        }
    }
}
