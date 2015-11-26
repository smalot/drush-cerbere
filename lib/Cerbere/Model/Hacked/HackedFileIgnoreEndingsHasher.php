<?php

namespace Cerbere\Model\Hacked;

class HackedFileIgnoreEndingsHasher extends HackedFileHasher
{
    /**
     * Ignores file line endings.
     * @inheritdoc
     */
    public function performHash($filename)
    {
        if (!HackedFileGroup::isBinary($filename)) {
            $file = file($filename, FILE_IGNORE_NEW_LINES);

            return sha1(serialize($file));
        } else {
            return sha1_file($filename);
        }
    }

    /**
     * @inheritdoc
     */
    public function fetchLines($filename)
    {
        return file($filename, FILE_IGNORE_NEW_LINES);
    }
}
