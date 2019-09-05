<?php

namespace Lexik\Bundle\FormFilterBundle\Event;

use Symfony\Component\EventDispatcher\Event as LegacyEvent;
use Symfony\Contracts\EventDispatcher\Event as ContractsEvent;

if (\class_exists(ContractsEvent::class)) {
    abstract class Event extends ContractsEvent
    {
    }
} else {
    abstract class Event extends LegacyEvent
    {
    }
}
