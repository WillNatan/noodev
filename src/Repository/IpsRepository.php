<?php

namespace App\Repository;

use App\Entity\Ips;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Ips|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ips|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ips[]    findAll()
 * @method Ips[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IpsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ips::class);
    }

    // /**
    //  * @return Ips[] Returns an array of Ips objects
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
    public function findOneBySomeField($value): ?Ips
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
