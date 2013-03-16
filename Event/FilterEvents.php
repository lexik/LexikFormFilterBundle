<?php

namespace Lexik\Bundle\FormFilterBundle\Event;

/**
 * Available filter events.
 *
 * @author Cédric Girard <c.girard@lexi.fr>
 */
class FilterEvents
{
    const PREPARE = 'lexik_filter.prepare';

    /**
     * @deprecated Deprecated since version 2.0, to be removed in 2.1.
     */
    const GET_FILTER = 'lexik_filter.get';
}
