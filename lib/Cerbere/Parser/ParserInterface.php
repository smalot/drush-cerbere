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
     * @param string $filename
     *
     * @return bool
     */
    public function supportedFile($filename);

    /**
     * @parser string $filename
     * @return void
     */
    public function processFile($filename);

    /**
     * @parser string $content
     * @return void
     */
    public function processContent($content);

    /**
     * @return Project[]
     */
    public function getProjects();
}
