<?php

namespace Cerbere\Archiver;

/**
 * @file
 * Archiver implementations provided by the system module.
 */

/**
 * Archiver for .zip files.
 *
 * @link http://php.net/zip
 */
class Zip implements ArchiverInterface {

    /**
     * The underlying ZipArchive instance that does the heavy lifting.
     *
     * @var \ZipArchive
     */
    protected $zip;

    public function __construct($file_path) {
        $this->zip = new \ZipArchive();
        if ($this->zip->open($file_path) !== TRUE) {
            // @todo: This should be an interface-specific exception some day.
            throw new \Exception(t('Cannot open %file_path', array('%file_path' => $file_path)));
        }
    }

    public function add($file_path) {
        $this->zip->addFile($file_path);

        return $this;
    }

    public function remove($file_path) {
        $this->zip->deleteName($file_path);

        return $this;
    }

    public function extract($path, Array $files = array()) {
        if ($files) {
            $this->zip->extractTo($path, $files);
        }
        else {
            $this->zip->extractTo($path);
        }

        return $this;
    }

    public function listContents() {
        $files = array();
        for ($i=0; $i < $this->zip->numFiles; $i++) {
            $files[] = $this->zip->getNameIndex($i);
        }
        return $files;
    }

    /**
     * Retrieve the zip engine itself.
     *
     * In some cases it may be necessary to directly access the underlying
     * ZipArchive object for implementation-specific logic. This is for advanced
     * use only as it is not shared by other implementations of ArchiveInterface.
     *
     * @return \ZipArchive
     *   The ZipArchive object used by this object.
     */
    public function getArchive() {
        return $this->zip;
    }
}
