<?php

namespace App\Entity;

use App\Repository\EventsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EventsRepository::class)
 */
class Events
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $von;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $bis;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="smallint")
     */
    private $typ;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $link;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $bemerkungen;

    /**
     * @ORM\Column(type="smallint")
     */
    private $active;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVon(): ?\DateTimeInterface
    {
        return $this->von;
    }

    public function setVon(\DateTimeInterface $von): self
    {
        $this->von = $von;

        return $this;
    }

    public function getBis(): ?\DateTimeInterface
    {
        return $this->bis;
    }

    public function setBis(?\DateTimeInterface $bis): self
    {
        $this->bis = $bis;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getTyp(): ?int
    {
        return $this->typ;
    }

    public function setTyp(int $typ): self
    {
        $this->typ = $typ;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function getBemerkungen(): ?string
    {
        return $this->bemerkungen;
    }

    public function setBemerkungen(?string $bemerkungen): self
    {
        $this->bemerkungen = $bemerkungen;

        return $this;
    }

    public function getActive(): ?int
    {
        return $this->active;
    }

    public function setActive(int $active): self
    {
        $this->active = $active;

        return $this;
    }
}
