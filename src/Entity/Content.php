<?php

namespace WebEtDesign\NewsletterBundle\Entity;

use App\Entity\Common\CallTranslations;
use App\Entity\Media;
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
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Newsletter::class, inversedBy="contents")
     * @ORM\JoinColumn(nullable=false)
     */
    private $newsletter;

    /**
     * @ORM\Column(type="text")
     */
    private $type;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $label;


    /**
     * @ORM\Column(type="text")
     */
    private $code;

    /**
     * @var string|null $help
     * @ORM\Column(type="text", nullable=true)
     */
    private $help;


    /**
     * @ORM\ManyToOne(targetEntity=Media::class)
     */
    private $media;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $canTranslate;


    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setMedia(?Media $media): self
    {
        $this->media = $media;

        return $this;
    }


    /**
     * @return string|null
     */
    public function getHelp(): ?string
    {
        return $this->help;
    }

    /**
     * @param string|null $help
     */
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

    /**
     * @return mixed
     */
    public function getCanTranslate()
    {
        return $this->canTranslate;
    }

    /**
     * @param mixed $canTranslate
     */
    public function setCanTranslate($canTranslate): self
    {
        $this->canTranslate = $canTranslate;

        return $this;
    }


}
