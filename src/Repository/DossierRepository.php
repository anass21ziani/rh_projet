<?php

namespace App\Repository;

use App\Entity\Dossier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Dossier>
 */
class DossierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Dossier::class);
    }

    public function save(Dossier $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Dossier $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByEmployee(int $employeeId): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.employe = :employeeId')
            ->setParameter('employeeId', $employeeId)
            ->orderBy('d.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.status = :status')
            ->setParameter('status', $status)
            ->orderBy('d.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findRecentDossiers(int $limit = 10): array
    {
        return $this->createQueryBuilder('d')
            ->orderBy('d.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findRecentDossiersWithDocuments(int $limit = 10): array
    {
        return $this->createQueryBuilder('d')
            ->leftJoin('d.documents', 'doc')
            ->addSelect('doc')
            ->orderBy('d.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function searchByNom(string $searchTerm): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.nom LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->orderBy('d.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
