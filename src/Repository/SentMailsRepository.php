<?php

namespace App\Repository;

use App\Entity\SentMails;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SentMails|null find($id, $lockMode = null, $lockVersion = null)
 * @method SentMails|null findOneBy(array $criteria, array $orderBy = null)
 * @method SentMails[]    findAll()
 * @method SentMails[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SentMailsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SentMails::class);
    }

    // /**
    //  * @return SentMails[] Returns an array of SentMails objects
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
    public function findOneBySomeField($value): ?SentMails
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
