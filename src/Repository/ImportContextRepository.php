<?php

namespace App\Repository;

use App\Entity\ImportContext;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ImportContext|null find($id, $lockMode = null, $lockVersion = null)
 * @method ImportContext|null findOneBy(array $criteria, array $orderBy = null)
 * @method ImportContext[]    findAll()
 * @method ImportContext[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImportContextRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ImportContext::class);
    }

    // /**
    //  * @return ImportContext[] Returns an array of ImportContext objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ImportContext
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function findLikeName(string $name)
    {
        $queryBuilder = $this->createQueryBuilder('i')
            ->join('i.author','u')
            ->where('i.title LIKE :name')
            ->orWhere('u.lastname LIKE :name')
            ->orWhere('u.firstname LIKE :name')
            ->setParameter('name', '%' . $name . '%')
            ->orderBy('i.title', 'ASC')
            ->getQuery();

        return $queryBuilder->getResult();
    }
}
