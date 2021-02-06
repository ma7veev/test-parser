<?php

namespace App\Parser;

use App\Entity\Products;
use App\Services\CsvReader;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class CsvParser implements ParserInterface
{
    private $url;
    private $content;
    private $reader;
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

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
            if (!empty($row) && is_array($row) && isset($row[ 1 ]) && $row[ 1 ] !== 'title') {
                yield $this->saveItem($row);
            }
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

    private function saveItem($item)
    {
        $product = new Products();
        if (is_string($item[ 1 ])) {
            $title = trim(substr($item[ 1 ], 0, 500));
            $title = (!empty($title)) ? $title : 'Unknown product';
            $product->setTitle($title);
        } else {
            return false;
        }
        if (is_string($item[ 2 ]) && !empty($item[ 2 ])) {
            $description = trim($item[ 2 ]);
            $product->setDescription($description);
        }
        if (is_string($item[ 3 ]) || is_numeric($item[ 3 ])) {
            $price = floatval(preg_replace("/[^0-9\.]/", "", $item[ 3 ]));
            $product->setPrice($price);
        }
        if (is_string($item[ 4 ])) {
            $status = trim(mb_strtolower($item[ 4 ]));
            $product->setStatus($status);
        }
        if (is_string($item[ 5 ])) {
            $url = trim($item[ 5 ]);
            $product->setUrl($url);
        }
        if (isset($item[ 6 ]) && is_string($item[ 6 ]) && !empty($item[ 6 ])) {
            $date_updated = trim($item[ 6 ]);
            if (isset($item[ 7 ]) && !empty($item[ 7 ]) && is_string($item[ 7 ])) {
                $date_updated .= " " . trim($item[ 7 ]);
            }
            if ($date_updated) {
                $date = new \DateTime();

                $date->setTimestamp(strtotime($date_updated));
                $product->setDateUpdated($date);
            }
        }
        $product->setDateParsed(new \DateTime());
        $this->em->persist($product);
        $this->em->flush();

        return $title;
    }

    private function cleanItem()
    {
    }
}