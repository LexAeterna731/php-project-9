<?php

namespace Hexlet\Code\Tests;

use PHPUnit\Framework\TestCase;
use Hexlet\Code\Parser;

class ParserTest extends PHPUnit\Framework\TestCase
{
    private array $noDescData;
    private array $descData;
    private string $fileNoDesc;
    private string $fileDesc;

    protected function setUp(): void
    {
        $this->noDescData = [
            'Tag',
            'Title',
            null
        ];
        $this->descData = [
            'Tag',
            'Title',
            'Description'
        ];
        $this->fileNoDesc = 'nodescription.html';
        $this->fileDesc = 'description.html';
    }

    private function getFixturePath(string $fixture): string
    {
        $parts = [__DIR__, 'fixtures', $fixture];
        return implode('/', $parts);
    }

    public function testParser(): void
    {
        $withDescription = file_get_contents($this->getFixturePath($this->fileDesc));
        $parserD = new Parser((string) $withDescription);
        $this->assertEquals($this->descData, $parserD->getData());

        $noDescription = file_get_contents($this->getFixturePath($this->fileNoDesc));
        $parserN = new Parser((string) $noDescription);
        $this->assertEquals($this->noDescData, $parserN->getData());
    }
}
