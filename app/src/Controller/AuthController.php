<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Service\AuthService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends AbstractController
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);

            if ($existingUser) {
                $this->addFlash('notice', 'User already exists. Logging in...');
                $this->authService->login($existingUser);
            } else {
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('notice', 'User registered successfully.');
                $this->authService->login($user);
            }

            return $this->redirectToRoute('index');
        }

        return $this->render('login.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function logout(): Response
    {
        $this->authService->logout();
        return $this->redirectToRoute('index');
    }
}