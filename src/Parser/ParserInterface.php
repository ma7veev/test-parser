<?php

namespace App\Parser;

interface ParserInterface
{
    public function setUrl($url);
    public function getContent();
    public function saveContent();
}