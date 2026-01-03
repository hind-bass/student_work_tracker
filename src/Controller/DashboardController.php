<?php
// src/Controller/DashboardController.php

namespace App\Controller;

use App\Service\StatisticsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    #[Route('/dashboard', name: 'app_dashboard_alt')]
    public function index(StatisticsService $statisticsService): Response
    {
        $user = $this->getUser();

        // Récupérer les statistiques
        $stats = $statisticsService->getDashboardStats($user);
        $chartData = $statisticsService->getChartData($user);

        return $this->render('dashboard/index.html.twig', [
            'stats' => $stats,
            'chart_data' => $chartData,
        ]);
    }
}
