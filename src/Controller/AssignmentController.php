<?php

// src/Controller/AssignmentController.php

namespace App\Controller;

use App\Entity\Assignment;
use App\Enum\AssignmentStatus;
use App\Form\AssignmentType;
use App\Repository\AssignmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Controller pour la gestion des travaux
 */
#[Route('/assignment')]
#[IsGranted('ROLE_USER')]
class AssignmentController extends AbstractController
{
    /**
     * Liste de tous les travaux de l'utilisateur
     */
    #[Route('/', name: 'app_assignment_index', methods: ['GET'])]
    public function index(AssignmentRepository $assignmentRepository): Response
    {
        $user = $this->getUser();
        $assignments = $assignmentRepository->findByUser($user);

        return $this->render('assignment/index.html.twig', [
            'assignments' => $assignments,
        ]);
    }

    /**
     * Créer un nouveau travail
     */
    #[Route('/new', name: 'app_assignment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Créer une nouvelle instance
        $assignment = new Assignment();
        $assignment->setUser($user);
        $assignment->setStatus(AssignmentStatus::TODO);

        // Créer le formulaire
        $form = $this->createForm(AssignmentType::class, $assignment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Sauvegarder en base
            $entityManager->persist($assignment);
            $entityManager->flush();

            $this->addFlash('success', 'Le travail a été créé avec succès !');
            return $this->redirectToRoute('app_assignment_index');
        }

        return $this->render('assignment/new.html.twig', [
            'assignment' => $assignment,
            'form' => $form,
        ]);
    }

    /**
     * Afficher les détails d'un travail
     */
    #[Route('/{id}', name: 'app_assignment_show', methods: ['GET'])]
    public function show(Assignment $assignment, AssignmentRepository $assignmentRepository): Response
    {
        $user = $this->getUser();

        // Vérifier que le travail appartient à l'utilisateur
        $assignment = $assignmentRepository->findOneByIdAndUser($assignment->getId(), $user);

        if (!$assignment) {
            throw $this->createNotFoundException('Ce travail n\'existe pas ou ne vous appartient pas.');
        }

        return $this->render('assignment/show.html.twig', [
            'assignment' => $assignment,
        ]);
    }

    /**
     * Modifier un travail
     */
    #[Route('/{id}/edit', name: 'app_assignment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Assignment $assignment, EntityManagerInterface $entityManager, AssignmentRepository $assignmentRepository): Response
    {
        $user = $this->getUser();

        // Vérifier que le travail appartient à l'utilisateur
        $assignment = $assignmentRepository->findOneByIdAndUser($assignment->getId(), $user);

        if (!$assignment) {
            throw $this->createNotFoundException('Ce travail n\'existe pas ou ne vous appartient pas.');
        }

        $form = $this->createForm(AssignmentType::class, $assignment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Le travail a été modifié avec succès !');
            return $this->redirectToRoute('app_assignment_index');
        }

        return $this->render('assignment/edit.html.twig', [
            'assignment' => $assignment,
            'form' => $form,
        ]);
    }

    /**
     * Supprimer un travail
     */
    #[Route('/{id}', name: 'app_assignment_delete', methods: ['POST'])]
    public function delete(Request $request, Assignment $assignment, EntityManagerInterface $entityManager, AssignmentRepository $assignmentRepository): Response
    {
        $user = $this->getUser();

        // Vérifier que le travail appartient à l'utilisateur
        $assignment = $assignmentRepository->findOneByIdAndUser($assignment->getId(), $user);

        if (!$assignment) {
            throw $this->createNotFoundException('Ce travail n\'existe pas ou ne vous appartient pas.');
        }

        // Vérifier le token CSRF
        if ($this->isCsrfTokenValid('delete' . $assignment->getId(), $request->request->get('_token'))) {
            $entityManager->remove($assignment);
            $entityManager->flush();

            $this->addFlash('success', 'Le travail a été supprimé avec succès !');
        }

        return $this->redirectToRoute('app_assignment_index');
    }

    /**
     * Changer le statut d'un travail via AJAX
     * Pour les actions rapides depuis le dashboard
     */
    #[Route('/{id}/status', name: 'app_assignment_change_status', methods: ['POST'])]
    public function changeStatus(Request $request, Assignment $assignment, EntityManagerInterface $entityManager, AssignmentRepository $assignmentRepository): JsonResponse
    {
        $user = $this->getUser();

        // Vérifier que le travail appartient à l'utilisateur
        $assignment = $assignmentRepository->findOneByIdAndUser($assignment->getId(), $user);

        if (!$assignment) {
            return new JsonResponse(['error' => 'Travail non trouvé'], 404);
        }

        // Récupérer le nouveau statut depuis la requête
        $data = json_decode($request->getContent(), true);
        $newStatus = $data['status'] ?? null;

        if (!$newStatus) {
            return new JsonResponse(['error' => 'Statut invalide'], 400);
        }

        try {
            // Convertir la string en enum
            $statusEnum = AssignmentStatus::from($newStatus);
            $assignment->setStatus($statusEnum);
            $entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Statut mis à jour',
                'status' => $statusEnum->getLabel(),
                'badge_class' => $statusEnum->getBadgeClass()
            ]);
        } catch (\ValueError $e) {
            return new JsonResponse(['error' => 'Statut invalide'], 400);
        }
    }
}
