<?php

namespace Cerbere\Archiver;

/**
 * @file
 * Archiver implementations provided by the system module.
 */

/**
 * Archiver for .tar files.
 */
class Tar implements ArchiverInterface {

    /**
     * The underlying Archive_Tar instance that does the heavy lifting.
     *
     * @var Archive_Tar
     */
    protected $tar;

    public function __construct($file_path) {
        $this->tar = new ArchiveTar($file_path);
    }

    public function add($file_path) {
        $this->tar->add($file_path);

        return $this;
    }

    public function remove($file_path) {
        // @todo Archive_Tar doesn't have a remove operation
        // so we'll have to simulate it somehow, probably by
        // creating a new archive with everything but the removed
        // file.

        return $this;
    }

    public function extract($path, Array $files = array()) {
        if ($files) {
            $this->tar->extractList($files, $path);
        }
        else {
            $this->tar->extract($path);
        }

        return $this;
    }

    public function listContents() {
        $files = array();
        foreach ($this->tar->listContent() as $file_data) {
            $files[] = $file_data['filename'];
        }
        return $files;
    }

    /**
     * Retrieve the tar engine itself.
     *
     * In some cases it may be necessary to directly access the underlying
     * Archive_Tar object for implementation-specific logic. This is for advanced
     * use only as it is not shared by other implementations of ArchiveInterface.
     *
     * @return Archive_Tar
     *   The Archive_Tar object used by this object.
     */
    public function getArchive() {
        return $this->tar;
    }
}
