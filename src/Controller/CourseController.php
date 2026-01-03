<?php
// src/Controller/CourseController.php

namespace App\Controller;

use App\Entity\Course;
use App\Form\CourseType;
use App\Repository\CourseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Contrôleur pour la gestion des matières universitaires
 *
 * ✅ CORRECTIONS APPLIQUÉES :
 * 1. Route /course (sans 's') pour correspondre aux templates
 * 2. Ordre des routes corrigé (/new AVANT /{id})
 * 3. Vérifications de sécurité ajoutées
 * 4. Gestion manuelle des entités pour éviter "object not found"
 */
#[Route('/course')]  // ✅ CORRECTION 1 : Sans 'S' pour matcher les templates
#[IsGranted('ROLE_USER')]
class CourseController extends AbstractController
{
    /**
     * Liste toutes les matières de l'utilisateur connecté
     * Route : GET /course/
     */
    #[Route('/', name: 'app_course_index', methods: ['GET'])]
    public function index(CourseRepository $courseRepository): Response
    {
        $courses = $courseRepository->findBy(
            ['user' => $this->getUser()],
            ['name' => 'ASC']
        );

        return $this->render('course/index.html.twig', [
            'courses' => $courses,
        ]);
    }

    /**
     * ✅ CORRECTION 2 : Route /new AVANT /{id} pour éviter les conflits
     * Formulaire de création d'une nouvelle matière
     * Route : GET|POST /course/new
     */
    #[Route('/new', name: 'app_course_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $course = new Course();
        $course->setUser($this->getUser());

        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($course);
            $entityManager->flush();

            $this->addFlash('success', sprintf(
                'La matière "%s" a été créée avec succès !',
                $course->getName()
            ));

            return $this->redirectToRoute('app_course_show', ['id' => $course->getId()]);
        }

        return $this->render('course/new.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    /**
     * ✅ CORRECTION 3 : Gestion manuelle avec vérifications de sécurité
     * Affiche les détails d'une matière
     * Route : GET /course/{id}
     */
    #[Route('/{id}', name: 'app_course_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id, CourseRepository $courseRepository): Response
    {
        $course = $courseRepository->find($id);

        // Vérification 1 : La matière existe-t-elle ?
        if (!$course) {
            $this->addFlash('error', 'Cette matière n\'existe pas.');
            return $this->redirectToRoute('app_course_index');
        }

        // Vérification 2 : L'utilisateur est-il propriétaire ?
        if ($course->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'Vous n\'avez pas accès à cette matière.');
            return $this->redirectToRoute('app_course_index');
        }

        return $this->render('course/show.html.twig', [
            'course' => $course,
        ]);
    }

    /**
     * ✅ CORRECTION 3 : Gestion manuelle avec vérifications
     * Formulaire de modification d'une matière
     * Route : GET|POST /course/{id}/edit
     */
    #[Route('/{id}/edit', name: 'app_course_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id, CourseRepository $courseRepository, EntityManagerInterface $entityManager): Response
    {
        $course = $courseRepository->find($id);

        // Vérifications de sécurité
        if (!$course) {
            $this->addFlash('error', 'Cette matière n\'existe pas.');
            return $this->redirectToRoute('app_course_index');
        }

        if ($course->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'Vous n\'avez pas accès à cette matière.');
            return $this->redirectToRoute('app_course_index');
        }

        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', sprintf(
                'La matière "%s" a été modifiée avec succès !',
                $course->getName()
            ));

            return $this->redirectToRoute('app_course_show', ['id' => $course->getId()]);
        }

        return $this->render('course/edit.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    /**
     * ✅ CORRECTION 3 : Gestion manuelle avec vérifications
     * Suppression d'une matière
     * Route : POST /course/{id}
     */
    #[Route('/{id}', name: 'app_course_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, int $id, CourseRepository $courseRepository, EntityManagerInterface $entityManager): Response
    {
        $course = $courseRepository->find($id);

        // Vérifications de sécurité
        if (!$course) {
            $this->addFlash('error', 'Cette matière n\'existe pas.');
            return $this->redirectToRoute('app_course_index');
        }

        if ($course->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'Vous n\'avez pas accès à cette matière.');
            return $this->redirectToRoute('app_course_index');
        }

        // Vérification du token CSRF
        if ($this->isCsrfTokenValid('delete' . $course->getId(), $request->request->get('_token'))) {
            $courseName = $course->getName();
            $assignmentsCount = $course->getAssignmentsCount();

            $entityManager->remove($course);
            $entityManager->flush();

            $this->addFlash('success', sprintf(
                'La matière "%s" et ses %d travaux associés ont été supprimés avec succès.',
                $courseName,
                $assignmentsCount
            ));
        } else {
            $this->addFlash('error', 'Token CSRF invalide. La suppression a été annulée.');
        }

        return $this->redirectToRoute('app_course_index');
    }
}
