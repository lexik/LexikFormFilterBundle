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
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            // Lexik form filter types
            'lexik_form_filter.apply.dbal.filter_boolean' => ['filterBoolean'],
            'lexik_form_filter.apply.dbal.filter_checkbox' => ['filterCheckbox'],
            'lexik_form_filter.apply.dbal.filter_choice' => ['filterValue'],
            'lexik_form_filter.apply.dbal.filter_date' => ['filterDate'],
            'lexik_form_filter.apply.dbal.filter_date_range' => ['filterDateRange'],
            'lexik_form_filter.apply.dbal.filter_datetime' => ['filterDateTime'],
            'lexik_form_filter.apply.dbal.filter_datetime_range' => ['filterDateTimeRange'],
            'lexik_form_filter.apply.dbal.filter_number' => ['filterNumber'],
            'lexik_form_filter.apply.dbal.filter_number_range' => ['filterNumberRange'],
            'lexik_form_filter.apply.dbal.filter_text' => ['filterText'],
            // Symfony field types
            'lexik_form_filter.apply.dbal.text' => ['filterText'],
            'lexik_form_filter.apply.dbal.email' => ['filterValue'],
            'lexik_form_filter.apply.dbal.integer' => ['filterValue'],
            'lexik_form_filter.apply.dbal.money' => ['filterValue'],
            'lexik_form_filter.apply.dbal.number' => ['filterValue'],
            'lexik_form_filter.apply.dbal.percent' => ['filterValue'],
            'lexik_form_filter.apply.dbal.search' => ['filterValue'],
            'lexik_form_filter.apply.dbal.url' => ['filterValue'],
            'lexik_form_filter.apply.dbal.choice' => ['filterValue'],
            'lexik_form_filter.apply.dbal.country' => ['filterValue'],
            'lexik_form_filter.apply.dbal.language' => ['filterValue'],
            'lexik_form_filter.apply.dbal.locale' => ['filterValue'],
            'lexik_form_filter.apply.dbal.timezone' => ['filterValue'],
            'lexik_form_filter.apply.dbal.date' => ['filterDate'],
            'lexik_form_filter.apply.dbal.datetime' => ['filterDate'],
            'lexik_form_filter.apply.dbal.birthday' => ['filterDate'],
            'lexik_form_filter.apply.dbal.checkbox' => ['filterValue'],
            'lexik_form_filter.apply.dbal.radio' => ['filterValue'],
        ];
    }
}
