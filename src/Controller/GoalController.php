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



class GoalController extends AbstractController
{

    private WeekService $weekService;

    public function __construct(WeekService $weekService)
    {
        $this->weekService = $weekService;
    }

    #[Route('/home', name: 'dashboard')]
    public function privateHome(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $task = new Task();

        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $task = $form->getData();

            // Setting datetime manually until datepicker is put into form.

            $startdate = new \DateTime("2026-01-22 09:00:00");
            $task->setStart($startdate);
            $enddate = new \DateTime('2026-01-22 10:00:00');
            $task->setEndTime($enddate);


            $user = $this->getUser();
            $task->setOwner($user);

            $entityManager->persist($task);
            $entityManager->flush();

            return $this->redirectToRoute('dashboard');
        }



        return $this->render('dashboard.html.twig', [
            'form' => $form,
        ]);
    }


    #[Route('/current', name: 'frontpage_currentweek')]
    public function publicCurrentWeek(Request $request, EntityManagerInterface $entityManager): Response
    {
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

      $task = new Task();

      $form = $this->createForm(TaskType::class, $task);

      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

          $task = $form->getData();

          $user = $this->getUser();
          $task->setOwner($user);
          $entityManager->persist($task);
          $entityManager->flush();

          return $this->redirectToRoute('frontpage_currentweek');
      }

      //return $this->render('dashboard.html.twig');

      return $this->render('currentweek.html.twig', [
          'form' => $form,
      ]);



      //return $this->render('currentweek.html.twig');
    }

    #[Route('/', name: 'frontpage')]
    public function publicFrontpage(TaskRepository $taskRepository): Response
    {

        $week = $this->weekService->getWeek(); // current week
        $startOfWeek = $week['start']; // DateTime object
        $endOfWeek = $week['end'];     // DateTime object

        // Get all events overlapping with the week
        $events = $taskRepository->findEventsForWeek($startOfWeek, $endOfWeek);

        $eventCollection = [];


        foreach ($events as $item) {
            $eventCollection[] = [
                'id' => $item->getId(),
                'title' => $item->getName(),
                'description' => $item->getDescription(),
                'category' => $item->getCategory(),
                'start' => $item->getStart()->format('Y-m-d H:i:s'),
                'end' => $item->getEndTime()->format('Y-m-d H:i:s'),
                'status' => $item->isStatus(),
                'subtasks' => $item->getSubTasks(),
            ];
        }

        //dd($eventCollection);

        $totalEventos = count($eventCollection);

        $categoriasData = [];

        foreach ($eventCollection as $evento) {
            $catName = $evento['category']->getName();

            if (!isset($categoriasData[$catName])) {
                $categoriasData[$catName] = [
                    'nombre' => $catName,
                    'total' => 0,
                    'hechas' => 0,
                ];
            }

            $categoriasData[$catName]['total']++;
            if ($evento['status']) {
                $categoriasData[$catName]['hechas']++;
            }
        }

        // Calcular porcentaje interno y asignar clase
        foreach ($categoriasData as $catName => $catData) {
            $total = $catData['total'];
            $hechas = $catData['hechas'];

            $porcentajeGlobal = $totalEventos > 0 ? ($total / $totalEventos) * 100 : 0;
            $porcentajeInterno = $total > 0 ? ($hechas / $total) * 100 : 0;

            // Asignar color según horquillas idénticas a la barra principal
            if ($porcentajeInterno <= 20) {
                $colorClass = 'bg-danger';
            } elseif ($porcentajeInterno <= 60) {
                $colorClass = 'bg-warning';
            } elseif ($porcentajeInterno < 100) {
                $colorClass = 'bg-info';
            } else {
                $colorClass = 'bg-success';
            }

            $categoriasData[$catName]['porcentaje_global'] = round($porcentajeGlobal, 2);
            $categoriasData[$catName]['porcentaje_interno'] = round($porcentajeInterno, 2);
            $categoriasData[$catName]['color_class'] = $colorClass;
        }

        // Convertir a array indexado para Twig
        $categoriasData = array_values($categoriasData);

        //dd($categoriasData);

        $totalEventos = count($eventCollection);

        $hechos = 0;

        foreach ($eventCollection as $evento) {
            if ($evento['status']) { // no isStatus(), es un array
                $hechos++;
            }
        }



        // Evitamos división por cero
        $porcentajeHechos = $totalEventos > 0 ? ($hechos / $totalEventos) * 100 : 0;
        $porcentajeHechos = round($porcentajeHechos, 2);


        //return new JsonResponse($eventCollection);

        return $this->render('frontpage.html.twig', [
            'events' => $eventCollection,
            'categoriesData' => $categoriasData,
            'num_events' => $totalEventos,
            'eventos_hechos' => $hechos,
            'porcentajeHechos' => $porcentajeHechos,
            'fechainicio' => $startOfWeek,
            'fechafin' => $endOfWeek,
        ]);


    }

    #[Route('/event/{id}', name: 'view_event')]
    public function viewEvent(Request $request, EntityManagerInterface $entityManager, int $id): Response
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




      return $this->render('event.html.twig', [
          'event' => $event,
          'porcentajeHechos' => $porcentajeHechos,
      ]);

    }





}
