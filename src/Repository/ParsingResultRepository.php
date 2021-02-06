<?php

namespace App\Repository;

use App\Entity\ParsingResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ParsingResult|null find($id, $lockMode = null, $lockVersion = null)
 * @method ParsingResult|null findOneBy(array $criteria, array $orderBy = null)
 * @method ParsingResult[]    findAll()
 * @method ParsingResult[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParsingResultRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ParsingResult::class);
    }

    // /**
    //  * @return ParsingResult[] Returns an array of ParsingResult objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */


    public function findLastPointer(): ?Array
    {
        return $this->createQueryBuilder('p')
            ->select('p.pointer')
            ->orderBy('p.date_parsed', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult()
        ;
    }

}
