<?php

namespace Cerbere\Parser;

use Cerbere\Model\Project;

/**
 * Interface ParserInterface
 *
 * @package Cerbere\Parser
 */
interface ParserInterface
{
    /**
     * @return string
     */
    public function getCode();

    /**
     * @return Project[]
     */
    public function getProjects();

    /**
     * @parser string $content
     * @return void
     */
    public function processContent($content);

    /**
     * @parser string $filename
     * @return void
     */
    public function processFile($filename);

    /**
     * @param string $filename
     *
     * @return bool
     */
    public function supportedFile($filename);
}
