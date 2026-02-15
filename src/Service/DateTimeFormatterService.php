<?php
namespace App\Service;

use App\Entity\Task;

class DateTimeFormatterService
{
    /**
     * Convierte un array de tasks y subtasks a la zona horaria del usuario
     * y devuelve un array listo para pasar a Twig.
     */
    public function convertTasksToUserTime(array $tasks, string $userTimezone): array
    {
        $result = [];

        foreach ($tasks as $task) {
            $start = $task->getStart()
                ? $task->getStart()->setTimezone(new \DateTimeZone($userTimezone))
                : null;
            $end = $task->getEndTime()
                ? $task->getEndTime()->setTimezone(new \DateTimeZone($userTimezone))
                : null;

            $subtasksForTwig = [];
            foreach ($task->getSubTasks() as $sub) {
                $subStart = $sub->getStart()
                    ? $sub->getStart()->setTimezone(new \DateTimeZone($userTimezone))
                    : null;
                $subEnd = $sub->getEndTime()
                    ? $sub->getEndTime()->setTimezone(new \DateTimeZone($userTimezone))
                    : null;

                $subtasksForTwig[] = [
                    'id'    => $sub->getId(),
                    'title' => $sub->getName(),
                    'start' => $subStart,
                    'end'   => $subEnd,
                ];
            }

            $result[] = [
                'id'        => $task->getId(),
                'title'     => $task->getName(),
                'description'=> $task->getDescription(),
                'category'  => $task->getCategory()->getName(),
                'category_icon' => $task->getCategory()->getIcon(),
                'category_color'=> $task->getCategory()->getColor(),
                'start'     => $start,
                'end'       => $end,
                'status'    => $task->isStatus(),
                'subtasks'  => $subtasksForTwig,
            ];
        }

        return $result;
    }
}
