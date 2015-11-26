<?php

namespace Cerbere\Model\Hacked;

/**
 * Base class for the different ways that files can be hashed.
 *
 * Class HackedFileHasher
 * @package Cerbere\Model\Hacked
 */
abstract class HackedFileHasher {
  /**
   * Returns a hash of the given filename.
   *
   * Ignores file line endings
   */
  public function hash($filename) {
    if (file_exists($filename)) {
      if ($hash = $this->getCache($filename)) {
        return $hash;
      }
      else {
        echo '+';
        $hash = $this->performHash($filename);
        $this->setCache($filename, $hash);

        return $hash;
      }
    }
  }

  /**
   * @param string $filename
   * @param string $hash
   */
  public function setCache($filename, $hash) {
    //cache_set($this->getCacheKey($filename), $hash, HACKED_CACHE_TABLE, strtotime('+7 days'));
  }

  /**
   * @param string $filename
   * @return string|false
   */
  public function getCache($filename) {
    $cache = false;//cache_get($this->getCacheKey($filename), HACKED_CACHE_TABLE);

    if (!empty($cache->data)) {
      return $cache->data;
    }

    return false;
  }

  /**
   * @param string $filename
   * @return string
   */
  public function getCacheKey($filename) {
    $key = array(
      'filename' => $filename,
      'mtime' => filemtime($filename),
      'class_name' => get_class($this),
    );

    return sha1(serialize($key));
  }

  /**
   * Compute and return the hash of the given file.
   *
   * @param string $filename
   *   A fully-qualified filename to hash.
   *
   * @return string
   *   The computed hash of the given file.
   */
  abstract public function performHash($filename);

  /**
   * Compute and return the lines of the given file.
   *
   * @param string $filename
   *   A fully-qualified filename to return.
   *
   * @return array|false
   *   The lines of the given filename or FALSE on failure.
   */
  abstract public function fetchLines($filename);
}
