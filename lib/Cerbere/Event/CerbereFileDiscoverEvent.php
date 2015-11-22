<?php

namespace Cerbere\Event;

use Cerbere\Cerbere;
use Cerbere\Parser\ParserInterface;

/**
 * Class CerbereFileDiscoverEvent
 *
 * @package Cerbere\Event
 */
class CerbereFileDiscoverEvent extends CerbereEvent
{
    /**
     * @var Cerbere
     */
    protected $cerbere;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @var ParserInterface
     */
    protected $parser;

    /**
     * @param Cerbere $cerbere
     * @param string $filename
     * @param ParserInterface $parser
     */
    public function __construct(Cerbere $cerbere, $filename, ParserInterface $parser)
    {
        $this->cerbere = $cerbere;
        $this->filename = $filename;
        $this->parser = $parser;
    }

    /**
     * @return Cerbere
     */
    public function getCerbere()
    {
        return $this->cerbere;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @return ParserInterface
     */
    public function getParser()
    {
        return $this->parser;
    }
}
