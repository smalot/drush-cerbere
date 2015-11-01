<?php

namespace Cerbere\Parser;

use Cerbere\Model\Project;

/**
 * Class Info
 * @package Cerbere\Parser
 */
class Info extends Ini {
  /**
   * @var string
   */
  protected $filename;

  /**
   * @var array
   */
  protected $data;

  /**
   * @var Project
   */
  protected $project;

  /**
   * @param string $filename
   */
  public function __construct($filename) {
    $this->filename = $filename;
    $this->init();
  }

  /**
   * @return Project
   */
  public function getProject() {
    return $this->project;
  }

  /**
   *
   */
  protected function init() {
    $data = $this->parseFile($this->filename);

    $data += array(
      'project' => basename($this->filename, '.info'),
    );

    $project = new Project($data['project'], $data['core'], $data['version']);
    // Todo: add properties to project.

    $this->project = $project;
  }
}
