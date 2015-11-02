<?php

namespace Cerbere\Parser;

use Cerbere\Model\Project;

/**
 * Class Make
 *
 * @package Cerbere\Parser
 */
class Make extends Ini
{
    /**
     * @var string
     */
    protected $filename;

    /**
     * @var array
     */
    protected $data;

    /**
     * @param string $filename
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
        $this->init();
    }

    /**
     *
     */
    protected function init()
    {
        $this->data = $this->parseFile($this->filename);

        // Core attribute is mandatory since Drupal 7.x.
        $this->data += array('core' => '6.x', 'api' => '', 'projects' => array(), 'libraries' => array());

        // Wrap project into objects.
        foreach ($this->data['projects'] as $project_name => $project_details) {
            $project_details['version'] = $this->getCore() . '-' . $project_details['version'];
            $project = new Project($project_name, $this->getCore(), $project_details['version']);
            $project->setDetails($project_details);

            $this->data['projects'][$project_name] = $project;
        }

        // Todo: wrap libraries into objects.
    }

    /**
     * @return string
     */
    public function getCore()
    {
        return $this->data['core'];
    }

    /**
     * @return string
     */
    public function getApi()
    {
        return $this->data['api'];
    }

    /**
     * @return Project[]
     */
    public function getProjects()
    {
        return $this->data['projects'];
    }

    /**
     * @param string $project
     *
     * @return bool
     */
    public function hasProject($project)
    {
        return isset($this->data['projects'][$project]);
    }

    /**
     * @param string $project
     *
     * @return Project
     */
    public function getProject($project)
    {
        return $this->data['projects'][$project];
    }

    /**
     * @return array
     */
    public function getLibraries()
    {
        return $this->data['libraries'];
    }
}
