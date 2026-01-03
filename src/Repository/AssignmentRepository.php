<?php

namespace App\Repository;

use App\Entity\Assignment;
use App\Entity\User;
use App\Enum\AssignmentStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Assignment>
 */
class AssignmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Assignment::class);
    }

    /**
     * Compte les devoirs par statut pour un utilisateur
     */
    public function countByStatus(User $user): array
    {
        $results = $this->createQueryBuilder('a')
            ->select('a.status, COUNT(a.id) as count')
            ->where('a.user = :user')
            ->setParameter('user', $user)
            ->groupBy('a.status')
            ->getQuery()
            ->getResult();

        $counts = [];
        foreach ($results as $result) {
            // Transformation de l'objet Enum en string pour les clés du tableau
            $statusKey = $result['status'] instanceof \BackedEnum ? $result['status']->value : $result['status'];
            $counts[$statusKey] = (int) $result['count'];
        }

        return $counts;
    }

    /**
     * Compte les devoirs par matière (Course)
     */
    public function countByCourse(User $user): array
    {
        return $this->createQueryBuilder('a')
            ->select('c.name as courseName, COUNT(a.id) as count')
            ->join('a.course', 'c')
            ->where('a.user = :user')
            ->setParameter('user', $user)
            ->groupBy('c.id')
            ->getQuery()
            ->getResult();
    }
    public function countOverdueByUser(User $user): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.user = :user')
            ->andWhere('a.status != :completed')
            ->andWhere('a.dueDate < :today')
            ->setParameter('user', $user)
            ->setParameter('completed', AssignmentStatus::COMPLETED)
            ->setParameter('today', new \DateTime())
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Trouve les prochains devoirs non terminés
     */
    public function findUpcomingByUser(User $user, int $limit = 5): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.user = :user')
            ->andWhere('a.status != :completed')
            ->andWhere('a.dueDate >= :today')
            ->setParameter('user', $user)        // Paramètre 1
            ->setParameter('completed', AssignmentStatus::COMPLETED) // Paramètre 2
            ->setParameter('today', new \DateTime()) // Paramètre 3
            ->orderBy('a.dueDate', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
    /**
     * Trouve un travail par son ID et s'assure qu'il appartient à l'utilisateur
     */
    public function findOneByIdAndUser(int $id, User $user): ?Assignment
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.id = :id')
            ->andWhere('a.user = :user')
            ->setParameter('id', $id)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve les activités récentes
     */
    public function findRecentActivities(User $user, int $limit = 10): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.user = :user')
            ->setParameter('user', $user) // <--- NE PAS OUBLIER
            ->orderBy('a.updatedAt', 'DESC')
            ->addOrderBy('a.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
