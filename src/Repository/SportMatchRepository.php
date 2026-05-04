<?php

namespace App\Repository;

use App\Entity\SportMatch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SportMatch>
 *
 * @method SportMatch|null find($id, $lockMode = null, $lockVersion = null)
 * @method SportMatch|null findOneBy(array $criteria, array $orderBy = null)
 * @method SportMatch[]    findAll()
 * @method SportMatch[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SportMatchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SportMatch::class);
    }

//    /**
//     * @return SportMatch[] Returns an array of SportMatch objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?SportMatch
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
