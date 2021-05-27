<?php

namespace App\Repository;

use App\Entity\ContextData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ContextData|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContextData|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContextData[]    findAll()
 * @method ContextData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContextDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContextData::class);
    }

    // /**
    //  * @return ContextData[] Returns an array of ContextData objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ContextData
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
