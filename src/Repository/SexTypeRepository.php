<?php

namespace App\Repository;

use App\Entity\SexType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SexType|null find($id, $lockMode = null, $lockVersion = null)
 * @method SexType|null findOneBy(array $criteria, array $orderBy = null)
 * @method SexType[]    findAll()
 * @method SexType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SexTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SexType::class);
    }

    // /**
    //  * @return SexType[] Returns an array of SexType objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SexType
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
