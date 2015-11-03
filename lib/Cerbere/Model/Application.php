<?php

namespace Cerbere;

use Cerbere\Model\Config;
use Cerbere\Model\Project;
use Cerbere\Parser\Info;
use Cerbere\Parser\Make;

class Application {
    /**
     * @var Config
     */
    protected $config;

    /**
     *
     */
    public function __construct()
    {

    }

    /**
     * @param string $filename
     */
    public function loadConfigFilename($filename)
    {
        $this->config = Config::loadFromFile($filename);
    }

    /**
     * @param array $patterns
     * @return Project[]
     */
    public function getProjects($patterns)
    {
        $projects = array();

        foreach ($patterns as $pattern) {
            if ($files = glob($pattern)) {
                foreach ($files as $file) {
                    if (file_exists($file)) {
                        if (preg_match('/\.info$/', $file)) {
                            $info = new Info($file);
                            $projects[] = $info->getProject();
                        } elseif (preg_match('/\.make$/', $file)) {
                            $make = new Make($file);
                            $projects = array_merge($projects, $make->getProjects());
                        }
                    }
                }
            }
        }

        return $projects;
    }
}
