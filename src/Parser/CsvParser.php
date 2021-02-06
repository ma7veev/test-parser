<?php

namespace App\Parser;

use App\Services\CsvReader;

class CsvParser implements ParserInterface
{
    private $url;
    private $content;
    private $reader;

    /**
     * @param $url
     * @return void
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return CsvReader
     */
    public function getContent()
    {
        $this->content = $this->getReader();

        return $this->content;
    }

    /**
     * @return bool|\Generator
     */
    public function saveContent()
    {
        if ($this->content) foreach ($this->content->rows() as $row) {
           yield 'message';
        }
        return true;
    }

    /**
     * @return CsvReader
     */
    private function getReader()
    {
        if (!$this->reader) {
            $this->reader = new CsvReader($this->url);
        }

        return $this->reader;
    }
}