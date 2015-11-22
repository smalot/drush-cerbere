<?php

namespace Cerbere;

use Cerbere\Action\ActionInterface;
use Cerbere\Event\CerbereEvents;
use Cerbere\Event\CerbereFileDiscoverEvent;
use Cerbere\Event\CerbereLoggerListener;
use Cerbere\Model\Job;
use Cerbere\Model\Project;
use Cerbere\Parser\ParserInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class Cerbere
 *
 * @package Cerbere
 */
class Cerbere
{
    /**
     * @var ParserInterface[]
     */
    protected $parsers = array();

    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     */
    public function __construct()
    {
    }

    /**
     * @param CerbereLoggerListener $listener
     *
     * @return $this
     */
    public function addLoggerListener(CerbereLoggerListener $listener)
    {
        $this->getDispatcher()->addSubscriber($listener);

        return $this;
    }

    /**
     * Gets the dispatcher used by this library to dispatch events.
     *
     * @return EventDispatcherInterface
     */
    public function getDispatcher()
    {
        if (!isset($this->dispatcher)) {
            $this->dispatcher = new EventDispatcher();
        }

        return $this->dispatcher;
    }

    /**
     * Sets the dispatcher used by this library to dispatch events.
     *
     * @param EventDispatcherInterface $dispatcher
     *   The Symfony event dispatcher object.
     *
     * @return $this
     */
    public function setDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }

    /**
     * @param ParserInterface $parser
     *
     * @return $this
     */
    public function addParser(ParserInterface $parser)
    {
        $this->parsers[$parser->getCode()] = $parser;

        return $this;
    }

    /**
     * @return ParserInterface[]
     */
    public function getParsers()
    {
        return $this->parsers;
    }

    /**
     * @param Job $job
     * @param ActionInterface $action
     * @param array $options
     *
     * @return $this
     */
    public function run(Job $job, ActionInterface $action, $options = array())
    {
        // Download remote project.
        if ($dir = $job->checkoutRepository()) {
            // Move to project folder.
            $currentDirectory = getcwd();
            chdir($dir);

            // Load projects from repository.
            $projects = $this->getProjectsFromPatterns($job->getPatterns());

            // Do cerbere action.
            $report = $action->process($projects, $options);

            // Restore initial directory.
            chdir($currentDirectory);

            return $report;
        } else {

            return false;
        }
    }

    /**
     * @return Project[]
     */
    public function getProjectsFromPatterns($patterns)
    {
        $projects = array();

        foreach ($patterns as $pattern) {
            $projects = array_merge($projects, $this->getProjectsFromPattern($pattern));
        }

        return $projects;
    }

    /**
     * @param string $pattern
     *
     * @return $this
     */
    public function getProjectsFromPattern($pattern)
    {
        $projects = array();
        $dispatcher = $this->getDispatcher();
        $files = glob($pattern);

        foreach ($files as $file) {
            foreach ($this->getParsers() as $parser) {
                if ($parser->supportedFile($file)) {
                    $event = new CerbereFileDiscoverEvent($this, $file, $parser);
                    $dispatcher->dispatch(CerbereEvents::APPLICATION_FILE_DISCOVERED, $event);
                    $parser->processFile($file);
                    $projects = array_merge($projects, $parser->getProjects());
                }
            }
        }

        return $projects;
    }
}
