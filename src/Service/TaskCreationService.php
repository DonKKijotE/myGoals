<?php

namespace App\Service;

use App\Entity\Task;
use App\Entity\Subtask;
use App\Entity\Category;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;
use App\Service\DateTimeService; // <-- añadir

class TaskCreationService
{
    public function __construct(
        private EntityManagerInterface $em,
        private DateTimeService $dateTimeService
    ) {
    }

    public function createFromArray(array $data, User $owner, ?string $userTimezone = null): Task
    {
        $task = new Task();
        $task->setName($data['title']);
        $task->setDescription($data['description'] ?? null);


        $task->setStart($this->dateTimeService->toUtc(new \DateTimeImmutable($data['start']), $userTimezone));
        $task->setEndTime($this->dateTimeService->toUtc(new \DateTimeImmutable($data['endTime']), $userTimezone));
        $task->setStatus($data['status'] ?? false);
        $task->setOwner($owner);

        // Categoría
        $categoryId = (int) ($data['category'] ?? 0);
        $category = $this->em->getRepository(Category::class)->find($categoryId);
        if (!$category) {
            throw new \Exception('Categoría no válida, id=' . $categoryId);
        }
        $task->setCategory($category);

        // Subtasks
        if (!empty($data['subtasks'])) {
            foreach ($data['subtasks'] as $stData) {
                $subtask = new Subtask();
                $subtask->setName($stData['name']);
                $subtask->setDescription($stData['description'] ?? null);
                $subtask->setStart($this->dateTimeService->toUtc(new \DateTimeImmutable($stData['start']), $userTimezone));
                $subtask->setEndTime($this->dateTimeService->toUtc(new \DateTimeImmutable($stData['endTime']), $userTimezone));
                $subtask->setStatus($stData['status'] ?? false);

                $subtask->setMaintask($task);
                $task->addSubtask($subtask);
            }
        }

        $this->em->persist($task);

        // Recurrencias
        $recurrenceData = $data['recurrence'] ?? null;
        if ($recurrenceData) {
            $groupId = Uuid::v4();
            $task->setRecurrenceGroup($groupId);

            $clones = $this->generateRecurrences($task, $recurrenceData, $owner, $userTimezone);
            foreach ($clones as $clone) {
                $this->em->persist($clone);
            }
        }

        $this->em->flush();
        return $task;
    }

    private function generateRecurrences(Task $original, array $recurrenceData, User $owner, ?string $userTimezone = null): array
    {
        $clones = [];
        $maxCount = 30;
        $count = min((int) $recurrenceData['count'], $maxCount);
        $type = $recurrenceData['type'];
        $interval = max((int) $recurrenceData['interval'], 1);

        $baseStart = $original->getStart();
        $baseEnd   = $original->getEndTime();

        for ($i = 1; $i < $count; $i++) {
            $newStart = clone $baseStart;
            $newEnd   = clone $baseEnd;

            switch ($type) {
                case 'daily':
                    $newStart = $newStart->modify("+{$interval} day");
                    $newEnd   = $newEnd->modify("+{$interval} day");
                    break;
                case 'weekly':
                    $newStart = $newStart->modify("+{$interval} week");
                    $newEnd   = $newEnd->modify("+{$interval} week");
                    break;
                case 'monthly':
                    $newStart = $newStart->modify("+{$interval} month");
                    $newEnd   = $newEnd->modify("+{$interval} month");
                    break;
            }

            $clone = new Task();
            $clone->setName($original->getName());
            $clone->setDescription($original->getDescription());
            $clone->setStart($this->dateTimeService->toUtc($newStart, $userTimezone));
            $clone->setEndTime($this->dateTimeService->toUtc($newEnd, $userTimezone));
            $clone->setStatus($original->isStatus());
            $clone->setRecurrenceGroup($original->getRecurrenceGroup());
            $clone->setOwner($owner);
            $clone->setCategory($original->getCategory());

            // Clonar subtareas
            foreach ($original->getSubtasks() as $subtask) {
                $newSubtask = new Subtask();
                $newSubtask->setName($subtask->getName());
                $newSubtask->setDescription($subtask->getDescription());
                $subtaskDuration = $subtask->getEndTime()->getTimestamp() - $subtask->getStart()->getTimestamp();

                $newSubtask->setStart($this->dateTimeService->toUtc(clone $newStart, $userTimezone));
                $newSubtask->setEndTime($this->dateTimeService->toUtc((clone $newStart)->modify("+{$subtaskDuration} seconds"), $userTimezone));
                $newSubtask->setStatus($subtask->isStatus());
                $newSubtask->setMaintask($clone);

                $clone->addSubtask($newSubtask);
            }

            $clones[] = $clone;
            $baseStart = $newStart;
            $baseEnd   = $newEnd;
        }

        return $clones;
    }
}
