<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use App\Entity\User;
use App\Entity\Task;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private User $owner;

    #[ORM\OneToMany(mappedBy: 'project', targetEntity: Task::class, cascade: ['persist', 'remove'])]
    private Collection $tasks;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'projects')]
    #[ORM\JoinTable(name: 'project_members')]
    private Collection $assignedTo;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
        $this->assignedTo = new ArrayCollection();
    }

    // ================== GETTERS & SETTERS ==================

    public function getId(): ?int { return $this->id; }

    public function getName(): ?string { return $this->name; }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string { return $this->description; }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface { return $this->startDate; }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface { return $this->endDate; }

    public function setEndDate(?\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;
        return $this;
    }

    public function getOwner(): User { return $this->owner; }

    public function setOwner(User $owner): static
    {
        $this->owner = $owner;
        $this->addAssignedTo($owner); // Propriétaire est toujours membre
        return $this;
    }

    /**
     * @return Collection<int, Task>
     */
    public function getTasks(): Collection { return $this->tasks; }

    public function addTask(Task $task): static
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks->add($task);
            $task->setProject($this);
        }
        return $this;
    }

    public function removeTask(Task $task): static
    {
        if ($this->tasks->removeElement($task)) {
            if ($task->getProject() === $this) {
                $task->setProject(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getAssignedTo(): Collection { return $this->assignedTo; }

    public function addAssignedTo(User $user): static
    {
        if (!$this->assignedTo->contains($user)) {
            $this->assignedTo->add($user);
        }
        return $this;
    }

    public function removeAssignedTo(User $user): static
    {
        $this->assignedTo->removeElement($user);
        return $this;
    }

    // ================== DASHBOARD HELPERS ==================

    public function getProgress(): int
    {
        $total = $this->tasks->count();
        if ($total === 0) return 0;

        $completed = 0;
        foreach ($this->tasks as $task) {
            if ($task->isCompleted()) $completed++;
        }

        return (int) round(($completed / $total) * 100);
    }

    public function getStatus(): string
    {
        if ($this->endDate && $this->endDate < new \DateTime()) return 'Completed';
        if ($this->getProgress() < 40) return 'At Risk';
        return 'On Track';
    }
}
