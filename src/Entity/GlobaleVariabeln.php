<?php

namespace App\Entity;

use App\Repository\GlobaleVariabelnRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=GlobaleVariabelnRepository::class)
 */
class GlobaleVariabeln
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
    private $k;

    /**
     * @ORM\Column(type="string", length=1000)
     */
    private $v;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getK(): ?string
    {
        return $this->k;
    }

    public function setK(string $k): self
    {
        $this->k = $k;

        return $this;
    }

    public function getV(): ?string
    {
        return $this->v;
    }

    public function setV(string $v): self
    {
        $this->v = $v;

        return $this;
    }
}
