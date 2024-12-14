<?php
// src/Controller/MasseController.php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\JwtTokenService;
use App\Entity\Programme; // Import correct

class MasseController extends AbstractController
{
    private JwtTokenService $jwtTokenService;

    public function __construct(JwtTokenService $jwtTokenService)
    {
        $this->jwtTokenService = $jwtTokenService;
    }

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
    }

    #[Route('/Masse/programme/{id}', name: 'Masse_programme')]
    public function showProgramme($id, EntityManagerInterface $entityManager): Response
    {
        $programme = $entityManager->getRepository(Programme::class)->find($id);
        
        if (!$programme) {
            throw $this->createNotFoundException('Programme non trouvé');
        }

        return $this->render('Masse/programme_detail.html.twig', [
            'programme' => $programme,
        ]);
    }

    #[Route('/Masse/programme/{id}/ajouter-avis', name: 'Masse_ajouter_avis')]
    public function addReview(Request $request, $id, EntityManagerInterface $entityManager): Response
    {
        $avisContent = $request->request->get('avis');  // On suppose que l'avis est envoyé via POST
        
        if (!$avisContent) {
            $this->addFlash('error', 'Avis non fourni.');
            return $this->redirectToRoute('Masse_programme', ['id' => $id]);
        }

        $programme = $entityManager->getRepository(Programme::class)->find($id);
        
        if (!$programme) {
            throw $this->createNotFoundException('Programme non trouvé');
        }

        $avis = new Avis();
        $avis->setContenu($avisContent);
        $avis->setProgramme($programme);
        $avis->setUtilisateur($this->getUser());  // Supposons que l'utilisateur est stocké

        $entityManager->persist($avis);
        $entityManager->flush();

        $this->addFlash('success', 'Avis ajouté avec succès.');
        return $this->redirectToRoute('Masse_programme', ['id' => $id]);
    }

    #[Route('/Masse/avis/{id}/supprimer', name: 'Masse_supprimer_avis')]
    public function deleteReview($id, EntityManagerInterface $entityManager): Response
    {
        $avis = $entityManager->getRepository(Avis::class)->find($id);

        if (!$avis) {
            throw $this->createNotFoundException('Avis non trouvé');
        }

        // Vérifiez que l'utilisateur actuel a bien le droit de supprimer cet avis
        if ($avis->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $entityManager->remove($avis);
        $entityManager->flush();

        $this->addFlash('success', 'Avis supprimé avec succès.');
        return $this->redirectToRoute('Masse_programme', ['id' => $avis->getProgramme()->getId()]);
    }

    #[Route('/Masse/programme/{id}/payer', name: 'Masse_payer_programme')]
    public function payment(Request $request, $id, EntityManagerInterface $entityManager): Response
    {
        $programme = $entityManager->getRepository(Programme::class)->find($id);

        if (!$programme) {
            throw $this->createNotFoundException('Programme non trouvé');
        }

        // Supposons que le paiement a été validé par un système externe
        $paymentSuccess = $request->request->get('payment_success');  // Vaut true ou false
        
        if ($paymentSuccess) {
            $payment = new Payment();
            $payment->setProgramme($programme);
            $payment->setUtilisateur($this->getUser());
            $payment->setMontant($programme->getPrix());  // Si vous avez un prix dans le programme

            $entityManager->persist($payment);
            $entityManager->flush();

            $this->addFlash('success', 'Paiement effectué avec succès.');
        } else {
            $this->addFlash('error', 'Le paiement a échoué. Veuillez réessayer.');
        }

        return $this->redirectToRoute('Masse_programme', ['id' => $id]);
    }
}
