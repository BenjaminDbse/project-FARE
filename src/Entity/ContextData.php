<?php

namespace App\Entity;

use App\Repository\ContextDataRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ContextDataRepository::class)
 */
class ContextData
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity=Context::class, inversedBy="contextData")
     * @ORM\JoinColumn(nullable=false)
     */
    private Context $context;

    /**
     * @ORM\Column(type="float")
     */
    private float $ratio;

    /**
     * @ORM\Column(type="float")
     */
    private float $delta1;

    /**
     * @ORM\Column(type="float")
     */
    private float $pulse1;

    /**
     * @ORM\Column(type="float")
     */
    private float $delta2;

    /**
     * @ORM\Column(type="float")
     */
    private float $pulse2;

    /**
     * @ORM\Column(type="float")
     */
    private float $tempRaw;

    /**
     * @ORM\Column(type="float")
     */
    private float $tempCorrected;

    /**
     * @ORM\Column(type="float")
     */
    private float $co;

    public function getId(): int
    {
        return $this->id;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function setContext(Context $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function getRatio(): float
    {
        return $this->ratio;
    }

    public function setRatio(float $ratio): self
    {
        $this->ratio = $ratio;

        return $this;
    }

    public function getDelta1(): float
    {
        return $this->delta1;
    }

    public function setDelta1(float $delta1): self
    {
        $this->delta1 = $delta1;

        return $this;
    }

    public function getPulse1(): float
    {
        return $this->pulse1;
    }

    public function setPulse1(float $pulse1): self
    {
        $this->pulse1 = $pulse1;

        return $this;
    }

    public function getDelta2(): float
    {
        return $this->delta2;
    }

    public function setDelta2(float $delta2): self
    {
        $this->delta2 = $delta2;

        return $this;
    }

    public function getPulse2(): float
    {
        return $this->pulse2;
    }

    public function setPulse2(float $pulse2): self
    {
        $this->pulse2 = $pulse2;

        return $this;
    }

    public function getTempRaw(): float
    {
        return $this->tempRaw;
    }

    public function setTempRaw(float $tempRaw): self
    {
        $this->tempRaw = $tempRaw;

        return $this;
    }

    public function getTempCorrected(): float
    {
        return $this->tempCorrected;
    }

    public function setTempCorrected(float $tempCorrected): self
    {
        $this->tempCorrected = $tempCorrected;

        return $this;
    }

    public function getCo(): float
    {
        return $this->co;
    }

    public function setCo(float $co): self
    {
        $this->co = $co;

        return $this;
    }
}
