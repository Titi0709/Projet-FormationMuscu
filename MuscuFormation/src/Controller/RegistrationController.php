<?php

// Controller RegistrationController.php

namespace App\Controller;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'register')]
    public function register(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Création de l'objet utilisateur
        $utilisateur = new Utilisateur();

           // Définir le rôle par défaut
    $utilisateur->setRole(1);


        // Création du formulaire
        $form = $this->createFormBuilder($utilisateur)
            ->add('nom', TextType::class, ['label' => 'Nom'])
            ->add('email', EmailType::class)
            ->add('mot_de_passe', PasswordType::class)
            ->add('register', SubmitType::class, ['label' => 'S\'inscrire'])
            ->getForm();

        // Traitement du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hash du mot de passe
            $hashedPassword = password_hash($utilisateur->getMotDePasse(), PASSWORD_DEFAULT);
            $utilisateur->setMotDePasse($hashedPassword);

            // Enregistrement de l'utilisateur en base de données
            $entityManager->persist($utilisateur);
            $entityManager->flush();

            // Redirection vers la page de connexion
            return $this->redirectToRoute('login');
        }

        return $this->render('register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
