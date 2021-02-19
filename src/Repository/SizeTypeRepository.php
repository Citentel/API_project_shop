<?php

namespace App\Repository;

use App\Entity\SizeType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SizeType|null find($id, $lockMode = null, $lockVersion = null)
 * @method SizeType|null findOneBy(array $criteria, array $orderBy = null)
 * @method SizeType[]    findAll()
 * @method SizeType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SizeTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SizeType::class);
    }

    // /**
    //  * @return SizeType[] Returns an array of SizeType objects
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
    public function findOneBySomeField($value): ?SizeType
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
