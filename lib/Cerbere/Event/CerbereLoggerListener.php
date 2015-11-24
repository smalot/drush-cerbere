<?php

/**
 * Drush Cerbere command line tools.
 * Copyright (C) 2015 - Sebastien Malot <sebastien@malot.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace Cerbere\Event;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CerbereLoggerListener
 * @package Cerbere\Event
 */
class CerbereLoggerListener implements EventSubscriberInterface, LoggerAwareInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Mapping of event to log level.
     *
     * @var array
     */
    protected $logLevelMappings = array(
      CerbereEvents::CERBERE_FILE_DISCOVERED => LogLevel::INFO,
    );

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->setLogger($logger);
    }

    /**
     * Returns the log level mapping for an event.
     *
     * @param string $eventName
     *
     * @return string
     *
     * @throws \DomainException
     */
    public function getLogLevelMapping($eventName)
    {
        if (!isset($this->logLevelMappings[$eventName])) {
            throw new \DomainException('Unknown event: ' . $eventName);
        }

        return $this->logLevelMappings[$eventName];
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger
     *
     * @return null
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
          CerbereEvents::CERBERE_FILE_DISCOVERED => array('onFileDiscover', 0),
        );
    }

    /**
     * @param CerbereFileDiscoverEvent $event
     */
    public function onFileDiscover(CerbereFileDiscoverEvent $event)
    {
        $context = array(
          'parser'   => $event->getParser()->getCode(),
          'filename' => $event->getFilename(),
        );

        $this->log('debug', 'New file discovered', $context);
    }

    /**
     * @param string $method
     * @param string $message
     * @param array $context
     */
    protected function log($method, $message, array $context = array())
    {
        $this->logger->$method($message, $context);
    }

    /**
     * Sets the log level mapping for an event.
     *
     * @param string $eventName
     * @param string|false $logLevel
     *
     * @return $this
     */
    public function setLogLevelMapping($eventName, $logLevel)
    {
        $this->logLevelMappings[$eventName] = $logLevel;

        return $this;
    }
}
