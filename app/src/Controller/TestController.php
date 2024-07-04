<?php

namespace App\Controller;

use App\Service\AuthService;
use App\Service\TestService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TestController extends AbstractController
{
    private AuthService $authService;
    private TestService $testService;

    public function __construct(AuthService $authService, TestService $testService)
    {
        $this->authService = $authService;
        $this->testService = $testService;
    }

    public function test(Request $request, int $testId, int $step = 1): Response
    {
        $test = $this->testService->getTest($testId);
        if (!$test) {
            throw $this->createNotFoundException('Test not found');
        }

        $session = $request->getSession();
        $session->set('test_id', $testId);

        $questions = $test->getQuestions();
        $totalSteps = count($questions);

        if ($step > $totalSteps) {
            return $this->redirectToRoute('test_result', ['testId' => $testId]);
        }

        $question = $questions[$step - 1];
        $form = $this->testService->createForm($question);

        $result = $this->testService->processStep($request, $session, $step, $totalSteps, $form);
        if (isset($result['next_step'])) {
            return $this->redirectToRoute('test', ['testId' => $testId, 'step' => $result['next_step']]);
        } elseif (isset($result['complete'])) {
            return $this->redirectToRoute('test_result');
        }

        return $this->render('test/step.html.twig', [
            'form' => $form->createView(),
            'step' => $step,
            'totalSteps' => $totalSteps,
            'question' => $question,
        ]);
    }

    public function result(Request $request, int $testId = null): Response
    {
        $session = $request->getSession();
        $testId = $session->get('test_id');

        $test = $this->testService->getTest($testId);
        if (!$test) {
            throw $this->createNotFoundException('Test not found');
        }

        $answers = $session->get('answers', []);

        if ($this->authService->isAuthenticated()) {
            $user = $this->authService->getUser();
            $this->testService->saveTestResult($test, $answers, $user);
        }

        $results = $this->testService->generateResults($test, $answers);

        return $this->render('test/result.html.twig', [
            'results' => $results,
        ]);
    }
}