<?php

namespace App\Entity;

use App\Repository\AnwesenheitenRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AnwesenheitenRepository::class)
 */
class Anwesenheiten
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
    private $mitglied;

    /**
     * @ORM\Column(type="integer")
     */
    private $event;

    /**
     * @ORM\Column(type="smallint")
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $text;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMitglied(): ?int
    {
        return $this->mitglied;
    }

    public function setMitglied(int $mitglied): self
    {
        $this->mitglied = $mitglied;

        return $this;
    }

    public function getEvent(): ?int
    {
        return $this->event;
    }

    public function setEvent(int $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): self
    {
        $this->text = $text;

        return $this;
    }
}
