<?php
// src/Controller/GoalController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class GoalController extends AbstractController
{

    #[Route('/')]
    public function pruebaLogin(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/home')]
    public function pruebaHome(): Response
    {
        //$this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        return $this->render('home.html.twig');
    }
}
