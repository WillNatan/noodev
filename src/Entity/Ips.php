<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\IpsRepository")
 */
class Ips
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $addr;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Comment", inversedBy="ipAddr")
     * @ORM\JoinTable(name="ips_comment")
     */
    private $LikedComments;

    public function __construct()
    {
        $this->LikedComments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAddr(): ?string
    {
        return $this->addr;
    }

    public function setAddr(string $addr): self
    {
        $this->addr = $addr;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getLikedComments(): Collection
    {
        return $this->LikedComments;
    }

    public function addLikedComment(Comment $likedComment): self
    {
        if (!$this->LikedComments->contains($likedComment)) {
            $this->LikedComments[] = $likedComment;
        }

        return $this;
    }

    public function removeLikedComment(Comment $likedComment): self
    {
        if ($this->LikedComments->contains($likedComment)) {
            $this->LikedComments->removeElement($likedComment);
        }

        return $this;
    }
}
