<?php

namespace Hexlet\Code;

use DiDom\Document;

class Parser
{
    private string $html = '';

    public function __construct(string $html)
    {
        $this->html = $html;
    }

    public function getData()
    {
        $document = new Document($this->html);
        $h1 = optional($document->first('h1'))->text();
        $title = optional($document->first('title'))->text();
        $description = optional($document->first('meta[name=description]'))->getAttribute('content');

        return [$h1, $title, $description];
    }
}
