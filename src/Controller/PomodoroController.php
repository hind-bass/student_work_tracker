<?php
// src/Controller/PomodoroController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Contrôleur pour la technique Pomodoro
 * Permet aux étudiants de gérer leur temps de travail
 */
#[Route('/pomodoro')]
#[IsGranted('ROLE_USER')]
class PomodoroController extends AbstractController
{
    /**
     * Page principale du Pomodoro Timer
     */
    #[Route('/', name: 'app_pomodoro', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('pomodoro/index.html.twig', [
            'user' => $this->getUser(),
        ]);
    }
}
