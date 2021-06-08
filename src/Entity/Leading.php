<?php

namespace App\Entity;

use App\Repository\LeadingRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LeadingRepository::class)
 * @ORM\Table(name="`leading`")
 */
class Leading
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
    private $ecs;

    /**
     * @ORM\Column(type="integer")
     */
    private $equipment;

    /**
     * @ORM\Column(type="integer")
     */
    private $module;

    /**
     * @ORM\Column(type="integer")
     */
    private $adr;

    /**
     * @ORM\Column(type="integer")
     */
    private $looping;

    /**
     * @ORM\Column(type="integer")
     */
    private $zone;

    /**
     * @ORM\OneToOne(targetEntity=Import::class, inversedBy="leadding", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $import;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEcs(): ?int
    {
        return $this->ecs;
    }

    public function setEcs(int $ecs): self
    {
        $this->ecs = $ecs;

        return $this;
    }

    public function getEquipment(): ?int
    {
        return $this->equipment;
    }

    public function setEquipment(int $equipment): self
    {
        $this->equipment = $equipment;

        return $this;
    }

    public function getModule(): ?int
    {
        return $this->module;
    }

    public function setModule(int $module): self
    {
        $this->module = $module;

        return $this;
    }

    public function getAdr(): ?int
    {
        return $this->adr;
    }

    public function setAdr(int $adr): self
    {
        $this->adr = $adr;

        return $this;
    }

    public function getLooping(): ?int
    {
        return $this->looping;
    }

    public function setLooping(int $looping): self
    {
        $this->looping = $looping;

        return $this;
    }

    public function getZone(): ?int
    {
        return $this->zone;
    }

    public function setZone(int $zone): self
    {
        $this->zone = $zone;

        return $this;
    }

    public function getImport(): ?Import
    {
        return $this->import;
    }

    public function setImport(Import $import): self
    {
        $this->import = $import;

        return $this;
    }
}
