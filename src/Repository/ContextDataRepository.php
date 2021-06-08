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
}
