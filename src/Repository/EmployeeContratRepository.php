<?php

namespace App\Repository;

use App\Entity\EmployeeContrat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmployeeContrat>
 */
class EmployeeContratRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmployeeContrat::class);
    }

    public function save(EmployeeContrat $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(EmployeeContrat $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findActiveContrats(): array
    {
        return $this->createQueryBuilder('ec')
            ->andWhere('ec.statut = :statut')
            ->setParameter('statut', 'actif')
            ->orderBy('ec.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByEmployee(int $employeeId): array
    {
        return $this->createQueryBuilder('ec')
            ->andWhere('ec.employee = :employeeId')
            ->setParameter('employeeId', $employeeId)
            ->orderBy('ec.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findExpiringContrats(int $days = 30): array
    {
        $date = new \DateTime();
        $date->add(new \DateInterval('P' . $days . 'D'));

        return $this->createQueryBuilder('ec')
            ->andWhere('ec.statut = :statut')
            ->andWhere('ec.endDate <= :date')
            ->andWhere('ec.endDate IS NOT NULL')
            ->setParameter('statut', 'actif')
            ->setParameter('date', $date)
            ->orderBy('ec.endDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
