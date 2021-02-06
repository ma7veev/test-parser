<?php

namespace App\Services;


class CsvReader
{
    protected $file;

    /**
     * CsvReader constructor.
     * @param $filePath
     */
    public function __construct($filePath)
    {
        $this->file = fopen($filePath, 'r');
    }

    /**
     * @return \Generator|void
     */
    public function rows()
    {
        while (!feof($this->file)) {
            $row = fgetcsv($this->file, 4096, ';');

            yield $row;
        }

        return;
    }
}

