<?php

namespace App\Command;

use App\Entity\Question;
use App\Entity\Answer;
use App\Entity\Test;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class LoadQuestionsCommand extends Command
{
    protected static string $defaultName = 'app:load-questions';
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Loads questions and answers from a text file into the database')
            ->setHelp('This command allows you to load questions and answers from a text file into the database...')
            ->addArgument('filename', InputArgument::REQUIRED, 'The path to the file containing questions and answers');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $filename = $input->getArgument('filename');

        if (!file_exists($filename)) {
            $io->error("File $filename not found!");
            return Command::FAILURE;
        }

        $question = null;
        $fileContent = file_get_contents($filename);

        if (!$fileContent) {
            $io->error("File can't read!");
            return Command::FAILURE;
        }

        $lines = explode( PHP_EOL, $fileContent);
        $lines = array_map('trim', $lines);
        $lines = array_filter($lines, 'strlen');

        $rawQuestion = [];

        $test = new Test();
        $test->setTitle('New test from ' . date('d-m-Y H:i:s'));
        $this->entityManager->persist($test);

        foreach ($lines as $line) {
            if (str_contains($line, 'Правильный ответ:')) {
                $keyText = explode(':', $line)[1];
                $question = new Question();
                $question->setText($rawQuestion[0]);
                $question->setKey($this->parseCorrectAnswers($keyText));
                $question->setCreatedAt(new \DateTimeImmutable());
                $question->setTest($test);
                $this->entityManager->persist($question);

                foreach ($rawQuestion as $pos => $rawAnswer) {
                    if ($pos === 0) {
                        continue;
                    }

                    $answer = new Answer();
                    $answer->setQuestion($question);
                    $answer->setText($rawAnswer);
                    $answer->setBit($pos);
                    $this->entityManager->persist($answer);
                }

                $rawQuestion = [];
            } else {
                $rawQuestion[] = $line;
            }
        }

        $this->entityManager->flush();

        $io->success('Questions and answers have been successfully loaded!');

        return Command::SUCCESS;
    }

    function parseCorrectAnswers(string $correctAnswersText): int
    {
        $correct_answers = 0;

        $parts = explode(' ИЛИ ', $correctAnswersText);
        $parts = array_map('trim', $parts);

        foreach ($parts as $part) {
            if (is_numeric($part)) {
                $correct_answers |= (1 << ($part - 1));
            }
        }

        return $correct_answers;
    }
}