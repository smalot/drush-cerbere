<?php

namespace Cerbere\Model;

use Cerbere\Action\ActionInterface;
use Cerbere\Notification\NotificationInterface;
use Cerbere\Parser\Info;
use Cerbere\Parser\Make;
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
     *
     */
    public function __construct()
    {
        $this->config   = new Config();
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
        $files = glob($pattern);

        foreach ($files as $file) {
            if (file_exists($file)) {
                if (preg_match('/\.info$/', $file)) {
                    $info             = new Info($file);
                    $this->projects[] = $info->getProject();
                } elseif (preg_match('/\.make$/', $file)) {
                    $make           = new Make($file);
                    $this->projects = array_merge($this->projects, $make->getProjects());
                }
            }
        }
    }

    /**
     * @param \Cerbere\Action\ActionInterface $action
     */
    public function process(ActionInterface $action)
    {
        foreach ($this->projects as $project) {
            $action->process($project);
        }
    }
}
