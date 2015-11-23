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
     * @var ProgressBar
     */
    protected $progress;

    /**
     * CerbereConsoleListener constructor.
     * @param \Symfony\Component\Console\Helper\ProgressBar|null $progress
     * @param \Symfony\Component\Console\Output\OutputInterface|null $output
     */
    public function __construct(ProgressBar $progress = null, OutputInterface $output = null)
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
          CerbereEvents::CERBERE_PRE_ACTION      => array('onCerberePreAction', 0),
          CerbereEvents::CERBERE_DO_ACTION       => array('onCerbereDoAction', 0),
          CerbereEvents::CERBERE_POST_ACTION     => array('onCerberePostAction', 0),
        );
    }

    /**
     * @param \Cerbere\Event\CerbereDoActionEvent $event
     */
    public function onCerbereDoAction(CerbereDoActionEvent $event)
    {
        $this->progress->setMessage($event->getProject()->getName(), 'project');
        $this->progress->advance();
    }

    /**
     * @param \Cerbere\Event\CerbereFileDiscoverEvent $event
     */
    public function onCerbereFileDiscovered(CerbereFileDiscoverEvent $event)
    {
//        $this->output->getErrorOutput()->writeln($event->getFilename());
    }

    /**
     * @param CerberePostActionEvent $event
     */
    public function onCerberePostAction(CerberePostActionEvent $event)
    {
        $this->progress->setMessage('Action ended');
        $this->progress->setMessage('', 'project');
        $this->progress->finish();

        // Returns and jump new line.
        $this->output->getErrorOutput()->writeln('');
        $this->output->getErrorOutput()->writeln('');
    }

    /**
     * @param CerberePreActionEvent $event
     */
    public function onCerberePreAction(CerberePreActionEvent $event)
    {
        // Returns and jump new line.
        $this->output->getErrorOutput()->writeln('');

        $format = " Project: %project%\n";
        $format.= ProgressBar::getFormatDefinition('debug');

        $progress = new ProgressBar($this->output, count($event->getProjects()));
        $progress->setFormat($format);
        $progress->setRedrawFrequency(1);
        $progress->setMessage('Action starts');

        $this->progress = $progress;
    }
}
