<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('', name: 'main_')]
class MainController extends AbstractController
{
    /**
     * Contrôleur de la page d'accueil
     */
    #[Route('/', name: 'home')]
    public function home(): Response
    {


        return $this->render('main/home.html.twig');
    }

    /**
     * Contrôleur de la page de profil
     * Accès réservé aux connectés
     */
    #[Route('/mon-profil/', name: 'profil')]
    #[isGranted('ROLE_USER')]
    public function profil(): Response
    {


        return $this->render('main/profil.html.twig');
    }
}
