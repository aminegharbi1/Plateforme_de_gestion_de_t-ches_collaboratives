<?php

namespace App\Controller;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\ProjectType;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/project')]
#[IsGranted('ROLE_USER')]
final class ProjectController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function dashboard(ProjectRepository $projectRepository, TaskRepository $taskRepository): Response
    {
        $projects = $projectRepository->findAll();
        $totalTasks = count($taskRepository->findAll());
        $completedTasks = count($taskRepository->findBy(['status' => 'done']));
        
        return $this->render('project/dashboard.html.twig', [
            'projects' => $projects,
            'totalTasks' => $totalTasks,
            'completedTasks' => $completedTasks,
        ]);
    }

    #[Route('', name: 'project_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(ProjectRepository $projectRepository): Response
    {
        $projects = $projectRepository->findAll();

        return $this->render('project/index.html.twig', [
            'projects' => $projects,
        ]);
    }

    #[Route('/new', name: 'project_new', methods: ['GET','POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($project);
            $em->flush();

            return $this->redirectToRoute('project_index');
        }

        return $this->render('project/new.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'project_show', methods: ['GET'])]
    public function show(Project $project): Response
    {
        return $this->render('project/show.html.twig', [
            'project' => $project,
        ]);
    }
}
