<?php

namespace Cerbere\Model\Hacked;

use splitbrain\PHPArchive\Archive;
use splitbrain\PHPArchive\Tar;
use Cerbere\Model\Release;

/**
 * Downloads a project using a standard Drupal method.
 */
class HackedProjectWebFilesDownloader extends HackedProjectWebDownloader {
  /**
   * @return string|false
   */
  public function getDownloadLink() {
    if (!empty($this->project->project_info['releases'][$this->project->existing_version])) {
      /** @var Release $this_release */
      $this_release = $this->project->project_info['releases'][$this->project->existing_version];

      return $this_release->getDownloadLink();
    }

    return false;
  }

  /**
   * @return bool|string
   */
  public function downloadFile() {
    $dir = $this->getDestination();

    if (!($release_url = $this->getDownloadLink())) {
      return FALSE;
    }

    // If our directory already exists, we can just return the path to this cached version
    if (file_exists($dir) && count(HackedFileGroup::scanDirectory($dir, '/.*/', array(
        '.',
        '..',
        'CVS',
        '.svn',
        '.git'
      )))
    ) {
      return $dir;
    }

    // Build the destination folder tree if it doesn't already exists.
    mkdir($dir, 0775, TRUE);

    if (!($local_file = $this->getFile($release_url))) {
      return FALSE;
    }

    try {
      $this->extractArchive($local_file, $dir);
    }
    catch (\Exception $e) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Copies a file from $url to the temporary directory for updates.
   *
   * If the file has already been downloaded, returns the the local path.
   *
   * @param $url
   *   The URL of the file on the server.
   *
   * @return string
   *   Path to local file.
   */
  protected function getFile($url) {
    $parsed_url = parse_url($url);
    $remote_schemes = array('http', 'https', 'ftp', 'ftps', 'smb', 'nfs');

    if (!in_array($parsed_url['scheme'], $remote_schemes)) {
      // This is a local file, just return the path.
      return realpath($url);
    }

    // Todo: use Symfony's cache objects
    // Check the cache and download the file if needed.
    $cache_directory = sys_get_temp_dir() . '/hacked-cache';
    $local = $cache_directory . '/' . basename($parsed_url['path']);

    if (!file_exists($cache_directory)) {
      mkdir($cache_directory, 0775, true);
    }

    // Todo: use guzzle.
    $content = file_get_contents($url);

    if ($content !== false && file_put_contents($local, $content)) {
      return $local;
    }

    return false;
  }

  /**
   * Unpack a downloaded archive file.
   *
   * @param string $file
   *   The filename of the archive you wish to extract.
   * @param string $directory
   *   The directory you wish to extract the archive into.
   * @return Archive
   *   The Archiver object used to extract the archive.
   * @throws \Exception on failure.
   */
  protected function extractArchive($file, $directory) {
    $archiver = new Tar();

    // Remove the directory if it exists, otherwise it might contain a mixture of
    // old files mixed with the new files (e.g. in cases where files were removed
    // from a later release).
    $archiver->open($file);
    $files = $archiver->contents();

    // First entry contains the root folder.
    $project_path = $files[0]->getPath();
    $extract_location = $directory . '/' . $project_path;

    if (file_exists($extract_location)) {
      $this->removeDir($extract_location);
    }

    // Reopen archive to extract all files.
    $archiver->open($file);
    $archiver->extract($directory);

    return $archiver;
  }
}
