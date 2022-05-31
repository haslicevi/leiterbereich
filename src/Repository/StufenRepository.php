<?php

namespace App\Repository;

use App\Entity\Stufen;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Stufen|null find($id, $lockMode = null, $lockVersion = null)
 * @method Stufen|null findOneBy(array $criteria, array $orderBy = null)
 * @method Stufen[]    findAll()
 * @method Stufen[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StufenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stufen::class);
    }

    // /**
    //  * @return Stufen[] Returns an array of Stufen objects
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
    public function findOneBySomeField($value): ?Stufen
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
