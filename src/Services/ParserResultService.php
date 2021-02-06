<?php

namespace App\Services;

use App\Entity\ParsingResult;
use App\Entity\Products;
use Doctrine\ORM\EntityManagerInterface;

class ParserResultService
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function saveResult(int $new = 0, int $upd = 0, int $pointer = 0)
    {
        $result = new ParsingResult();
        $result->setDateParsed(new \DateTime());
        $result->setCountNew($new);
        $result->setCountUpd($upd);
        $result->setPointer($pointer);

        $this->em->persist($result);
        $this->em->flush();
    }

    public function getPointer()
    {
        $res = $this->em->getRepository(ParsingResult::class)->findLastPointer();

        return isset($res['pointer'])? $res[ 'pointer' ] :  0;
    }
}