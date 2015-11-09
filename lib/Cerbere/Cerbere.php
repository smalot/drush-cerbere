<?php

namespace Cerbere;

use Cerbere\Action\ActionInterface;
use Cerbere\Event\CerbereLoggerListener;
use Cerbere\Model\Config;
use Cerbere\Model\Part;
use Cerbere\Model\Project;
use Cerbere\Notification\NotificationInterface;
use Cerbere\Parser\ParserInterface;
use Cerbere\Versioning\VersioningInterface;
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
     * @var Config
     */
    protected $config;

    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * @var VersioningInterface[]
     */
    protected $versionings;

    /**
     * @var ParserInterface[]
     */
    protected $parsers;

    /**
     * @var ActionInterface[]
     */
    protected $actions;

    /**
     * @var NotificationInterface[]
     */
    protected $notifications;

    /**
     * @var Part[]
     */
    protected $parts;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;

        $this->versionings   = array();
        $this->parsers       = array();
        $this->actions       = array();
        $this->notifications = array();
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
     * @param VersioningInterface $versioning
     *
     * @return $this
     */
    public function addVersioning(VersioningInterface $versioning)
    {
        $this->versionings[$versioning->getCode()] = $versioning;

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
     * @param ActionInterface $action
     *
     * @return $this
     */
    public function addAction(ActionInterface $action)
    {
        $this->actions[$action->getCode()] = $action;

        return $this;
    }

    /**
     * @param NotificationInterface $notification
     *
     * @return $this
     */
    public function addNotification(NotificationInterface $notification)
    {
        $this->notifications[$notification->getCode()] = $notification;

        return $this;
    }

    /**
     * @throws \Exception
     */
//    public function retrieveFilesFromVersioning()
//    {
//        if (!empty($this->config['vcs']['type'])) {
//            $versioning = $this->getVersioning($this->config['vcs']['type']);
//            $versioning->prepare($this->config);
//            $directory = $versioning->getTemporaryDirectory();
//            $versioning->process($directory);
//            chdir($directory);
//        }
//
//        return $this;
//    }

    /**
     * @param string $versioning
     *
     * @return VersioningInterface
     * @throws \DomainException
     */
    public function getVersioning($versioning)
    {
        if (isset($this->versionings[$versioning])) {
            return $this->versionings[$versioning];
        }

        throw new \DomainException('Unregistered versioning');
    }

    /**
     * @param string $notification
     *
     * @return NotificationInterface
     * @throws \DomainException
     */
    public function getNotification($notification)
    {
        if (isset($this->notifications[$notification])) {
            return $this->notifications[$notification];
        }

        throw new \DomainException('Unregistered notification');
    }

    /**
     * @return ParserInterface[]
     */
    public function getParsers()
    {
        return $this->parsers;
    }

    /**
     * @param string $action
     *
     * @return ActionInterface
     * @throws \DomainException
     */
    public function getAction($action)
    {
        if (isset($this->actions[$action])) {
            return $this->actions[$action];
        }

        throw new \DomainException('Unregistered action');
    }

    /**
     * @param Part[] $parts
     *
     * @return $this
     */
    public function setParts($parts)
    {
        $this->parts = $parts;

        return $this;
    }

    /**
     * @return Part[]
     */
    public function getParts()
    {
        if (is_null($this->parts)) {
            $this->parts = array();

            foreach ($this->config['parts'] as $name => $part) {
                $this->parts[$name] = Part::generateFromConfig($this, $part);
            }
        }

        return $this->parts;
    }

    /**
     * @param array $actions
     *
     * @return ActionInterface[]
     */
    protected function getActionsByCode($actions)
    {
        $action_instances = array();

        foreach ($actions as $position => $action) {
            if (!$action instanceof ActionInterface) {
                $action = $this->getAction($action);
            }

            $action_instances[$action->getCode()] = $action;
        }

        return $action_instances;
    }

    /**
     * @param string[] $actions
     *
     * @return $this
     */
    public function run($actions)
    {
        $actions = $this->getActionsByCode($actions);
        $parts   = $this->getParts();
        $reports = array();
        $currentDirectory = getcwd();

        foreach ($parts as $part_name => $part) {
            drush_print('part: ' . $part->getTitle());

            /** @var ActionInterface $action */
            foreach ($actions as $action_name => $action) {
                drush_print(' - action: ' . $action->getCode());

                if ($result = $action->process($part)) {
                    $reports = array_merge($reports, $result);
                }
            }

            foreach ($this->notifications as $notification) {
                $config = $this->config['notifications'][$notification->getCode()];
                $notification->prepare($config);
                $notification->notify('update', $reports);
            }

            $reports = array();

            // Restore initial directory.
            chdir($currentDirectory);
        }

        return $this;


        // Load notification class before running action.
        if (is_string($notification)) {
            $notification = $this->getNotification($notification);
        } elseif (!$notification instanceof NotificationInterface) {
            $notification = $this->getNotification($this->config['report-format']);
        }

        if (!$action instanceof ActionInterface) {
            $action = $this->getAction($action);
        }

        $action->prepare($this->config);
        $report = array();

        /** @var Project $project */
        foreach ($this->projects as $project) {
            if ($project_report = $action->process($project)) {
                $report[$project->getProject()] = $project_report;
            }
        }

        $notification->prepare($this->config);
        $notification->notify($action->getCode(), $report);

        return $this;
    }
}
