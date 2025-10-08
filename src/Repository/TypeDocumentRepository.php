<?php

namespace App\Repository;

use App\Entity\TypeDocument;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TypeDocument>
 */
class TypeDocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeDocument::class);
    }

    public function save(TypeDocument $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TypeDocument $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return TypeDocument[] Returns an array of TypeDocument objects
     */
    public function findObligatoires(): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.obligatoire = :val')
            ->setParameter('val', true)
            ->orderBy('t.nom', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return TypeDocument[] Returns an array of TypeDocument objects
     */
    public function findByNatureContrat($natureContratId): array
    {
        return $this->createQueryBuilder('t')
            ->join('t.natureContrats', 'nc')
            ->andWhere('nc.id = :natureContratId')
            ->setParameter('natureContratId', $natureContratId)
            ->orderBy('t.nom', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
