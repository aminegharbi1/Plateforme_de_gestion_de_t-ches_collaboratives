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
    // Get all users assigned to tasks
    $assignedUsers = [];

    foreach ($project->getTasks() as $task) {
        if ($task->getAssignedTo() && !in_array($task->getAssignedTo(), $assignedUsers, true)) {
            $assignedUsers[] = $task->getAssignedTo();
        }
    }

    return $this->render('project/show.html.twig', [
        'project' => $project,
        'assignedUsers' => $assignedUsers,
    ]);
}


    #[Route('/{id}/edit', name: 'project_edit', methods: ['GET','POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Project $project, EntityManagerInterface $em): Response
    {
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
            'is_edit' => true,
        ]);
    }

    #[Route('/{id}', name: 'project_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Project $project, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$project->getId(), $request->request->get('_token'))) {
            $em->remove($project);
            $em->flush();
        }

        return $this->redirectToRoute('project_index');
    }
}
