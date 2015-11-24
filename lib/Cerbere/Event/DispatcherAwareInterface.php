<?php

namespace Cerbere\Event;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Interface DispatcherAwareInterface
 * @package Cerbere\Event
 */
interface DispatcherAwareInterface
{
    /**
     * Gets the dispatcher used by this library to dispatch events.
     *
     * @return EventDispatcherInterface
     */
    public function getDispatcher();

    /**
     * Sets the dispatcher used by this library to dispatch events.
     *
     * @param EventDispatcherInterface $dispatcher
     *   The Symfony event dispatcher object.
     */
    public function setDispatcher(EventDispatcherInterface $dispatcher);
}
