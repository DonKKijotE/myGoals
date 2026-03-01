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
use App\Repository\SubTaskRepository;

use App\Service\WeekService;
use App\Service\DateTimeService;
use App\Service\ProgressService;
use App\Service\TaskCreationService;



class AjaxController extends AbstractController
{

    private WeekService $weekService;

    public function __construct(WeekService $weekService)
    {
        $this->weekService = $weekService;
    }


    //Endpoint para obtener eventos -> Maquearlo para ver si saca tasks y/o subtasks

    #[Route('/get-events', name: 'get_events')]
    public function privateGetEvents(Request $request, EntityManagerInterface $entityManager, DateTimeService $dateTimeService): JsonResponse
    {

    $this->denyAccessUnlessGranted('IS_AUTHENTICATED');


    $user = $this->getUser();

    $events = $entityManager->getRepository(Task::class)->findBy(
    ['owner' => $user],
    ['start' => 'ASC']
    );


    if (!$events) {
        throw $this->createNotFoundException(
            'No events found for user '.$user->getEmail()
       );
    }

    $eventCollection = [];


    foreach($events as $item) {

        $userTimezone = $this->getUser()->getTimezone();
        $start = $dateTimeService->toUserTime($item->getStart(), $userTimezone)->format('Y-m-d H:i:s');
        $end   = $dateTimeService->toUserTime($item->getEndTime(), $userTimezone)->format('Y-m-d H:i:s');

        $subtasksForTwig = [];

        foreach ($item->getSubTasks() as $sub) {
            $subtasksForTwig[] = [
                'id' => $sub->getId(),
                'title' => $sub->getName(),
                'start' => $dateTimeService->toUserTime($sub->getStart(), $userTimezone)->format('Y-m-d H:i:s'),
                'end' => $dateTimeService->toUserTime($sub->getEndTime(), $userTimezone)->format('Y-m-d H:i:s'),
            ];
        }

         $eventCollection[] = array(
             'id' => $item->getId(),
             'title' => $item->getName(),
             'description' => $item->getDescription(),
             'category' => $item->getCategory()->getName(),
             'category_icon' => $item->getCategory()->getIcon(),
             'category_color' => $item->getCategory()->getColor(),
             'start' => $start,
             'end' => $end,
             'status' => $item->isStatus(),
             'subtasks' => $subtasksForTwig,

         );
    }

    return new JsonResponse($eventCollection);

    }


    //Endpoint para cambiar el status de un evento o subevento.

    #[Route('/set-event-status/{tipo}/{id}', name: 'set_event_status')]
    public function privateSetEventStatus(Request $request, EntityManagerInterface $entityManager, string $tipo, int $id): JsonResponse
    {

        // Si marcas una task como hecha marca todas las subtasks como hechas.
        // Si marcas una subtask como hecha comprueba si todas las subtasks están hechas, entonces marca la task principal como hecha.

        // Meter un voter para que cada uno solo toque lo suyo.

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        if (!$request->isXmlHttpRequest())
        {
          throw $this->createNotFoundException('This is not an AJAX request.');
        }

        if($tipo == "task")
        {
          $event = $entityManager->getRepository(Task::class)->find($id);

        }

        elseif($tipo == "subtask")
        {
          $event = $entityManager->getRepository(SubTask::class)->find($id);
        }


        if (!$event) {
          throw $this->createNotFoundException('No task/subtask found for id '.$id);
        }

        if($event->isStatus() == 0) {
          $event->setStatus(1);
          $marker = 1;
        }
        else{
          $event->setStatus(0);
          $marker = 0;
        }

        if($tipo === "task" )  //Si marcas la task como hecha marca todas las subtask como hechas.
        {
          $subtasks = $event->getSubTasks();
          foreach ($subtasks as $st) {
            $st->setStatus(true);
          }

        }


        if ($tipo === "subtask") {  // si es subtask, comprobar si todas las subtasks de la task están hechas
            $task = $event->getMainTask();

            $allHechas = true;

            foreach ($task->getSubTasks() as $st) {
                if (!$st->isStatus()) {
                    $allHechas = false;
                    break;
                }
            }

              if ($allHechas) {
                  $task->setStatus(1);
              }

              if(!$allHechas && $task-> isStatus() === true)
              {
                $task->setStatus(false);
              }


          }


        $entityManager->flush();

        $response = [
            'success' => true,
            'data' => [
                'task' => $event->getId(),
                'status' => $marker
            ]
        ];


        return new JsonResponse($response);


    }

    //Endpoint para eliminar tasks o subtasks

