<?php

namespace Cerbere\Event;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CerbereConsoleListener
 * @package Cerbere\Event
 */
class CerbereConsoleListener implements EventSubscriberInterface
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * CerbereConsoleListener constructor.
     * @param \Symfony\Component\Console\Output\OutputInterface|null $output
     */
    public function __construct(OutputInterface $output = null)
    {
        if (null === $output) {
            $output = new ConsoleOutput();
        }

        $this->output = $output;
    }

    /**
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param OutputInterface $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
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
          CerbereEvents::CERBERE_FILE_DISCOVERED => array('onCerbereFileDiscovered', 0),
        );
    }

    /**
     * @param \Cerbere\Event\CerbereFileDiscoverEvent $event
     */
    public function onCerbereFileDiscovered(CerbereFileDiscoverEvent $event)
    {
        $this->output->getErrorOutput()->writeln($event->getFilename());
    }
}
