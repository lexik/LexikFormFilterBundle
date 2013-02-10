<?php

namespace Lexik\Bundle\FormFilterBundle\Event\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Lexik\Bundle\FormFilterBundle\Filter\ORM\Expr;
use Lexik\Bundle\FormFilterBundle\Event\ApplyFilterEvent;
use Lexik\Bundle\FormFilterBundle\Filter\Extension\Type\BooleanFilterType;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Collections\Collection;

/**
 * Provide Doctrine ORM filters.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 * @author Jeremy Barthe <j.barthe@lexik.fr>
 */
class DoctrineORMSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            'lexik_form_filter.apply.orm.filter_boolean'      => array('filterBoolean'),
            'lexik_form_filter.apply.orm.filter_checkbox'     => array('filterCheckbox'),
            'lexik_form_filter.apply.orm.filter_choice'       => array('filterChoice'),
            'lexik_form_filter.apply.orm.filter_date'         => array('filterDate'),
            'lexik_form_filter.apply.orm.filter_date_range'   => array('filterDateRange'),
            'lexik_form_filter.apply.orm.filter_entity'       => array('filterEntity'),
            'lexik_form_filter.apply.orm.filter_number'       => array('filterNumber'),
            'lexik_form_filter.apply.orm.filter_number_range' => array('filterNumberRange'),
            'lexik_form_filter.apply.orm.filter_text'         => array('filterText'),
        );
    }

    public function filterBoolean(ApplyFilterEvent $event)
    {
        if ( ! $event->getQueryBuilder() instanceof QueryBuilder) {
            return;
        }

        $values = $event->getValues();
        if (!empty($values['value'])) {
            $value = (int)(BooleanFilterType::VALUE_YES == $values['value']);
            $event->getQueryBuilder()->andWhere($event->getFilterQuery()->getExpr()->eq($event->getField(), $value));
        }
    }

    public function filterCheckbox(ApplyFilterEvent $event)
    {
        if ( ! $event->getQueryBuilder() instanceof QueryBuilder) {
            return;
        }

        $values = $event->getValues();
        if (!empty($values['value'])) {
            $event->getQueryBuilder()->andWhere($event->getFilterQuery()->getExpr()->eq($event->getField(), $values['value']));
        }
    }

    public function filterChoice(ApplyFilterEvent $event)
    {
        if ( ! $event->getQueryBuilder() instanceof QueryBuilder) {
            return;
        }

        $values = $event->getValues();
        if (!empty($values['value'])) {
            // alias.field -> alias_field
            $fieldName = str_replace('.', '_', $event->getField());

            $event->getQueryBuilder()
                  ->andWhere($event->getFilterQuery()->getExpr()->eq($event->getField(), ':' . $fieldName))
                  ->setParameter($fieldName, $values['value']);
        }
    }

    public function filterDate(ApplyFilterEvent $event)
    {
        if ( ! $event->getQueryBuilder() instanceof QueryBuilder) {
            return;
        }

        $values = $event->getValues();
        if ($values['value'] instanceof \DateTime) {
            $date = $values['value']->format(Expr::SQL_DATE);
            $event->getQueryBuilder()->andWhere($event->getFilterQuery()->getExpr()->eq($event->getField(), $event->getFilterQuery()->getExpr()->literal($date)));
        }
    }

    public function filterDateRange(ApplyFilterEvent $event)
    {
        if ( ! $event->getQueryBuilder() instanceof QueryBuilder) {
            return;
        }

        $values = $event->getValues();
        $value = $values['value'];
        if(isset($value['left_date'][0]) || $value['right_date'][0]){
            $event->getQueryBuilder()->andWhere($event->getFilterQuery()->getExpr()->dateInRange($event->getField(), $value['left_date'][0], $value['right_date'][0]));
        }
    }

    public function filterEntity(ApplyFilterEvent $event)
    {
        if ( ! $event->getQueryBuilder() instanceof QueryBuilder) {
            return;
        }

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
                    $event->getQueryBuilder()->andWhere($event->getFilterQuery()->getExpr()->in($event->getField(), $ids));
                }

            } else {
                if (!is_callable(array($values['value'], 'getId'))) {
                    throw new \Exception(sprintf('Can\'t call method "getId()" on an instance of "%s"', get_class($values['value'])));
                }

                $event->getQueryBuilder()->andWhere($event->getFilterQuery()->getExpr()->eq($event->getField(), $values['value']->getId()));
            }
        }
    }

    public function filterNumber(ApplyFilterEvent $event)
    {
        if ( ! $event->getQueryBuilder() instanceof QueryBuilder) {
            return;
        }

        $values = $event->getValues();
        if (!empty($values['value'])) {
            $op = $values['condition_operator'];
            $event->getQueryBuilder()->andWhere($event->getFilterQuery()->getExpr()->$op($event->getField(), $values['value']));
        }
    }

    public function filterNumberRange(ApplyFilterEvent $event)
    {
        if ( ! $event->getQueryBuilder() instanceof QueryBuilder) {
            return;
        }

        $values = $event->getValues();
        $value = $values['value'];

        if (isset($value['left_number'][0])) {
            $leftCond   = $value['left_number']['condition_operator'];
            $leftValue  = $value['left_number'][0];

            $event->getQueryBuilder()->andWhere($event->getFilterQuery()->getExpr()->$leftCond($event->getField(), $leftValue));
        }

        if (isset($value['right_number'][0])) {
            $rightCond  = $value['right_number']['condition_operator'];
            $rightValue = $value['right_number'][0];

            $event->getQueryBuilder()->andWhere($event->getFilterQuery()->getExpr()->$rightCond($event->getField(), $rightValue));
        }
    }

    public function filterText(ApplyFilterEvent $event)
    {
        if ( ! $event->getQueryBuilder() instanceof QueryBuilder) {
            return;
        }

        $values = $event->getValues();
        if (!empty($values['value'])) {
            $event->getQueryBuilder()->andWhere($event->getFilterQuery()->getExpr()->stringLike($event->getField(), $values['value'], $values['condition_pattern']));
        }
    }
}
