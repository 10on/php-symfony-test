<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AuthService
{
    private EntityManagerInterface $entityManager;
    private SessionInterface $session;

    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->session = $requestStack->getSession();
    }

    public function login(User $user): void
    {
        $this->session->set('user_id', $user->getId());
    }

    public function logout(): void
    {
        $this->session->remove('user_id');
    }

    public function getUser(): ?User
    {
        $userId = $this->session->get('user_id');

        if ($userId) {
            return $this->entityManager->getRepository(User::class)->find($userId);
        }

        return null;
    }

    public function isAuthenticated(): bool
    {
        return $this->session->has('user_id');
    }
}