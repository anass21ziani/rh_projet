<?php

namespace App\Repository;

use App\Entity\Employe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<Employe>
 *
 * @implements PasswordUpgraderInterface<Employe>
 *
 * @method Employe|null find($id, $lockMode = null, $lockVersion = null)
 * @method Employe|null findOneBy(array $criteria, array $orderBy = null)
 * @method Employe[]    findAll()
 * @method Employe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmployeRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Employe::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Employe) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Trouve un employé par son email
     */
    public function findByEmail(string $email): ?Employe
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve les employés actifs
     */
    public function findActiveEmployees(): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('e.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les employés par rôle
     */
    public function findEmployeesByRole(string $role): array
    {
        // Utiliser une requête SQL native pour PostgreSQL
        $sql = 'SELECT e.* FROM employe e WHERE e.roles::text LIKE :role ORDER BY e.nom ASC';
        
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $result = $stmt->executeQuery(['role' => '%' . $role . '%']);
        
        $data = $result->fetchAllAssociative();
        $employees = [];
        
        foreach ($data as $row) {
            $employees[] = $this->find($row['id']);
        }
        
        return $employees;
    }

    /**
     * Trouve les employés par département
     */
    public function findByDepartment(string $department): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.department = :department')
            ->setParameter('department', $department)
            ->orderBy('e.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les employés par rôle (alias pour findEmployeesByRole)
     */
    public function findByRole(string $role): array
    {
        return $this->findEmployeesByRole($role);
    }

    /**
     * Trouve les employés actifs par rôle
     */
    public function findActiveByRole(string $role): array
    {
        // Utiliser une requête SQL native pour PostgreSQL
        $sql = 'SELECT e.* FROM employe e WHERE e.is_active = :active AND e.roles::text LIKE :role ORDER BY e.nom ASC';
        
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $result = $stmt->executeQuery([
            'active' => true,
            'role' => '%' . $role . '%'
        ]);
        
        $data = $result->fetchAllAssociative();
        $employees = [];
        
        foreach ($data as $row) {
            $employees[] = $this->find($row['id']);
        }
        
        return $employees;
    }
}
