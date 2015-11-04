<?php

namespace Cerbere\Model;

use Cerbere\Action\ActionInterface;
use Cerbere\Model\Config;
use Cerbere\Model\Project;
use Cerbere\Parser\Info;
use Cerbere\Parser\Make;

/**
 * Class Application
 * @package Cerbere
 */
class Application {
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Project[]
     */
    protected $projects;

    /**
     *
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->projects = array();
    }

    /**
     * @param array $patterns
     */
    public function loadProjects($patterns)
    {
        $this->projects = array();

        foreach ($patterns as $pattern) {
            if ($files = glob($pattern)) {
                foreach ($files as $file) {
                    if (file_exists($file)) {
                        if (preg_match('/\.info$/', $file)) {
                            $info = new Info($file);
                            $this->projects[] = $info->getProject();
                        } elseif (preg_match('/\.make$/', $file)) {
                            $make = new Make($file);
                            $this->projects = array_merge($this->projects, $make->getProjects());
                        }
                    }
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
