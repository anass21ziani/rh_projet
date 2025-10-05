<?php

namespace App\Repository;

use App\Entity\Document;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Document>
 */
class DocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Document::class);
    }

    public function save(Document $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Document $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByDossier(int $dossierId): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.dossier = :dossierId')
            ->setParameter('dossierId', $dossierId)
            ->orderBy('d.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByFileType(string $fileType): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.fileType = :fileType')
            ->setParameter('fileType', $fileType)
            ->orderBy('d.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByReference(string $reference): ?Document
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.reference = :reference')
            ->setParameter('reference', $reference)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findRecentDocuments(int $limit = 10): array
    {
        return $this->createQueryBuilder('d')
            ->orderBy('d.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function searchByFilename(string $searchTerm): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.filename LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->orderBy('d.filename', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
