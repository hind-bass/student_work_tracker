<?php
// src/Service/StatisticsService.php

namespace App\Service;

use App\Entity\User;
use App\Repository\AssignmentRepository;
use App\Repository\CourseRepository;

class StatisticsService
{
    public function __construct(
        private AssignmentRepository $assignmentRepository,
        private CourseRepository $courseRepository
    ) {}

    /**
     * Récupère toutes les statistiques nécessaires pour le tableau de bord
     */
    public function getDashboardStats(User $user): array
    {
        // Récupération des données brutes depuis les repositories
        $statusCounts = $this->assignmentRepository->countByStatus($user);
        $courseCounts = $this->assignmentRepository->countByCourse($user);
        $upcoming = $this->assignmentRepository->findUpcomingByUser($user);
        $recent = $this->assignmentRepository->findRecentActivities($user);
        $overdueCount = $this->assignmentRepository->countOverdueByUser($user);
        $totalCourses = $this->courseRepository->countByUser($user);

        // Calcul des totaux et de la progression
        $totalAssignments = array_sum($statusCounts);
        $completed = $statusCounts['completed'] ?? 0;
        $progressPercentage = $totalAssignments > 0 ? round(($completed / $totalAssignments) * 100, 2) : 0;

        return [
            'todo' => $statusCounts['todo'] ?? 0,
            'in_progress' => $statusCounts['in_progress'] ?? 0,
            'completed' => $completed,
            'total' => $totalAssignments,
            'progress_percentage' => $progressPercentage,
            'upcoming_assignments' => $upcoming,
            'recent_activities' => $recent,
            'assignments_by_course' => $courseCounts,
            'overdue_count' => $overdueCount,
            'total_courses' => $totalCourses,
        ];
    }

    /**
     * Prépare les données formatées pour le graphique Chart.js (Répartition par matière)
     */
    public function getChartData(User $user): array
    {
        $courseCounts = $this->assignmentRepository->countByCourse($user);

        $labels = [];
        $data = [];

        foreach ($courseCounts as $item) {
            $labels[] = $item['courseName']; // Doit correspondre à l'alias dans AssignmentRepository
            $data[] = (int) $item['count'];
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }
}
