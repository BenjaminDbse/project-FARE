<?php

namespace App\Repository;

use App\Entity\Algo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Algo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Algo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Algo[]    findAll()
 * @method Algo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AlgoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Algo::class);
    }

    // /**
    //  * @return Algo[] Returns an array of Algo objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Algo
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
