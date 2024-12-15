<?php


namespace App\Controller;

use App\Entity\Utilisateur;
use App\Entity\Achat;
use App\Service\JwtTokenService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends AbstractController
{
    private JwtTokenService $jwtTokenService;

    public function __construct(JwtTokenService $jwtTokenService)
    {
        $this->jwtTokenService = $jwtTokenService;
    }

    #[Route('/login', name: 'login')]
    public function login(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Création du formulaire de login
        $form = $this->createFormBuilder()
            ->add('email')
            ->add('mot_de_passe')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $motDePasse = $form->get('mot_de_passe')->getData();

            // Recherche UTI DB
            $utilisateur = $entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);

            if ($utilisateur && password_verify($motDePasse, $utilisateur->getMotDePasse())) {
                // Authentification réussie, génération du jeton JWT
                $jwtToken = $this->jwtTokenService->createToken($utilisateur);

                // Ajouter le jeton JWT dans la session
                $request->getSession()->set('jwt_token', $jwtToken->getToken());

                // Ajouter l'utilisateur dans la session
                $request->getSession()->set('user', [
                    'id' => $utilisateur->getId(),
                    'email' => $utilisateur->getEmail(),
                    'roles' => $utilisateur->getRole(),
                ]);


                                // VERIF ROLE 2
                                if ($utilisateur->getRole() === 2) {
                                    return $this->redirectToRoute('coach_dashboard');
                                }
                
                // Vérifier UTI ACHAT
                $achat = $entityManager->getRepository(Achat::class)->findOneBy(['utilisateur' => $utilisateur]);
                if ($achat) {
                    return $this->redirectToRoute('InfoPayment');
                }


                
                return $this->redirectToRoute('programmes');
            } else {
                
                $this->addFlash('error', 'Adresse email ou mot de passe incorrect.');
            }
        }

        return $this->render('login.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
