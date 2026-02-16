<?php
// src/Controller/GoalController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Doctrine\ORM\EntityManagerInterface;

use App\Form\Type\TaskType;

use App\Entity\User;
use App\Entity\Task;
use App\Entity\SubTask;
use App\Entity\Category;

use App\Repository\TaskRepository;

use App\Service\WeekService;
use App\Service\DateTimeService;



class GoalController extends AbstractController
{

    private WeekService $weekService;

    public function __construct(WeekService $weekService)
    {
        $this->weekService = $weekService;
    }



    #[Route('/current', name: 'frontpage_currentweek')]
    public function publicCurrentWeek(Request $request, EntityManagerInterface $entityManager, DateTimeService $dateTimeService): Response
    {
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

      $task = new Task();

      $form = $this->createForm(TaskType::class, $task);

      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

          $task = $form->getData();

          $user = $this->getUser();

          $userTimezone = $this->getUser()->getTimezone();


          if ($task->getStart()) {
            $task->setStart(
                $dateTimeService->toUtc($task->getStart(), $userTimezone)
            );
          }

          if ($task->getEndtime()) {
              $task->setEndtime(
                  $dateTimeService->toUtc($task->getEndtime(), $userTimezone)
              );
          }

          foreach ($task->getSubtasks() as $subtask) {
              $subtask->setStart(
                  $dateTimeService->toUtc($subtask->getStart(), $userTimezone)
              );

              $subtask->setEndtime(
                  $dateTimeService->toUtc($subtask->getEndTime(), $userTimezone)
              );
          }

          $task->setOwner($user);
          $entityManager->persist($task);
          $entityManager->flush();

          return $this->redirectToRoute('frontpage_currentweek');
      }

