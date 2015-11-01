<?php

namespace Cerbere\Parser;

use Cerbere\Model\Project;

/**
 * Class Make
 * @package Cerbere\Parser
 */
class Make extends Ini {
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
  public function __construct($filename) {
    $this->filename = $filename;
    $this->init();
  }

  /**
   *
   */
  protected function init() {
    $this->data = $this->parseFile($this->filename);

    $this->data += array(
      'core'      => '7.x',
      'api'       => '',
      'projects'  => array(),
      'libraries' => array(),
    );

    foreach ($this->data['projects'] as $project_name => $project_details) {
      $project = new Project($project_name, $this->data['core'], $this->data['core'] . '-' . $project_details['version']);
      $this->data['projects'][$project_name] = $project;
    }
  }

  /**
   * @return string
   */
  public function getCore() {
    return $this->data['core'];
  }

  /**
   * @return string
   */
  public function getApi() {
    return $this->data['api'];
  }

  /**
   * @return Project[]
   */
  public function getProjects() {
    return $this->data['projects'];
  }

  /**
   * @return array
   */
  public function getLibraries() {
    return $this->data['libraries'];
  }
}
