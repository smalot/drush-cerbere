<?php

namespace Cerbere\Model\Hacked;

/**
 * This is a much faster, but potentially less useful file hasher.
 */
class HackedFileIncludeEndingsHasher extends HackedFileHasher {
  function perform_hash($filename) {
    return sha1_file($filename);
  }

  function fetch_lines($filename) {
    return file($filename);
  }
}