      return $this->render('currentweek.html.twig', [
          'form' => $form,
      ]);

    }


    #[Route('/', name: 'frontpage')]
    public function publicFrontpage(TaskRepository $taskRepository, DateTimeService $dateTimeService): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $user = $this->getUser();
        $userTimezone = $user->getTimezone();

        // --- Semana en zona del usuario ---
        $weekUtc = $this->weekService->getWeek(null, 'UTC');
        // En zona del user para mostrar en twig
        $startUser = $weekUtc['start']->setTimezone(new \DateTimeZone($user->getTimezone()));
        $endUser   = $weekUtc['end']->setTimezone(new \DateTimeZone($user->getTimezone()));

        // --- Semana en UTC para DB ---
        $startUtc = $dateTimeService->toUtc($startUser, $userTimezone);
        $endUtc   = $dateTimeService->toUtc($endUser, $userTimezone);

        $events = $taskRepository->findEventsForWeek($startUtc, $endUtc, $user);
        //$events = $taskRepository->findAll();

        $eventCollection = [];

        foreach ($events as $item) {
            $start = $dateTimeService->toUserTime($item->getStart(), $userTimezone);
            $end   = $dateTimeService->toUserTime($item->getEndTime(), $userTimezone);

            $subtasksForTwig = [];
            foreach ($item->getSubTasks() as $sub) {
                $subtasksForTwig[] = [
                    'id' => $sub->getId(),
                    'title' => $sub->getName(),
                    'start' => $dateTimeService->toUserTime($sub->getStart(), $userTimezone),
                    'end' => $dateTimeService->toUserTime($sub->getEndTime(), $userTimezone),
                ];
            }

            $eventCollection[] = [
                'id' => $item->getId(),
                'title' => $item->getName(),
                'description' => $item->getDescription(),
                'category' => $item->getCategory(),
                'start' => $start,
                'end' => $end,
                'status' => $item->isStatus(),
                'subtasks' => $subtasksForTwig,
            ];
        }

        // --- Tus cálculos de categorías y porcentajes intactos ---
        $totalEventos = count($eventCollection);
        $categoriasData = [];
        foreach ($eventCollection as $evento) {
            $catName = $evento['category']->getName();
            if (!isset($categoriasData[$catName])) {
                $categoriasData[$catName] = ['nombre' => $catName, 'total' => 0, 'hechas' => 0];
            }
            $categoriasData[$catName]['total']++;
            if ($evento['status']) $categoriasData[$catName]['hechas']++;
        }

        foreach ($categoriasData as $catName => $catData) {
            $total = $catData['total'];
            $hechas = $catData['hechas'];

            $porcentajeGlobal = $totalEventos > 0 ? ($total / $totalEventos) * 100 : 0;
            $porcentajeInterno = $total > 0 ? ($hechas / $total) * 100 : 0;

            if ($porcentajeInterno <= 20) $colorClass = 'bg-danger';
            elseif ($porcentajeInterno <= 60) $colorClass = 'bg-warning';
            elseif ($porcentajeInterno < 100) $colorClass = 'bg-info';
            else $colorClass = 'bg-success';

            $categoriasData[$catName]['porcentaje_global'] = round($porcentajeGlobal, 2);
            $categoriasData[$catName]['porcentaje_interno'] = round($porcentajeInterno, 2);
            $categoriasData[$catName]['color_class'] = $colorClass;
        }

        $categoriasData = array_values($categoriasData);

        $hechos = 0;
        foreach ($eventCollection as $evento) {
            if ($evento['status']) $hechos++;
        }
        $porcentajeHechos = $totalEventos > 0 ? ($hechos / $totalEventos) * 100 : 0;
        $porcentajeHechos = round($porcentajeHechos, 2);

        // --- Render Twig usando fechas en zona del usuario ---
        return $this->render('frontpage.html.twig', [
            'events' => $eventCollection,
            'categoriesData' => $categoriasData,
            'num_events' => $totalEventos,
            'eventos_hechos' => $hechos,
            'porcentajeHechos' => $porcentajeHechos,
            'fechainicio' => $startUser,
            'fechafin' => $endUser,
        ]);
    }

    #[Route('/event/{id}', name: 'view_event')]
    public function viewEvent(Request $request, EntityManagerInterface $entityManager, DateTimeService $dateTimeService, int $id): Response
    {

      $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

      $event = $entityManager->getRepository(Task::class)->find($id);

      if (!$event) {
        throw $this->createNotFoundException('No task found for id '.$id);
      }

      $estadoTarea = $event->isStatus();
      $subTareas = $event->getSubTasks();
      $totalSubTareas = count($subTareas);
      $subTareasHechas = 0;

      foreach ($subTareas as $subTarea) {
          if ($subTarea->isStatus()) {
              $subTareasHechas++;
          }
      }

      // porcentaje basado en subtareas
      $porcentajeHechos = $totalSubTareas > 0
          ? round(($subTareasHechas / $totalSubTareas) * 100, 2)
          : 0;

      // si la tarea principal ya está completa, fuerza 100%
      if ($event->isStatus() === true) {
          $porcentajeHechos = 100;
      }

      $userTimezone = $this->getUser()->getTimezone();
      $start = $dateTimeService->toUserTime($event->getStart(), $userTimezone);
      $end   = $dateTimeService->toUserTime($event->getEndTime(), $userTimezone);



      $subtasksForTwig = [];

      foreach ($event->getSubTasks() as $sub) {
          $subtasksForTwig[] = [
              'entity' => $sub,
              'start'  => $dateTimeService->toUserTime($sub->getStart(), $userTimezone),
              'end'    => $dateTimeService->toUserTime($sub->getEndTime(), $userTimezone),
          ];
          //dump($sub->getStart(), $dateTimeService->toUserTime($sub->getStart(), $userTimezone));
      }


      return $this->render('event.html.twig', [
          'event' => $event,
          'subtasks' => $subtasksForTwig,
          'start' => $start,
          'end' => $end,
          'porcentajeHechos' => $porcentajeHechos,
      ]);

    }





}
