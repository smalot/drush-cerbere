<?php

namespace Cerbere\Model;

use Cerbere\Cerbere;
use Cerbere\Event\CerbereEvents;
use Cerbere\Event\CerbereFileDiscoverEvent;

/**
 * Class Part
 *
 * @package Cerbere\Model
 */
class Part
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var Cerbere
     */
    protected $cerbere;

    /**
     * @var array
     */
    protected $versioning;

    /**
     * @var string
     */
    protected $workingDirectory;

    /**
     * @var array
     */
    protected $patterns;

    /**
     * @var Project[]
     */
    protected $projects;

    /**
     * @param Cerbere $cerbere
     */
    public function __construct(Cerbere $cerbere)
    {
        $this->cerbere          = $cerbere;
        $this->versioning       = array();
        $this->workingDirectory = '';
        $this->patterns         = array();
        $this->projects         = null;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return array
     */
    public function getVersioning()
    {
        return $this->versioning;
    }

    /**
     * @param array $versioning
     *
     * @return Part
     */
    public function setVersioning($versioning)
    {
        $this->versioning = $versioning;

        return $this;
    }

    /**
     * @return string
     */
    public function getWorkingDirectory()
    {
        return $this->workingDirectory;
    }

    /**
     * @param string $workingDirectory
     *
     * @return Part
     */
    public function setWorkingDirectory($workingDirectory)
    {
        $this->workingDirectory = $workingDirectory;

        return $this;
    }

    /**
     * @return array
     */
    public function getPatterns()
    {
        return $this->patterns;
    }

    /**
     * @param array $patterns
     *
     * @return Part
     */
    public function setPatterns($patterns)
    {
        $this->patterns = $patterns;

        return $this;
    }

    /**
     * @return Project[]
     */
    public function getProjects()
    {
        if (is_null($this->projects)) {
            $this->projects = array();
            $this->loadProjectsFromPatterns($this->getPatterns());
        }

        return $this->projects;
    }

    /**
     * @param Project[] $projects
     *
     * @return Part
     */
    public function setProjects($projects)
    {
        $this->projects = $projects;

        return $this;
    }

    /**
     * @return Part
     */
    public function checkoutRepository()
    {
        $versioning = $this->cerbere->getVersioning($this->versioning['type']);
        $versioning->prepare($this->versioning);
        $this->workingDirectory = $versioning->getWorkingDirectory();
        $versioning->process($this->workingDirectory);


        return $this;
    }

    /**
     * @param array $patterns
     *
     * @return $this
     */
    protected function loadProjectsFromPatterns($patterns)
    {
        foreach ($patterns as $pattern) {
            $this->loadProjectsFromPattern($pattern);
        }

        return $this;
    }

    /**
     * @param string $pattern
     *
     * @return $this
     */
    protected function loadProjectsFromPattern($pattern)
    {
        $dispatcher     = $this->cerbere->getDispatcher();
        $filenames      = glob($pattern);

        foreach ($filenames as $filename) {
            foreach ($this->cerbere->getParsers() as $parser) {
                if ($parser->supportedFile($filename)) {
                    $event = new CerbereFileDiscoverEvent($this->cerbere, $filename, $parser);
                    $dispatcher->dispatch(CerbereEvents::APPLICATION_FILE_DISCOVER, $event);
                    $parser->processFile($filename);
                    $this->projects = array_merge($this->projects, $parser->getProjects());
                }
            }
        }

        return $this;
    }

    /**
     * @param Cerbere $cerbere
     * @param array   $config
     *
     * @return Part
     */
    public static function generateFromConfig(Cerbere $cerbere, $config)
    {
        $config += array(
          'title'             => 'No name',
          'vcs'               => array(),
          'working_directory' => '',
          'patterns'          => array(),
        );

        $part = new self($cerbere);
        $part->setVersioning($config['vcs']);
        $part->setWorkingDirectory($config['working_directory']);
        $part->setPatterns($config['patterns']);
        $part->setTitle($config['title']);

        return $part;
    }
}
