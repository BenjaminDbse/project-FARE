<?php

namespace App\Entity;

use App\Repository\ImportRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=ImportRepository::class)
 * @UniqueEntity(
 *     "title",
 *     message="Ce titre existe déjà.")
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
     * @Assert\NotBlank
     */
    private string $title;

    /**
     * @Assert\File(
     *      mimeTypes = {
     *         "text/plain"
     *      })
     * @var File
     */
    private File $file;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTimeInterface $Datetime;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="imports")
     * @ORM\JoinColumn(nullable=false)
     */
    private User $author;

    /**
     * @ORM\OneToMany(targetEntity=Data::class, mappedBy="import", orphanRemoval=true)
     */
    private $datas;

    /**
     * @ORM\OneToMany(targetEntity=Context::class, mappedBy="import", orphanRemoval=true)
     */
    private $contexts;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="imports")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

    public function __construct()
    {
        $this->datas = new ArrayCollection();
        $this->contexts = new ArrayCollection();
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

    public function getAuthor(): User
    {
        return $this->author;
    }

    public function setAuthor(User $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection|Data[]
     */
    public function getDatas(): Collection
    {
        return $this->datas;
    }

    public function addData(Data $data): self
    {
        if (!$this->datas->contains($data)) {
            $this->datas[] = $data;
            $data->setImport($this);
        }

        return $this;
    }

    public function removeData(Data $data): self
    {
        if ($this->datas->removeElement($data)) {
            // set the owning side to null (unless already changed)
            if ($data->getImport() === $this) {
                $data->setImport(null);
            }
        }

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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }
}
