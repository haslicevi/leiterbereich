<?php

namespace App\Entity;

use App\Repository\MailPostausgangRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MailPostausgangRepository::class)
 */
class MailPostausgang
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
    private $timestamp;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $an;

    /**
     * @ORM\Column(type="string", length=1000)
     */
    private $betreff;

    /**
     * @ORM\Column(type="string", length=100000)
     */
    private $nachricht;

    /**
     * @ORM\Column(type="string", length=1000)
     */
    private $titel;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTimestamp(): ?string
    {
        return $this->timestamp;
    }

    public function setTimestamp(string $timestamp): self
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function getAn(): ?string
    {
        return $this->an;
    }

    public function setAn(string $an): self
    {
        $this->an = $an;

        return $this;
    }

    public function getBetreff(): ?string
    {
        return $this->betreff;
    }

    public function setBetreff(string $betreff): self
    {
        $this->betreff = $betreff;

        return $this;
    }

    public function getNachricht(): ?string
    {
        return $this->nachricht;
    }

    public function setNachricht(string $nachricht): self
    {
        $this->nachricht = $nachricht;

        return $this;
    }

    public function getTitel(): ?string
    {
        return $this->titel;
    }

    public function setTitel(string $titel): self
    {
        $this->titel = $titel;

        return $this;
    }
}
