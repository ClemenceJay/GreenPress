<?php

namespace App\Repository;

use App\Entity\ProductCommande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductCommande>
 */
class ProductCommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductCommande::class);
    }

    public function save(ProductCommande $ProductCommande, bool $flush = false): void
    {
        $this->getEntityManager()->persist($ProductCommande);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProductCommande $ProductCommande, bool $flush = false): void
    {
        $this->getEntityManager()->remove($ProductCommande);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //    /**
    //     * @return ProductCommande[] Returns an array of ProductCommande objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ProductCommande
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
