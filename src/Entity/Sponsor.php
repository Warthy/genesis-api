<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SponsorRepository")
 * @HasLifecycleCallbacks
 */
class Sponsor
{

    const MEDIA_ROOT_DIR = 'sponsors/';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $reduction;

    /**
     * @var Media
     * @ORM\OneToOne(targetEntity="App\Entity\Media", cascade={"persist", "remove"})
     */
    private $media;

    /**
     * @var UploadedFile
     */
    public $uploadedFile;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @ORM\PreFlush()
     */
    public function upload(){
        $this->updatedAt = new DateTime('now');
        $media = new Media();

        $path = sha1(uniqid(mt_rand(), true)).'.'.$this->uploadedFile->guessExtension();
        $this->uploadedFile->move(Media::ASSETS_PATH.self::MEDIA_ROOT_DIR, $path);

        $media->setPath(self::MEDIA_ROOT_DIR.$path);
        $this->setMedia($media);

        unset($uploadedFile);
    }

    /**
     * @ORM\PreRemove()
     */
    public function removeFile(){
        if($this->media) $this->media->removeFile();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getReduction(): ?string
    {
        return $this->reduction;
    }

    public function setReduction(?string $reduction): self
    {
        $this->reduction = $reduction;

        return $this;
    }

    public function getMedia(): ?string
    {
        return $this->media->getPath();
    }

    public function setMedia(?Media $media): self
    {
        $this->media = $media;

        return $this;
    }
}
