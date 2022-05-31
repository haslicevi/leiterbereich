<?php

namespace App\Repository;

use App\Entity\GlobaleVariabeln;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GlobaleVariabeln|null find($id, $lockMode = null, $lockVersion = null)
 * @method GlobaleVariabeln|null findOneBy(array $criteria, array $orderBy = null)
 * @method GlobaleVariabeln[]    findAll()
 * @method GlobaleVariabeln[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlobaleVariabelnRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GlobaleVariabeln::class);
    }

    // /**
    //  * @return GlobaleVariabeln[] Returns an array of GlobaleVariabeln objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?GlobaleVariabeln
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
