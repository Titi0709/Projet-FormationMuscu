<?php
// src/Controller/ProgrammeController.php

namespace App\Controller;

use App\Entity\Programme;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\JwtTokenService;

class ProgrammeController extends AbstractController
{
    private JwtTokenService $jwtTokenService;

    public function __construct(JwtTokenService $jwtTokenService)
    {
        $this->jwtTokenService = $jwtTokenService;
    }

    #[Route('/programmes', name: 'programmes')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Déboguer la session pour vérifier si le jeton est bien stocké
        $request->getSession()->all(); // À retirer après débogage

        // Récupération du jeton JWT dans la session
        $jwtToken = $request->getSession()->get('jwt_token');
        
        if (!$jwtToken) {
            return $this->redirectToRoute('login');
        }

        // Vérifier que le jeton est une chaîne de caractères
        if (!is_string($jwtToken)) {
            return $this->redirectToRoute('login');
        }

        // Valider le jeton JWT
        $jwtTokenEntity = $this->jwtTokenService->validateToken($jwtToken);

        if (!$jwtTokenEntity || !$jwtTokenEntity->getUtilisateur()) {
            return $this->redirectToRoute('login');
        }

        // Décoder le JWT et obtenir l'utilisateur associé
        $utilisateur = $this->jwtTokenService->decodeJwt($jwtTokenEntity->getToken());

        if (!$utilisateur) {
            return $this->redirectToRoute('login');
        }

        // Récupérer les programmes à afficher
        $programmes = $entityManager->getRepository(Programme::class)->findAll();

        return $this->render('formations.html.twig', [
            'programmes' => $programmes,
        ]);
    }
}
