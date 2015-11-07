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
     * @parser string $content
     * @return void
     */
    public function processContent($content);

    /**
     * @return string
     */
    public function getCode();

    /**
     * @return Project[]
     */
    public function getProjects();
}
