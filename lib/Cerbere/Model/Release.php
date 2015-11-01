<?php

namespace Cerbere\Model;

/**
 * Class Release
 * @package Cerbere\Model
 */
class Release {
  /**
   * @var array
   */
  protected $data;

  /**
   * @param array $data
   */
  public function __construct($data) {
    $this->data = $data;
  }

  /**
   * @return string
   */
  public function getName() {
    return $this->data['name'];
  }

  /**
   * @return string
   */
  public function getVersion() {
    return $this->data['version'];
  }

  /**
   * @return string
   */
  public function getTag() {
    return $this->data['tag'];
  }

  /**
   * @return string
   */
  public function getVersionMajor() {
    return $this->data['version_major'];
  }

  /**
   * @return string
   */
  public function getVersionPatch() {
    return isset($this->data['version_patch']) ? $this->data['version_patch'] : '';
  }

  /**
   * @return string
   */
  public function getVersionExtra() {
    return isset($this->data['version_extra']) ? $this->data['version_extra'] : '';
  }

  /**
   * @return string
   */
  public function getStatus() {
    return $this->data['status'];
  }

  /**
   * @param int $status
   */
  public function setStatus($status) {
    $this->data['status'] = $status;
  }

  /**
   * @return string
   */
  public function getProjectStatus() {
    return $this->data['project_status'];
  }

  /**
   * @param int $project_status
   */
  public function setProjectStatus($project_status) {
    $this->data['project_status'] = $project_status;
  }

  /**
   * @return string
   */
  public function getReleaseLink() {
    return $this->data['release_link'];
  }

  /**
   * @return string
   */
  public function getDownloadLink() {
    return $this->data['download_link'];
  }

  /**
   * @return \DateTime
   */
  public function getDate() {
    return new \DateTime('@' . $this->data['date']);
  }

  /**
   * @return string
   */
  public function getMDHash() {
    return $this->data['mdhash'];
  }

  /**
   * @return int
   */
  public function getFilesize() {
    return intval($this->data['filesize']);
  }

  /**
   * @return array
   */
  public function getFiles() {
    return trim($this->data['files']);
  }

  /**
   * @return array
   */
  public function getTerms() {
    return $this->data['terms'];
  }

  /**
   * @param $term
   * @return bool
   */
  public function hasTerm($term) {
    return isset($this->data['terms'][$term]);
  }

  /**
   * @param $term
   * @return mixed
   */
  public function getTerm($term) {
    return $this->data['terms'][$term];
  }
}
