<?php

namespace App\Service;

use App\Entity\Test;
use App\Entity\TestAnswer;
use App\Entity\TestResult;
use App\Entity\Question;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Form\FormInterface;

class TestService
{
    private EntityManagerInterface $entityManager;
    private FormFactoryInterface $formFactory;

    public function __construct(EntityManagerInterface $entityManager, FormFactoryInterface $formFactory)
    {
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
    }

    public function getTest(int $testId): ?Test
    {
        return $this->entityManager->getRepository(Test::class)->find($testId);
    }

    public function createForm(Question $question): FormInterface
    {
        return $this->formFactory->createBuilder()
            ->add('answer', ChoiceType::class, [
                'choices' => $question->getAnswers(),
                'choice_label' => function ($choice) {
                    return $choice->getText();
                },
                'choice_value' => 'id',
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('next', SubmitType::class, ['label' => 'Next'])
            ->getForm();
    }

    public function processStep(Request $request, SessionInterface $session, int $step, int $totalSteps, $form): array
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $answers = $session->get('answers', []);

            $answers[$step] = array_map(function ($answer) {
                return $answer->getId();
            }, $data['answer']);

            $session->set('answers', $answers);

            if ($step < $totalSteps) {
                return ['next_step' => $step + 1];
            } else {
                return ['complete' => true];
            }
        }

        return [];
    }

    public function saveTestResult(Test $test, array $answers, $user): void
    {
        $questions = $test->getQuestions();
        $results = [];

        foreach ($questions as $step => $question) {
            $correctKey = $question->getKey();
            $userAnswers = $answers[$step + 1] ?? [];

            $userAnswerMask = 0;
            $answersArray = $question->getAnswers()->toArray();

            $userAnswerObjects = [];
            foreach ($answersArray as $index => $answer) {
                if (in_array($answer->getId(), $userAnswers)) {
                    $userAnswerMask |= (1 << $index);
                    $userAnswerObjects[] = $answer;
                }
            }

            $isCorrect = ($userAnswerMask & $correctKey) > 0 && ($userAnswerMask | $correctKey) == $correctKey;
            $results[] = [
                'question' => $question,
                'userAnswers' => $userAnswerObjects,
                'isCorrect' => $isCorrect,
                'userAnswerMask' => $userAnswerMask
            ];
        }

        $testResult = new TestResult();
        $testResult->setUserId($user);
        $testResult->setCreatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($testResult);

        foreach ($results as $result) {
            $testAnswer = new TestAnswer();
            $testAnswer->setTestResult($testResult);
            $testAnswer->setQuestion($result['question']);
            $testAnswer->setAnswers($result['userAnswerMask']);
            $testAnswer->setCreatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($testAnswer);
        }

        $this->entityManager->flush();
    }

    public function generateResults(Test $test, array $answers): array
    {
        $questions = $test->getQuestions();
        $results = [];

        foreach ($questions as $step => $question) {
            $correctKey = $question->getKey();
            $userAnswers = $answers[$step + 1] ?? [];

            $userAnswerMask = 0;
            $answersArray = $question->getAnswers()->toArray();

            $userAnswerObjects = [];
            $answerStates = [];
            foreach ($answersArray as $index => $answer) {
                $isCorrectAnswer = ($correctKey & (1 << $index)) > 0;
                $answerStates[$answer->getId()] = $isCorrectAnswer ? 'correct' : 'incorrect';
                if (in_array($answer->getId(), $userAnswers)) {
                    $userAnswerMask |= (1 << $index);
                    $userAnswerObjects[] = $answer;
                }
            }

            $isCorrect = ($userAnswerMask & $correctKey) > 0 && ($userAnswerMask | $correctKey) == $correctKey;
            $results[] = [
                'question' => $question,
                'userAnswers' => $userAnswerObjects,
                'answerStates' => $answerStates,
                'isCorrect' => $isCorrect
            ];
        }

        return $results;
    }
}