<?php

namespace Cerbere\Parser;

use Cerbere\Model\Project;

/**
 * Class Info
 *
 * @package Cerbere\Parser
 */
class Info extends Ini
{
    /**
     * @var string
     */
    protected $filename;

    /**
     * @var Project
     */
    protected $project;

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
        return 'info';
    }

    /**
     * @return Project[]
     */
    public function getProjects()
    {
        return array($this->getProject());
    }

    /**
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param string $content
     */
    public function processContent($content)
    {
        $data = $this->parseContent($content);
        $data += array('project' => basename($this->filename, '.info'));

        $project = new Project($data['project'], $data['core'], $data['version']);
        $project->setDetails($data);

        $this->project = $project;
    }

    /**
     * @param string $filename
     */
    public function processFile($filename)
    {
        // Store filename to extract project name.
        $this->filename = $filename;

        parent::processFile($filename);
    }

    /**
     * @parser string $filename
     * @return integer
     */
    public function supportedFile($filename)
    {
        return preg_match('/\.info$/', $filename) > 0;
    }
}
