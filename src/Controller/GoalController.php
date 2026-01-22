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



class GoalController extends AbstractController
{

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

        //return $this->render('dashboard.html.twig');

        return $this->render('dashboard.html.twig', [
            'form' => $form,
        ]);
    }

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
             'title' => $item->getName(),
             'description' => $item->getDescription(),
             'category' => $item->getCategory(),
             'start' => $start,
             'end' => $end,
             // ... Same for each property you want
         );
    }

    return new JsonResponse($eventCollection);

    }



    #[Route('/', name: 'frontpage')]
    public function publicFrontpage(): Response
    {
      return $this->render('frontpage.html.twig');
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

          // Setting datetime manually until datepicker is put into form.

          //$startdate = new \DateTime("2026-01-20 09:00:00");
          //$task->setStart($startdate);
          //$enddate = new \DateTime('2026-01-20 10:00:00');
          //$task->setEndTime($enddate);


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





}
