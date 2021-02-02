<?php

namespace App\Repository;

use App\Entity\UsersHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UsersHelper|null find($id, $lockMode = null, $lockVersion = null)
 * @method UsersHelper|null findOneBy(array $criteria, array $orderBy = null)
 * @method UsersHelper[]    findAll()
 * @method UsersHelper[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsersHelperRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UsersHelper::class);
    }

    // /**
    //  * @return UsersHelper[] Returns an array of UsersHelper objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UsersHelper
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
