<?php

namespace App\Repository;

use App\Entity\Course;
use App\Entity\User; // <--- ASSUREZ-VOUS QUE CETTE LIGNE EST PRÉSENTE
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Course>
 */
class CourseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Course::class);
    }

    /**
     * Compte le nombre total de matières pour un utilisateur
     */
    public function countByUser(User $user): int // Type hinting vers App\Entity\User
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
