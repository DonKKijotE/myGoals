<?php
// src/Controller/GoalController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Form\Extension\Core\Type\DateType;
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

    #[Route('/', name: 'frontpage')]
    public function publicFrontpage(): Response
    {
      return $this->render('frontpage.html.twig');
    }

    #[Route('/current', name: 'frontpage_currentweek')]
    public function publicCurrentWeek(EntityManagerInterface $entityManager): Response
    {
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED');
      $user = $this->getUser();




      return $this->render('currentweek.html.twig');
    }





}
