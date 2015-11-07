<?php

namespace Cerbere\Model;

use Cerbere\Action\ActionInterface;
use Cerbere\Notification\NotificationInterface;
use Cerbere\Parser\ParserInterface;
use Cerbere\Versioning\VersioningInterface;

/**
 * Class Application
 *
 * @package Cerbere
 */
class Application
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Project[]
     */
    protected $projects;

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
     * @param \Cerbere\Model\Config $config
     */
    public function __construct(Config $config)
    {
        $this->config   = $config;
        $this->projects = array();

        $this->versionings   = array();
        $this->parsers       = array();
        $this->actions       = array();
        $this->notifications = array();
    }

    /**
     * @param VersioningInterface $versioning
     */
    public function registerVersioning(VersioningInterface $versioning)
    {
        $this->versionings[$versioning->getCode()] = $versioning;
    }

    /**
     * @param ParserInterface $parser
     */
    public function registerParser(ParserInterface $parser)
    {
        $this->parsers[$parser->getCode()] = $parser;
    }

    /**
     * @param ActionInterface $action
     */
    public function registerAction(ActionInterface $action)
    {
        $this->actions[$action->getCode()] = $action;
    }

    /**
     * @param NotificationInterface $notification
     */
    public function registerNotification(NotificationInterface $notification)
    {
        $this->notifications[$notification->getCode()] = $notification;
    }

    /**
     * @param Config $config
     */
    public function loadConfig(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param array $patterns
     */
    public function loadProjectsFromPatterns($patterns)
    {
        foreach ($patterns as $pattern) {
            $this->loadProjectsFromPattern($pattern);
        }
    }

    /**
     * @param string $pattern
     */
    public function loadProjectsFromPattern($pattern)
    {
        $filenames = glob($pattern);

        foreach ($filenames as $filename) {
            /** @var ParserInterface $parser */
            foreach ($this->parsers as $parser) {
                if ($parser->supportedFile($filename)) {
                    $parser->processFile($filename);
                    $this->projects = array_merge($this->projects, $parser->getProjects());
                }
            }
        }
    }

    /**
     * @param ActionInterface|string            $action
     * @param NotificationInterface|string|null $notification
     *
     * @throws \Exception
     */
    public function process($action, $notification = null)
    {
        // Load notification class before running action.
        if (is_string($notification)) {
            $notification = $this->getRegisteredNotification($notification);
        } elseif (!$notification instanceof NotificationInterface) {
            $notification = $this->getRegisteredNotification($this->config['report-format']);
        }

        if (!$action instanceof ActionInterface) {
            $action = $this->getRegisteredAction($action);
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
    }

    /**
     * @param string $notification
     *
     * @return NotificationInterface
     * @throws \Exception
     */
    public function getRegisteredNotification($notification)
    {
        if (isset($this->notifications[$notification])) {
            return $this->notifications[$notification];
        }

        throw new \Exception('Unregistered notification');
    }

    /**
     * @param string $action
     *
     * @return ActionInterface
     * @throws \Exception
     */
    public function getRegisteredAction($action)
    {
        if (isset($this->actions[$action])) {
            return $this->actions[$action];
        }

        throw new \Exception('Unregistered action');
    }
}
