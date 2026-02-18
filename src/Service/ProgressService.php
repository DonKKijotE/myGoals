<?php
// src/Service/ProgressService.php

namespace App\Service;

use App\Entity\User;
use App\Repository\TaskRepository;
use DateTimeInterface;

class ProgressService
{
    private TaskRepository $taskRepository;

    public function __construct(TaskRepository $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    /**
     * Calcula progreso global y por categoría para un usuario en un rango de tiempo
     *
     * @param User $user
     * @param DateTimeInterface $start
     * @param DateTimeInterface $end
     * @return array
     */
    public function getProgressForPeriod(User $user, DateTimeInterface $start, DateTimeInterface $end): array
    {
        // Traer todas las Tasks del usuario en el rango
        $tasks = $this->taskRepository->findUserEventsForPeriod($start, $end, $user);

        $totalGlobal = count($tasks);
        $doneGlobal = 0;
        $categories = [];

        foreach ($tasks as $task) {
            if ($task->isStatus()) {
                $doneGlobal++;
            }

            $category = $task->getCategory();
            $categoryId = $category->getId();

            if (!isset($categories[$categoryId])) {
                $categories[$categoryId] = [
                    'id' => $categoryId,
                    'name' => $category->getName(),
                    'color' => $category->getColor(),
                    'total' => 0,
                    'hechas' => 0,
                    'percent_interno' => 0,
                    'percent_global' => 0,
                    'color_class' => '',
                ];
            }

            $categories[$categoryId]['total']++;

            if ($task->isStatus()) {
                $categories[$categoryId]['hechas']++;
            }
        }

        // Calcular porcentajes por categoría
        foreach ($categories as &$cat) {
            // % interno (hechas / total de la categoría)
            if ($cat['total'] > 0) {
              $cat['percent_interno'] = $cat['total'] > 0 ? round(($cat['hechas'] / $cat['total']) * 100, 0) : 0;

            }

            // % global (total de la categoría / total global de tasks)
            if ($totalGlobal > 0) {
                $cat['percent_global']   = $totalGlobal > 0 ? round(($cat['total'] / $totalGlobal) * 100, 0) : 0;
            }

            // Definir clase de color según horquillas
            if ($cat['percent_interno'] <= 20) $cat['color_class'] = 'bg-danger';
            elseif ($cat['percent_interno'] <= 60) $cat['color_class'] = 'bg-warning';
            elseif ($cat['percent_interno'] < 100) $cat['color_class'] = 'bg-info';
            else $cat['color_class'] = 'bg-success';
        }
        unset($cat);

        // Porcentaje global de todas las tasks
        $percentGlobal = $totalGlobal > 0 ? round(($doneGlobal / $totalGlobal) * 100, 0) : 0;


        return [
            'global' => [
                'total' => $totalGlobal,
                'hechas' => $doneGlobal,
                'percent' => $percentGlobal,
            ],
            'categories' => array_values($categories),
        ];
    }
}
