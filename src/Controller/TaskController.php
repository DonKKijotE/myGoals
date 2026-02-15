<?php
// src/Controller/TaskController.php
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



class TaskController extends AbstractController
{

    private WeekService $weekService;

    public function __construct(WeekService $weekService)
    {
        $this->weekService = $weekService;
    }



    #[Route('/task-test', name: 'task_RegisterEvents')]
    public function publicCurrentWeek(Request $request, EntityManagerInterface $entityManager, DateTimeService $dateTimeService): JsonResponse
    {

      $user = $this->getUser();
      $task = new Task();
      $userTimezone = $this->getUser()->getTimezone();







    }








}
