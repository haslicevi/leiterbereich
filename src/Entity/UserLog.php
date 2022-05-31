<?php

namespace App\Entity;

use App\Repository\UserLogRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserLogRepository::class)
 */
class UserLog
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
    private $csrf;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $path;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $controller;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $datetime;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCsrf(): ?string
    {
        return $this->csrf;
    }

    public function setCsrf(string $csrf): self
    {
        $this->csrf = $csrf;

        return $this;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function setUser(string $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getController(): ?string
    {
        return $this->controller;
    }

    public function setController(string $controller): self
    {
        $this->controller = $controller;

        return $this;
    }

    public function getDatetime(): ?string
    {
        return $this->datetime;
    }

    public function setDatetime(string $datetime): self
    {
        $this->datetime = $datetime;

        return $this;
    }
}
