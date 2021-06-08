<?php

namespace App\Repository;

use App\Entity\Leading;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Leading|null find($id, $lockMode = null, $lockVersion = null)
 * @method Leading|null findOneBy(array $criteria, array $orderBy = null)
 * @method Leading[]    findAll()
 * @method Leading[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LeadingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Leading::class);
    }

    // /**
    //  * @return Leading[] Returns an array of Leading objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Leading
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
