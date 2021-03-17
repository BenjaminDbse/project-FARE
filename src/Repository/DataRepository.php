<?php

namespace App\Repository;

use App\Entity\Data;
use DateTime;
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
    public function dateToDate(int $import, int $adr, string $dateAt, string $toDatetime)
    {
        $queryBuilder = $this->createQueryBuilder('d')
            ->where('d.import ='. $import)
            ->andWhere('d.adr ='. $adr)
            ->andWhere('d.datetime between'. $dateAt . ' and '. $toDatetime)
            ->orderBy('d.datetime', 'ASC')
            ->getQuery();


        return $queryBuilder->getResult();
    }
    public function findByDateToLimit(int $import, int $adr, string $dateAt, int $limit)
    {
        $queryBuilder = $this->createQueryBuilder('d')
            ->where('d.import ='. $import)
            ->andWhere('d.adr ='. $adr)
            ->andWhere('d.datetime offset'. $dateAt. 'limit '. $limit)
            ->orderBy('d.datetime', 'ASC')
            ->getQuery();


        return $queryBuilder->getResult();
    }
}
