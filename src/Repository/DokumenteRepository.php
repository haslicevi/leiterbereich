<?php

namespace App\Repository;

use App\Entity\Dokumente;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Dokumente|null find($id, $lockMode = null, $lockVersion = null)
 * @method Dokumente|null findOneBy(array $criteria, array $orderBy = null)
 * @method Dokumente[]    findAll()
 * @method Dokumente[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DokumenteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Dokumente::class);
    }

    // /**
    //  * @return Dokumente[] Returns an array of Dokumente objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Dokumente
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
