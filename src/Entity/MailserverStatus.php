<?php

namespace App\Entity;

use App\Repository\MailserverStatusRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MailserverStatusRepository::class)
 */
class MailserverStatus
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $datum;

    /**
     * @ORM\Column(type="string", length=100000)
     */
    private $content;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDatum(): ?string
    {
        return $this->datum;
    }

    public function setDatum(?string $datum): self
    {
        $this->datum = $datum;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }
}
