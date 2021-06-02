<?php

namespace App\Repository;

use App\Entity\Import;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Import|null find($id, $lockMode = null, $lockVersion = null)
 * @method Import|null findOneBy(array $criteria, array $orderBy = null)
 * @method Import[]    findAll()
 * @method Import[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Import::class);
    }

    public function findLikeName(string $name)
    {
        $queryBuilder = $this->createQueryBuilder('i')
            ->join('i.author','u')
            ->join('i.category','c')
            ->where('i.title LIKE :name')
            ->orWhere('u.lastname LIKE :name')
            ->orWhere('u.firstname LIKE :name')
            ->orWhere('c.name LIKE :name')
            ->setParameter('name', '%' . $name . '%')
            ->orderBy('i.title', 'ASC')
            ->getQuery();

        return $queryBuilder->getResult();
    }
}
