<?php

namespace Cerbere;

use Cerbere\Action\ActionInterface;
use Cerbere\Event\CerbereEvents;
use Cerbere\Event\CerbereFileDiscoverEvent;
use Cerbere\Event\CerbereLoggerListener;
use Cerbere\Event\CerberePostActionEvent;
use Cerbere\Event\CerberePreActionEvent;
use Cerbere\Event\DispatcherAwareInterface;
use Cerbere\Model\Job;
use Cerbere\Model\Project;
use Cerbere\Parser\ParserInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class Cerbere
 *
 * @package Cerbere
 */
class Cerbere implements DispatcherAwareInterface
{
    /**
     * @var ParserInterface[]
     */
    protected $parsers = array();

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     */
    public function __construct()
    {
    }

    /**
     * @param EventSubscriberInterface $listener
     */
    public function addLoggerListener(EventSubscriberInterface $listener)
    {
        $this->getDispatcher()->addSubscriber($listener);
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
     * @param string $code
     *
     * @return ParserInterface|null
     */
    public function getParser($code)
    {
        if (isset($this->parsers[$code])) {
            return $this->parsers[$code];
        }

        return null;
    }

    /**
     * @param Job             $job
     * @param ActionInterface $action
     * @param array           $options
     *
     * @return array
     */
    public function run(Job $job, ActionInterface $action, $options = array())
    {
        // Download remote project if remote.
        $dir = $job->checkoutRepository();

        // Move to project folder.
        $currentDirectory = getcwd();
        chdir($dir);

        // Load projects from repository.
        $projects = $this->getProjectsFromPatterns($job->getPatterns(), $job->isPatternNested());

        $event = new CerberePreActionEvent($this, $job, $action, $projects);
        $this->getDispatcher()->dispatch(CerbereEvents::CERBERE_PRE_ACTION, $event);

        // Do cerbere action.
        $report = $action->process($projects, $options);

        $event = new CerberePostActionEvent($this, $job, $action, $projects);
        $this->getDispatcher()->dispatch(CerbereEvents::CERBERE_POST_ACTION, $event);

        // Restore initial directory.
        chdir($currentDirectory);

        return $report;
    }

    /**
     * @param array      $patterns
     * @param bool|false $nested
     *
     * @return Project[]
     */
    public function getProjectsFromPatterns($patterns, $nested = false)
    {
        $projects = array();

        foreach ($patterns as $pattern) {
            $projects = array_merge($projects, $this->getProjectsFromPattern($pattern, $nested));
        }

        return $projects;
    }

    /**
     * @param string     $pattern
     * @param bool|false $nested
     *
     * @return Project[]
     */
    public function getProjectsFromPattern($pattern, $nested = false)
    {
        $projects   = array();
        $dispatcher = $this->getDispatcher();
        $files      = $this->getFilesFromPattern($pattern, $nested);

        foreach ($files as $file) {
            foreach ($this->getParsers() as $parser) {
                if ($parser->supportedFile($file)) {
                    $event = new CerbereFileDiscoverEvent($this, $file, $parser);
                    $dispatcher->dispatch(CerbereEvents::CERBERE_FILE_DISCOVERED, $event);
                    $parser->processFile($file);
                    $projects = array_merge($projects, $parser->getProjects());
                }
            }
        }

        return $projects;
    }

    /**
     * @param string $pattern
     * @param bool|false $nested
     * @param int $flags
     *
     * @return array
     */
    public function getFilesFromPattern($pattern, $nested = false, $flags = 0)
    {
        $files = glob($pattern, $flags);

        if ($nested) {
            foreach (glob(dirname($pattern) . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
                $files = array_merge($files, $this->getFilesFromPattern($dir . DIRECTORY_SEPARATOR . basename($pattern), $nested, $flags));
            }
        }

        return $files;
    }

    /**
     * @return ParserInterface[]
     */
    public function getParsers()
    {
        return $this->parsers;
    }
}
