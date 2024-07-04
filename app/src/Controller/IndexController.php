<?php

namespace App\Controller;

use App\Entity\Test;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class IndexController extends AbstractController
{
    public function index(EntityManagerInterface $entityManager): Response
    {
        $tests = $entityManager->getRepository(Test::class)->findAll();

        return $this->render('index.html.twig', [
            'tests' => $tests,
        ]);
    }
}