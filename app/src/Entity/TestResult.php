<?php

namespace App\Entity;

use App\Repository\TestResultRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TestResultRepository::class)]
class TestResult
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'testResults')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user_id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    /**
     * @var Collection<int, TestAnswer>
     */
    #[ORM\OneToMany(targetEntity: TestAnswer::class, mappedBy: 'test_result')]
    private Collection $testAnswers;

    public function __construct()
    {
        $this->testAnswers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?User
    {
        return $this->user_id;
    }

    public function setUserId(?User $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * @return Collection<int, TestAnswer>
     */
    public function getTestAnswers(): Collection
    {
        return $this->testAnswers;
    }

    public function addTestAnswer(TestAnswer $testAnswer): static
    {
        if (!$this->testAnswers->contains($testAnswer)) {
            $this->testAnswers->add($testAnswer);
            $testAnswer->setTestResult($this);
        }

        return $this;
    }

    public function removeTestAnswer(TestAnswer $testAnswer): static
    {
        if ($this->testAnswers->removeElement($testAnswer)) {
            // set the owning side to null (unless already changed)
            if ($testAnswer->getTestResult() === $this) {
                $testAnswer->setTestResult(null);
            }
        }

        return $this;
    }
}
