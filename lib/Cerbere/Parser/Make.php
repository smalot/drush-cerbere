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
    protected $core;

    /**
     * @var string
     */
    protected $api;

    /**
     * @var Project[]
     */
    protected $projects;

    /**
     * @var array
     */
    protected $libraries;

    /**
     *
     */
    public function __construct()
    {

    }

    /**
     * @return string
     */
    public function getCode()
    {
        return 'make';
    }

    /**
     * @parser string $filename
     * @return bool
     */
    public function supportedFile($filename)
    {
        return preg_match('/\.make$/', $filename) > 0;
    }

    /**
     * @param string $content
     *
     * @return void
     */
    public function processContent($content)
    {
        $data = $this->parseContent($content);

        // Core attribute is mandatory since Drupal 7.x.
        $data += array('core' => '6.x', 'api' => '', 'projects' => array(), 'libraries' => array());

        $this->core      = $data['core'];
        $this->api       = $data['api'];
        $this->projects  = array();
        $this->libraries = $data['libraries'];

        // Wrap project into objects.
        foreach ($data['projects'] as $project_name => $project_details) {
            $project_details['version'] = $this->getCore() . '-' . $project_details['version'];

            $project = new Project($project_name, $this->getCore(), $project_details['version']);
            $project->setDetails($project_details);

            $this->projects[$project_name] = $project;
        }

        // Todo: wrap libraries into objects.
    }

    /**
     * @return string
     */
    public function getCore()
    {
        return $this->core;
    }

    /**
     * @return string
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * @return Project[]
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * @param string $project
     *
     * @return bool
     */
    public function hasProject($project)
    {
        return isset($this->projects[$project]);
    }

    /**
     * @param string $project
     *
     * @return Project
     */
    public function getProject($project)
    {
        return $this->projects[$project];
    }

    /**
     * @return array
     */
    public function getLibraries()
    {
        return $this->libraries;
    }
}
