<?php

namespace App\Entity;

use App\Repository\ContextRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ContextRepository::class)
 */
class Context
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private int $number;
    /**
     * @ORM\Column(type="integer")
     */
    private int $algo;

    /**
     * @ORM\Column(type="integer")
     */
    private int $evalutionCase;

    /**
     * @ORM\Column(type="integer")
     */
    private int $halfContext;

    /**
     * @ORM\Column(type="integer")
     */
    private int $productIdentifier;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $datetime;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private float $velocimeter;

    /**
     * @ORM\Column(type="float")
     */
    private float $encrOne;

    /**
     * @ORM\Column(type="float")
     */
    private float $encrTwo;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private float $ratioAlarm;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private float $deltaSeuil;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private float $tempAlarm;

    /**
     * @ORM\Column(type="float")
     */
    private float $slopeSeuil;

    /**
     * @ORM\OneToMany(targetEntity=ContextData::class, mappedBy="context", cascade="all")
     */
    private $contextData;

    /**
     * @ORM\ManyToOne(targetEntity=Import::class, inversedBy="contexts")
     * @ORM\JoinColumn(nullable=false)
     */
    private Import $import;

    public function __construct()
    {
        $this->contextData = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getAlgo(): int
    {
        return $this->algo;
    }

    public function setAlgo(int $algo): self
    {
        $this->algo = $algo;

        return $this;
    }

    public function getEvalutionCase(): int
    {
        return $this->evalutionCase;
    }

    public function setEvalutionCase(int $evalutionCase): self
    {
        $this->evalutionCase = $evalutionCase;

        return $this;
    }

    public function getHalfContext(): int
    {
        return $this->halfContext;
    }

    public function setHalfContext(int $halfContext): self
    {
        $this->halfContext = $halfContext;

        return $this;
    }

    public function getProductIdentifier(): int
    {
        return $this->productIdentifier;
    }

    public function setProductIdentifier(int $productIdentifier): self
    {
        $this->productIdentifier = $productIdentifier;

        return $this;
    }

    public function getDatetime(): ?\DateTimeInterface
    {
        return $this->datetime;
    }

    public function setDatetime(?\DateTimeInterface $datetime): self
    {
        $this->datetime = $datetime;

        return $this;
    }

    public function getVelocimeter(): float
    {
        return $this->velocimeter;
    }

    public function setVelocimeter(float $velocimeter): self
    {
        $this->velocimeter = $velocimeter;

        return $this;
    }

    public function getEncrOne(): float
    {
        return $this->encrOne;
    }

    public function setEncrOne(float $encrOne): self
    {
        $this->encrOne = $encrOne;

        return $this;
    }

    public function getEncrTwo(): float
    {
        return $this->encrTwo;
    }

    public function setEncrTwo(float $encrTwo): self
    {
        $this->encrTwo = $encrTwo;

        return $this;
    }

    public function getRatioAlarm(): float
    {
        return $this->ratioAlarm;
    }

    public function setRatioAlarm(float $ratioAlarm): self
    {
        $this->ratioAlarm = $ratioAlarm;

        return $this;
    }

    public function getDeltaSeuil(): float
    {
        return $this->deltaSeuil;
    }

    public function setDeltaSeuil(float $deltaSeuil): self
    {
        $this->deltaSeuil = $deltaSeuil;

        return $this;
    }

    public function getTempAlarm(): float
    {
        return $this->tempAlarm;
    }

    public function setTempAlarm(float $tempAlarm): self
    {
        $this->tempAlarm = $tempAlarm;

        return $this;
    }

    public function getSlopeSeuil(): float
    {
        return $this->slopeSeuil;
    }

    public function setSlopeSeuil(float $slopeSeuil): self
    {
        $this->slopeSeuil = $slopeSeuil;

        return $this;
    }

    /**
     * @return Collection|ContextData[]
     */
    public function getContextData(): Collection
    {
        return $this->contextData;
    }

    public function addContextData(ContextData $contextData): self
    {
        if (!$this->contextData->contains($contextData)) {
            $this->contextData[] = $contextData;
            $contextData->setContext($this);
        }

        return $this;
    }

    public function removeContextData(ContextData $contextData): self
    {
        if ($this->contextData->removeElement($contextData)) {
            // set the owning side to null (unless already changed)
            if ($contextData->getContext() === $this) {
                $contextData->setContext(null);
            }
        }

        return $this;
    }

    public function getImport(): Import
    {
        return $this->import;
    }

    public function setImport(Import $import): self
    {
        $this->import = $import;

        return $this;
    }
}
