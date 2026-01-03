<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(ProjectRepository $projectRepo, TaskRepository $taskRepo): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();

        $projects = [];
        $tasks = [];

        if ($user) {
            if ($this->isGranted('ROLE_ADMIN')) {
                // Les admins voient tous les projets
                $projects = $projectRepo->findAll();
                // Optionnel : récupérer toutes les tâches si tu veux afficher un tableau global
                $tasks = $taskRepo->findAll();
            } else {
                // Les membres voient uniquement les projets où ils sont assignés
                $projects = $user->getProjects(); // ManyToMany relation Project <-> User

                // Les tâches assignées à l'utilisateur connecté
                $tasks = $user->getTasks();
            }
        }

        return $this->render('home/index.html.twig', [
            'projects' => $projects,
            'tasks' => $tasks,
        ]);
    }
}
