<?php

namespace App\Parser;

use App\Entity\Products;
use App\Services\CsvReader;
use App\Services\ParserResultService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class CsvParser implements ParserInterface
{
    private $url;
    private $content;
    private $reader;
    private $em;
    private $pointer;
    private $new=0;
    private $upd=0;
    private $limit=20;
    private $result_service;

    public function __construct(EntityManagerInterface $em, ParserResultService $result_service)
    {
        $this->em = $em;
        $this->result_service = $result_service;
        $this->pointer = $this->result_service->getPointer();
    }

    /**
     * @param $url
     * @return void
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function setLimit($limit)
    {
        if (intval($limit)>0){
            $this->limit = intval($limit);
        }
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
        $start = 0;
        $this->pointer++;
        yield "Start parsing from position {$this->pointer}";
        $new_pointer = -1;
        if ($this->content) foreach ($this->content->rows() as $row) {
            /*we should start parsing from last pointer*/
            if ($start < $this->pointer) {
                $start ++;
                continue;
            }
            $start ++;
            $this->limit --;
            /*if we reach limit of records, stop parsing*/
            if ($this->limit==0){
                break;
            }
            /*If we got at least one row parsed, change pointer*/
            $new_pointer = $start;
            if (!empty($row) && is_array($row) && count($row) > 1 && intval($row[ 0 ]) !== 0 && $row[ 1 ] !== 'title') {
                yield $this->saveItem($row);
            }

        }
        $this->result_service->saveResult($this->new, $this->upd, $new_pointer);
        yield "Created new records {$this->new}, updated {$this->upd}";
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

    /**
     * @param $item
     * @return false|string
     */
    private function saveItem($item)
    {
        $product_id = intval($item[ 0 ]);
        $date_updated = null;
        /*get updating date*/
        if (isset($item[ 6 ]) && is_string($item[ 6 ]) && !empty($item[ 6 ])) {
            $date = trim($item[ 6 ]);
            if (isset($item[ 7 ]) && !empty($item[ 7 ]) && is_string($item[ 7 ])) {
                $date .= " " . trim($item[ 7 ]);
            }
            if ($date) {
                $date_updated = new \DateTime();

                $date_updated->setTimestamp(strtotime($date));
            }
        }
        /*Get product for updating or create new*/
        $product = $this->getEntity($product_id);
        /*Dont update existing product if its date_updated is not specified*/
        if (!empty($product->getProductId()) && empty($date_updated)) {
            return false;
        }
        if ($date_updated instanceof \DateTime) {
            /*check if parsed product has a newer version*/
            if ($product->getDateParsed() instanceof \DateTime && $date_updated->getTimestamp() <= $product->getDateParsed()
                    ->getTimestamp()) {
                return false;
            }

            $product->setDateUpdated($date_updated);
        }
        if (!empty($product->getProductId())) {
            $this->upd++;
        } else {
            $this->new++;
        }
        $product->setProductId($product_id);
        if (is_string($item[ 1 ])) {
            $title = strip_tags(trim(substr($item[ 1 ], 0, 500)));
            $title = (!empty($title)) ? $title : 'Unknown product';
            $product->setTitle($title);
        }
        if (is_string($item[ 2 ]) && !empty($item[ 2 ])) {
            $description = strip_tags(trim($item[ 2 ]));
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
        $product->setDateParsed(new \DateTime());
        $this->em->persist($product);
        $this->em->flush();

        return $title ?? false;
    }

    private function getEntity($product_id)
    {
        $entity = $this->em->getRepository(Products::class)->findOneByProductId($product_id);
        if (empty($entity)) {
            $entity = new Products();
        }

        return $entity;
    }
}