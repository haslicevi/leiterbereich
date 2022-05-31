<?php

namespace App\Repository;

use App\Entity\MailserverStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MailserverStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method MailserverStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method MailserverStatus[]    findAll()
 * @method MailserverStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MailserverStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MailserverStatus::class);
    }

    // /**
    //  * @return MailserverStatus[] Returns an array of MailserverStatus objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MailserverStatus
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
