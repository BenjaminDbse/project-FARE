<?php

namespace App\Entity;

use App\Repository\ImportContextRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=ImportContextRepository::class)
 * @UniqueEntity(
 *     "title",
 *     message="Ce titre existe déjà.")
 */
class ImportContext
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $title;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $datetime;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="importContexts")
     */
    private $author;

    /**
     * @Assert\File(
     *      mimeTypes = {
     *         "text/plain"
     *      })
     * @var File
     */
    private File $file;

    /**
     * @ORM\OneToMany(targetEntity=Context::class, mappedBy="import")
     */
    private $contexts;

    public function __construct()
    {
        $this->contexts = new ArrayCollection();
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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

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

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection|Context[]
     */
    public function getContexts(): Collection
    {
        return $this->contexts;
    }

    public function addContext(Context $context): self
    {
        if (!$this->contexts->contains($context)) {
            $this->contexts[] = $context;
            $context->setImport($this);
        }

        return $this;
    }

    public function removeContext(Context $context): self
    {
        if ($this->contexts->removeElement($context)) {
            // set the owning side to null (unless already changed)
            if ($context->getImport() === $this) {
                $context->setImport(null);
            }
        }

        return $this;
    }
}
