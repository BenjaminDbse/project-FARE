<?php

namespace App\Repository;

use App\Entity\Data;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Data|null find($id, $lockMode = null, $lockVersion = null)
 * @method Data|null findOneBy(array $criteria, array $orderBy = null)
 * @method Data[]    findAll()
 * @method Data[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Data::class);
    }
    public function findByLikeAdr(int $import, int $adr)
    {
        $queryBuilder = $this->createQueryBuilder('d')
            ->where('d.import ='. $import)
            ->andWhere('d.adr ='. $adr)
            ->orderBy('d.datetime', 'ASC')
            ->getQuery();


            return $queryBuilder->getResult();
    }
}
