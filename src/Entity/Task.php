<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 600)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $start = null;

    #[ORM\Column(name: "endtime", type: "datetime_immutable", nullable: true)]
    private ?\DateTimeImmutable $endTime = null;

    /**
     * @var Collection<int, SubTask>
     */
    #[ORM\OneToMany(targetEntity: SubTask::class, mappedBy: 'maintask', cascade: ['persist','remove'])]
    private Collection $subTasks;

    #[ORM\Column]
    private ?bool $status = false;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\Column(type: 'uuid', nullable: true)]
    private ?Uuid $recurrenceGroup = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $recurrenceType = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $recurrenceInterval = null;

    public function __construct()
    {
        $this->subTasks = new ArrayCollection();
        $this->status = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getStart(): ?\DateTimeImmutable
    {
        return $this->start;
    }

    public function setStart(\DateTimeInterface $start): self
    {
        $this->start = $start instanceof \DateTimeImmutable ? $start : \DateTimeImmutable::createFromMutable($start);
        return $this;
    }

    public function getEndtime(): ?\DateTimeImmutable
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTimeInterface $end): self
    {
        $this->endTime = $end instanceof \DateTimeImmutable ? $end : \DateTimeImmutable::createFromMutable($end);
        return $this;
    }

    /**
     * @return Collection<int, SubTask>
     */
    public function getSubTasks(): Collection
    {
        return $this->subTasks;
    }

    public function addSubTask(SubTask $subTask): static
    {
        if (!$this->subTasks->contains($subTask)) {
            $this->subTasks->add($subTask);
            $subTask->setMaintask($this);
        }

        return $this;
    }

    public function removeSubTask(SubTask $subTask): static
    {
        if ($this->subTasks->removeElement($subTask)) {
            // set the owning side to null (unless already changed)
            if ($subTask->getMaintask() === $this) {
                $subTask->setMaintask(null);
            }
        }

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(?bool $status): self
    {
        $this->status = $status ?? false;
        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getRecurrenceGroup(): ?Uuid
    {
        return $this->recurrenceGroup;
    }

    public function setRecurrenceGroup(?Uuid $recurrenceGroup): static
    {
        $this->recurrenceGroup = $recurrenceGroup;

        return $this;
    }

    public function getRecurrenceType(): ?string
    {
        return $this->recurrenceType;
    }

    public function setRecurrenceType(?string $recurrenceType): static
    {
        $this->recurrenceType = $recurrenceType;

        return $this;
    }

    public function getRecurrenceInterval(): ?string
    {
        return $this->recurrenceInterval;
    }

    public function setRecurrenceInterval(?string $recurrenceInterval): static
    {
        $this->recurrenceInterval = $recurrenceInterval;

        return $this;
    }
}
