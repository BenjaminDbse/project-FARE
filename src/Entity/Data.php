<?php

namespace App\Entity;

use App\Repository\DataRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DataRepository::class)
 */
class Data
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     */
    private float $delta1;

    /**
     * @ORM\Column(type="float")
     */
    private float $delta2;

    /**
     * @ORM\Column(type="float")
     */
    private float $filterRatio;

    /**
     * @ORM\Column(type="float")
     */
    private float $temperatureCorrection;

    /**
     * @ORM\Column(type="float")
     */
    private float $slopeTemperatureCorrection;

    /**
     * @ORM\Column(type="float")
     */
    private float $rawCo;

    /**
     * @ORM\Column(type="float")
     */
    private float $coCorrection;

    /**
     * @ORM\Column(type="datetime")
     */
    private DateTimeInterface $datetime;

    /**
     * @ORM\Column(type="integer")
     */
    private int $adr;

    /**
     * @ORM\Column(type="integer")
     */
    private int $status;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $alarm;

    /**
     * @ORM\ManyToOne(targetEntity=Import::class, inversedBy="datas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $import;

    public function getId(): int
    {
        return $this->id;
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

    public function getDelta2(): float
    {
        return $this->delta2;
    }

    public function setDelta2(float $delta2): self
    {
        $this->delta2 = $delta2;

        return $this;
    }

    public function getFilterRatio(): float
    {
        return $this->filterRatio;
    }

    public function setFilterRatio(float $filterRatio): self
    {
        $this->filterRatio = $filterRatio;

        return $this;
    }

    public function getTemperatureCorrection(): float
    {
        return $this->temperatureCorrection;
    }

    public function setTemperatureCorrection(float $temperatureCorrection): self
    {
        $this->temperatureCorrection = $temperatureCorrection;

        return $this;
    }

    public function getSlopeTemperatureCorrection(): float
    {
        return $this->slopeTemperatureCorrection;
    }

    public function setSlopeTemperatureCorrection(float $slopeTemperatureCorrection): self
    {
        $this->slopeTemperatureCorrection = $slopeTemperatureCorrection;

        return $this;
    }

    public function getRawCo(): float
    {
        return $this->rawCo;
    }

    public function setRawCo(float $rawCo): self
    {
        $this->rawCo = $rawCo;

        return $this;
    }

    public function getCoCorrection(): float
    {
        return $this->coCorrection;
    }

    public function setCoCorrection(float $coCorrection): self
    {
        $this->coCorrection = $coCorrection;

        return $this;
    }

    public function getDatetime(): ?DateTimeInterface
    {
        return $this->datetime;
    }

    public function setDatetime(DateTimeInterface $datetime): self
    {
        $this->datetime = $datetime;

        return $this;
    }

    public function getAdr(): int
    {
        return $this->adr;
    }

    public function setAdr(int $adr): self
    {
        $this->adr = $adr;

        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getAlarm(): ?int
    {
        return $this->alarm;
    }

    public function setAlarm(?int $alarm): self
    {
        $this->alarm = $alarm;

        return $this;
    }

    public function getImport(): ?Import
    {
        return $this->import;
    }

    public function setImport(?Import $import): self
    {
        $this->import = $import;

        return $this;
    }
}
