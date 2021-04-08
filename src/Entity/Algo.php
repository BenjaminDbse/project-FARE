<?php

namespace App\Entity;

use App\Repository\AlgoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AlgoRepository::class)
 */
class Algo
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="float")
     */
    private $coef1ratio;

    /**
     * @ORM\Column(type="float")
     */
    private $coef1tmp;

    /**
     * @ORM\Column(type="float")
     */
    private $coef2tmp;

    /**
     * @ORM\Column(type="float")
     */
    private $coef2ratio;

    /**
     * @ORM\Column(type="float")
     */
    private $xtmp;

    /**
     * @ORM\Column(type="float")
     */
    private $xratio;

    /**
     * @ORM\Column(type="float")
     */
    private $ytmp;

    /**
     * @ORM\Column(type="float")
     */
    private $yratio;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCoef1ratio(): ?float
    {
        return $this->coef1ratio;
    }

    public function setCoef1ratio(float $coef1ratio): self
    {
        $this->coef1ratio = $coef1ratio;

        return $this;
    }

    public function getCoef1tmp(): ?float
    {
        return $this->coef1tmp;
    }

    public function setCoef1tmp(float $coef1tmp): self
    {
        $this->coef1tmp = $coef1tmp;

        return $this;
    }

    public function getCoef2tmp(): ?float
    {
        return $this->coef2tmp;
    }

    public function setCoef2tmp(float $coef2tmp): self
    {
        $this->coef2tmp = $coef2tmp;

        return $this;
    }

    public function getCoef2ratio(): ?float
    {
        return $this->coef2ratio;
    }

    public function setCoef2ratio(float $coef2ratio): self
    {
        $this->coef2ratio = $coef2ratio;

        return $this;
    }

    public function getXtmp(): ?float
    {
        return $this->xtmp;
    }

    public function setXtmp(float $xtmp): self
    {
        $this->xtmp = $xtmp;

        return $this;
    }

    public function getXratio(): ?float
    {
        return $this->xratio;
    }

    public function setXratio(float $xratio): self
    {
        $this->xratio = $xratio;

        return $this;
    }

    public function getYtmp(): ?float
    {
        return $this->ytmp;
    }

    public function setYtmp(float $ytmp): self
    {
        $this->ytmp = $ytmp;

        return $this;
    }

    public function getYratio(): ?float
    {
        return $this->yratio;
    }

    public function setYratio(float $yratio): self
    {
        $this->yratio = $yratio;

        return $this;
    }
}