    #[Route('/delete-event/{id}', name: 'delete_event')]
    public function privateDeleteEvent(Request $request, EntityManagerInterface $entityManager, int $id): JsonResponse
    {

        // Meter un voter para que cada uno solo toque lo suyo.

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        if (!$request->isXmlHttpRequest())
        {
          throw $this->createNotFoundException('This is not an AJAX request.');
        }

        $event = $entityManager->getRepository(Task::class)->find($id);

        if (!$event) {
          throw $this->createNotFoundException('No task found for id '.$id);
        }

        $entityManager->remove($event);
        $entityManager->flush();

        $response = [
            'success' => true,
            'data' => [
                'task' => $event->getId(),
            ]
        ];

        return new JsonResponse($response);


    }

    #[Route('/get-events-date/{period}', name: 'get_events_date')]
    public function privateGetEventsDate(Request $request, TaskRepository $taskRepository, string $period): JsonResponse
    {

    $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

    //Empezamos probando sacando las tareas del mes.

    $startOfPeriod = new \DateTime('first day of this month');
    $endOfPeriod = new \DateTime('last day of this month');

    $events = $taskRepository->findEventsForPeriod($startOfPeriod, $endOfPeriod);


    if (!$events) {
        throw $this->createNotFoundException(
            'No events found for user '.$user->getEmail()
       );
    }

    $eventCollection = array();

    foreach($events as $item) {

        $userTimezone = $this->getUser()->getTimezone();
        $start = $dateTimeService->toUserTime($item->getStart(), $userTimezone);
        $end   = $dateTimeService->toUserTime($item->getEndTime(), $userTimezone);

         $eventCollection[] = array(
             'id' => $item->getId(),
             'title' => $item->getName(),
             'description' => $item->getDescription(),
             'category' => $item->getCategory()->getName(),
             'category_icon' => $item->getCategory()->getIcon(),
             'category_color' => $item->getCategory()->getColor(),
             'start' => $start,
             'end' => $end,
             'status' => $item->isStatus(),
             // ... Same for each property you want
         );
    }

    return new JsonResponse($eventCollection);

    }

    #[Route('/test', name: 'get_events_date')]
    public function privateTest(Request $request, TaskRepository $taskRepository, DateTimeService $dateTimeService): JsonResponse
    {

      $user = $this->getUser();
      $events = $taskRepository->findEventsByUser($user);
      $userTimezone = $this->getUser()->getTimezone();

      $eventCollection = array_map(
          fn($event) => [
              'id' => $event->getId(),
              'title' => $event->getName(),
              'description' => $event->getDescription(),
              'category' => $event->getCategory()->getName(),
              'category_icon' => $event->getCategory()->getIcon(),
              'category_color' => $event->getCategory()->getColor(),
              'start' => $dateTimeService
                  ->toUserTime($event->getStart(), $userTimezone)
                  ->format('Y-m-d H:i:s'),
              'end' => $dateTimeService
                  ->toUserTime($event->getEndTime(), $userTimezone)
                  ->format('Y-m-d H:i:s'),
              'status' => $event->isStatus(),
              'subtasks' => array_map(
                  fn($sub) => [
                      'id' => $sub->getId(),
                      'title' => $sub->getName(),
                      'start' => $dateTimeService
                          ->toUserTime($sub->getStart(), $userTimezone)
                          ->format('Y-m-d H:i:s'),
                      'end' => $dateTimeService
                          ->toUserTime($sub->getEndTime(), $userTimezone)
                          ->format('Y-m-d H:i:s'),
                      'status' => $sub->isStatus(),
                  ],
                  $event->getSubTasks()->toArray() // convierte la colección Doctrine a array
              ),
          ],
          $events // este es tu array de eventos, puede tener cualquier número de elementos
      );

      return new JsonResponse($eventCollection);

    }

    #[Route('/get-progress', name: 'get_progress')]
    public function getProgress(
        ProgressService $progressService,
        TaskRepository $taskRepository,
        DateTimeService $dateTimeService
    ): JsonResponse {
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        // Semana en UTC para DB
        $weekUtc = $this->weekService->getWeek(null, 'UTC');
        $startUtc = $weekUtc['start'];
        $endUtc   = $weekUtc['end'];

        // Llamamos al servicio para calcular porcentajes
        $progress = $progressService->getProgressForPeriod($user, $startUtc, $endUtc);

        return new JsonResponse($progress);
    }

    #[Route('/task-create', name: 'create_task', methods: ['POST'])]
    public function create(Request $request, TaskCreationService $taskCreationService): JsonResponse
    {

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['status' => 'error', 'message' => 'JSON inválido'], 400);
        }

        try {
            // Aquí le pasamos el usuario actual
            $task = $taskCreationService->createFromArray($data, $this->getUser());

            return $this->json([
                'status' => 'ok',
                'id' => $task->getId(),
                'recurrenceGroup' => $task->getRecurrenceGroup()?->toRfc4122()
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

}
