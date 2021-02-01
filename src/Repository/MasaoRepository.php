<?php

namespace App\Repository;

use App\Entity\Masao;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Masao|null find($id, $lockMode = null, $lockVersion = null)
 * @method Masao|null findOneBy(array $criteria, array $orderBy = null)
 * @method Masao[]    findAll()
 * @method Masao[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MasaoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Masao::class);
    }

    // /**
    //  * @return Masao[] Returns an array of Masao objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Masao
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
