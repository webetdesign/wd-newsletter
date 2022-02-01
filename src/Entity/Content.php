<?php

namespace WebEtDesign\NewsletterBundle\Entity;

use WebEtDesign\MediaBundle\Entity\Media;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;
use Symfony\Component\PropertyAccess\PropertyAccess;
use WebEtDesign\NewsletterBundle\Repository\ContentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ContentRepository::class)
 * @ORM\Table(name="newsletter__content")
 */
class Content implements TranslatableInterface
{
    use TranslatableTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\ManyToOne(targetEntity=Newsletter::class, inversedBy="contents")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Newsletter $newsletter;

    /**
     * @ORM\Column(type="text")
     */
    private ?string $type = '';

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $label = '';

    /**
     * @ORM\Column(type="text")
     */
    private ?string $code = '';

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $help;

    /**
     * @ORM\ManyToOne(targetEntity=Media::class)
     */
    private ?Media $media;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $canTranslate;

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setMedia(?Media $media): self
    {
        $this->media = $media;

        return $this;
    }

    public function getHelp(): ?string
    {
        return $this->help;
    }

    public function setHelp(?string $help): self
    {
        $this->help = $help;

        return $this;
    }

    public function __call($method, $arguments)
    {
        if ($method == '_action') {
            return null;
        }

        return PropertyAccess::createPropertyAccessor()->getValue($this->translate(), $method);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNewsletter(): ?Newsletter
    {
        return $this->newsletter;
    }

    public function setNewsletter(?Newsletter $newsletter): self
    {
        $this->newsletter = $newsletter;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getCanTranslate(): ?string
    {
        return $this->canTranslate;
    }

    public function setCanTranslate(?string $canTranslate): self
    {
        $this->canTranslate = $canTranslate;

        return $this;
    }


}
