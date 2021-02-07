<?php

namespace App\Parser;

interface ParserInterface
{
    public function setUrl($url);
    public function setLimit($limit);
    public function getContent();
    public function saveContent();
}