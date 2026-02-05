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

use App\Repository\TaskRepository;
use App\Repository\SubTaskRepository;

use App\Service\WeekService;



class AjaxController extends AbstractController
{

    private WeekService $weekService;

    public function __construct(WeekService $weekService)
    {
        $this->weekService = $weekService;
    }


    //Endpoint para obtener eventos -> Maquearlo para ver si saca tasks y/o subtasks

    #[Route('/get-events', name: 'get_events')]
    public function privateGetEvents(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {

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

    $eventCollection = array();

    foreach($events as $item) {

        $start=date_format($item->getStart(), 'Y-m-d H:i:s');
        $end=date_format($item->getEndtime(), 'Y-m-d H:i:s');

         $eventCollection[] = array(
             'id' => $item->getId(),
             'title' => $item->getName(),
             'description' => $item->getDescription(),
             'category' => $item->getCategory(),
             'start' => $start,
             'end' => $end,
             'status' => $item->isStatus(),
             // ... Same for each property you want
         );
    }

    return new JsonResponse($eventCollection);

    }


    //Endpoint para cambiar el status de un evento o subevento.

    #[Route('/set-event-status/{tipo}/{id}', name: 'set_event_status')]
    public function privateSetEventStatus(Request $request, EntityManagerInterface $entityManager, string $tipo, int $id): JsonResponse
    {

        // Meter un voter para que cada uno solo toque lo suyo.

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

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

        // si es subtask, comprobar si todas las subtasks de la task están hechas
        if ($tipo === "subtask") {
            $task = $event->getMainTask(); // relación ManyToOne SubTask -> Task

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
        }


        $entityManager->flush();

        $response = array(
          'task' => $event->getId(),
          'status' => $marker,
          'success' => true,
        );

        return new JsonResponse($response);


    }

    //Endpoint para eliminar tasks o subtasks

    #[Route('/delete-event/{id}', name: 'delete_event')]
    public function privateDeleteEvent(Request $request, EntityManagerInterface $entityManager, int $id): JsonResponse
    {

        // Meter un voter para que cada uno solo toque lo suyo.

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $event = $entityManager->getRepository(Task::class)->find($id);

        if (!$event) {
          throw $this->createNotFoundException('No task found for id '.$id);
        }

        $entityManager->remove($event);
        $entityManager->flush();

        $response = array(
          'task' => $event->getId(),
          'success' => true,
        );

        return new JsonResponse($response);


    }






}
