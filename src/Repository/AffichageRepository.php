<?php

namespace App\Repository;

use App\Entity\Affichage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Affichage|null find($id, $lockMode = null, $lockVersion = null)
 * @method Affichage|null findOneBy(array $criteria, array $orderBy = null)
 * @method Affichage[]    findAll()
 * @method Affichage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AffichageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Affichage::class);
    }

    // /**
    //  * @return Affichage[] Returns an array of Affichage objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Affichage
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
