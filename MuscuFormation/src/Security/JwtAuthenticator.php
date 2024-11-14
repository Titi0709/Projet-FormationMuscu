<?php

// src/Security/JwtAuthenticator.php

namespace App\Security;

use App\Repository\UserRepository;
use App\Service\JwtService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface; // Ajout pour la compatibilité

class JwtAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private JwtService $jwtService, 
        private UserRepository $userRepository
    ) {}

    public function supports(Request $request): ?bool
    {
        return str_starts_with($request->headers->get('Authorization', ''), 'Bearer ');
    }

    public function authenticate(Request $request): Passport
    {
        $token = substr($request->headers->get('Authorization'), 7);
        $parsedToken = $this->jwtService->parseToken($token);

        if (!$parsedToken) {
            throw new AuthenticationException('Token JWT invalide ou expiré.');
        }

        $userId = $parsedToken->claims()->get('uid');
        $user = $this->userRepository->find($userId);

        return new SelfValidatingPassport(new UserBadge($userId, fn() => $user));
    }

    // Méthode appelée en cas de succès de l'authentification
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?\Symfony\Component\HttpFoundation\Response
    {
        // Vous pouvez personnaliser la réponse après une authentification réussie
        return null; // Cela permet de continuer normalement le processus de la requête
    }

    // Méthode appelée en cas d'échec de l'authentification
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?\Symfony\Component\HttpFoundation\Response
    {
        // Retourne une réponse d'erreur JSON en cas d'échec de l'authentification
        return new JsonResponse(['error' => 'Échec de l\'authentification: ' . $exception->getMessage()], 401);
    }
}
