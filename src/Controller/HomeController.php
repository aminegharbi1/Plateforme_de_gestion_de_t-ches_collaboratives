<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(ProjectRepository $projectRepo, TaskRepository $taskRepo): Response
    {
        // Récupérer tous les projets pour les admins
        $projects = [];
        if ($this->isGranted('ROLE_ADMIN')) {
            $projects = $projectRepo->findAll();
        }

        // Récupérer les tâches assignées à l'utilisateur connecté
        $tasks = $taskRepo->findBy(['assignedTo' => $this->getUser()]);

        return $this->render('home/index.html.twig', [
            'projects' => $projects,
            'tasks' => $tasks,
        ]);
    }
}
