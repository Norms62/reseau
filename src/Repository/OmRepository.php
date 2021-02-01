<?php

namespace App\Repository;

use App\Entity\Om;
use App\Entity\Masao;
use App\Entity\TicketsRegroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;

/**
 * @method Om|null find($id, $lockMode = null, $lockVersion = null)
 * @method Om|null findOneBy(array $criteria, array $orderBy = null)
 * @method Om[]    findAll()
 * @method Om[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OmRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Om::class);
    }

    public function RefCommun(){
        // Recherche des references communes a=om , p =masao 
        $query = $this  ->createQueryBuilder('a');
        
        $query          ->select('a.ref , p.id as idmasao , a.id as idom')
                        ->join(Masao::class,'p')
                        ->where('a.ref = p.ref');

        return $query   ->getQuery()
                        ->getResult() ;

    }

    // /**
    //  * @return Om[] Returns an array of Om objects
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
    public function findOneBySomeField($value): ?Om
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
