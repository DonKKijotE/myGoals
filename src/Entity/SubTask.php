<?php

namespace App\Entity;

use App\Repository\SubTaskRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubTaskRepository::class)]
class SubTask
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'subTasks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Task $maintask = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 600)]
    private ?string $description = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $start = null;

    #[ORM\Column(name: "endtime", type: "datetime_immutable", nullable: true)]
    private ?\DateTimeImmutable $endTime = null;

    #[ORM\Column]
    private ?bool $status = false;

    public function __construct()
    {
        $this->status = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMaintask(): ?Task
    {
        return $this->maintask;
    }

    public function setMaintask(?Task $maintask): static
    {
        $this->maintask = $maintask;

        return $this;
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

    public function getStart(): ?\DateTimeImmutable
    {
        return $this->start;
    }

    public function setStart(\DateTimeInterface $start): self
    {
        $this->start = $start instanceof \DateTimeImmutable ? $start : \DateTimeImmutable::createFromMutable($start);
        return $this;
    }

    public function getEndTime(): ?\DateTimeImmutable
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTimeInterface $end): self
    {
        $this->endTime = $end instanceof \DateTimeImmutable ? $end : \DateTimeImmutable::createFromMutable($end);
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
}
