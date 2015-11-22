<?php

namespace Cerbere\Event;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CerbereLoggerListener implements EventSubscriberInterface, LoggerAwareInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Mapping of event to log level.
     *
     * @var array
     */
    protected $logLevelMappings = array(
      CerbereEvents::APPLICATION_FILE_DISCOVER => LogLevel::INFO,
    );

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
          CerbereEvents::APPLICATION_FILE_DISCOVER => array('onFileDiscover', 0),
        );
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->setLogger($logger);
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
     * Sets the log level mapping for an event.
     *
     * @param string       $eventName
     * @param string|false $logLevel
     *
     * @return $this
     */
    public function setLogLevelMapping($eventName, $logLevel)
    {
        $this->logLevelMappings[$eventName] = $logLevel;

        return $this;
    }

    /**
     * @param string $method
     * @param string $message
     * @param array  $context
     */
    protected function log($method, $message, array $context = array())
    {
        $this->logger->$method($message, $context);
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
}
