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

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column]
    private ?\DateTime $start = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $endtime = null;

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

    public function getStart(): ?\DateTime
    {
        return $this->start;
    }

    public function setStart(\DateTime $start): static
    {
        $this->start = $start;

        return $this;
    }

    public function getEndTime(): ?\DateTime
    {
        return $this->endtime;
    }

    public function setEndTime(?\DateTime $endtime): static
    {
        $this->endtime = $endtime;

        return $this;
    }
}
