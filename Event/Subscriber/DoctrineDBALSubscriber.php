<?php

namespace Lexik\Bundle\FormFilterBundle\Event\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 *  Register listeners to compute conditions to be applied on a Doctrine DBAL query builder.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class DoctrineDBALSubscriber extends AbstractDoctrineSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            // Lexik form filter types
            'lexik_form_filter.apply.dbal.filter_boolean'        => array('filterBoolean'),
            'lexik_form_filter.apply.dbal.filter_checkbox'       => array('filterCheckbox'),
            'lexik_form_filter.apply.dbal.filter_choice'         => array('filterValue'),
            'lexik_form_filter.apply.dbal.filter_date'           => array('filterDate'),
            'lexik_form_filter.apply.dbal.filter_date_range'     => array('filterDateRange'),
            'lexik_form_filter.apply.dbal.filter_datetime'       => array('filterDateTime'),
            'lexik_form_filter.apply.dbal.filter_datetime_range' => array('filterDateTimeRange'),
            'lexik_form_filter.apply.dbal.filter_number'         => array('filterNumber'),
            'lexik_form_filter.apply.dbal.filter_number_range'   => array('filterNumberRange'),
            'lexik_form_filter.apply.dbal.filter_text'           => array('filterText'),

            // Symfony2 field types
            'lexik_form_filter.apply.dbal.text'     => array('filterText'),
            'lexik_form_filter.apply.dbal.email'    => array('filterValue'),
            'lexik_form_filter.apply.dbal.integer'  => array('filterValue'),
            'lexik_form_filter.apply.dbal.money'    => array('filterValue'),
            'lexik_form_filter.apply.dbal.number'   => array('filterValue'),
            'lexik_form_filter.apply.dbal.percent'  => array('filterValue'),
            'lexik_form_filter.apply.dbal.search'   => array('filterValue'),
            'lexik_form_filter.apply.dbal.url'      => array('filterValue'),
            'lexik_form_filter.apply.dbal.choice'   => array('filterValue'),
            'lexik_form_filter.apply.dbal.country'  => array('filterValue'),
            'lexik_form_filter.apply.dbal.language' => array('filterValue'),
            'lexik_form_filter.apply.dbal.locale'   => array('filterValue'),
            'lexik_form_filter.apply.dbal.timezone' => array('filterValue'),
            'lexik_form_filter.apply.dbal.date'     => array('filterDate'),
            'lexik_form_filter.apply.dbal.datetime' => array('filterDate'),
            'lexik_form_filter.apply.dbal.birthday' => array('filterDate'),
            'lexik_form_filter.apply.dbal.checkbox' => array('filterValue'),
            'lexik_form_filter.apply.dbal.radio'    => array('filterValue'),
        );
    }
}
