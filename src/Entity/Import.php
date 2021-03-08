<?php

namespace App\Entity;

use App\Repository\ImportRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @ORM\Entity(repositoryClass=ImportRepository::class)
 */
class Import
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
    private string $title;

    private File $file;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTimeInterface $Datetime;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $author;

    /**
     * @ORM\OneToMany(targetEntity=Data::class, mappedBy="import")
     */
    private Collection $data;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slugify;

    public function __construct()
    {
        $this->data = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
    /**
     * @return File
     */
    public function getFile(): File
    {
        return $this->file;
    }

    /**
     * @param File $file
     */
    public function setFile(File $file): void
    {
        $this->file = $file;
    }

    public function getDatetime(): ?DateTimeInterface
    {
        return $this->Datetime;
    }

    public function setDatetime(?DateTimeInterface $Datetime): self
    {
        $this->Datetime = $Datetime;

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection|Data[]
     */
    public function getData(): Collection
    {
        return $this->data;
    }

    public function addData(Data $data): self
    {
        if (!$this->data->contains($data)) {
            $this->data[] = $data;
            $data->setImport($this);
        }

        return $this;
    }

    public function removeData(Data $data): self
    {
        if ($this->data->removeElement($data)) {
            // set the owning side to null (unless already changed)
            if ($data->getImport() === $this) {
                $data->setImport(null);
            }
        }

        return $this;
    }

    public function getSlugify(): ?string
    {
        return $this->slugify;
    }

    public function setSlugify(string $slugify): self
    {
        $this->slugify = $slugify;

        return $this;
    }
}
