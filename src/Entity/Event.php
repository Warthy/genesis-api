<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EventRepository")
 * @HasLifecycleCallbacks
 */
class Event
{
    const MEDIA_ROOT_DIR = 'events/';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $location;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="time")
     */
    private $startsAt;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $endsAt;

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
        $this->updatedAt = new \DateTime('now');
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

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

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getStartsAt(): ?\DateTimeInterface
    {
        return $this->startsAt;
    }

    public function setStartsAt(\DateTimeInterface $startsAt): self
    {
        $this->startsAt = $startsAt;

        return $this;
    }

    public function getEndsAt(): ?\DateTimeInterface
    {
        return $this->endsAt;
    }

    public function setEndsAt(?\DateTimeInterface $endsAt): self
    {
        $this->endsAt = $endsAt;

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
