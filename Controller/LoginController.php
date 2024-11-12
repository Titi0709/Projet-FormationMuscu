<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'login')]
    public function login(Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createFormBuilder()
            ->add('email')
            ->add('mot_de_passe')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $motDePasse = $form->get('mot_de_passe')->getData();

            $utilisateur = $entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);

            if ($utilisateur && password_verify($motDePasse, $utilisateur->getMotDePasse())) {
                // Authentification réussie, redirection vers la page des formations
                return $this->redirectToRoute('programmes');
            } else {
                // Identifiants incorrects
                $this->addFlash('error', 'Adresse email ou mot de passe incorrect.');
            }
        }

        return $this->render('login.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
