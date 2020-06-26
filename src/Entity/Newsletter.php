<?php

namespace WebEtDesign\NewsletterBundle\Entity;

use App\Entity\Group;
use WebEtDesign\NewsletterBundle\Repository\NewsletterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity(repositoryClass=NewsletterRepository::class)
 * @ORM\Table(name="newsletter__newsletter")
 */
class Newsletter
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     */
    private $model;

    /**
     * @ORM\Column(type="text")
     */
    private $sender;

    /**
     * @ORM\Column(type="text")
     */
    private $email;

    /**
     * @ORM\OneToMany(targetEntity=Content::class, mappedBy="newsletter", orphanRemoval=true, cascade={"persist", "remove"})
     */
    private $contents;

    /**
     * @var string|null $emailsMore
     * @ORM\Column(type="text", nullable=true)
     */
    private $emailsMore;

    /**
     * @ORM\ManyToMany(targetEntity=App\Entity\Group::class)
     */
    private $groups;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isSent = false;

    public function __toString()
    {
        return $this->getTitle();
    }

    public function __construct()
    {
        $this->contents = new ArrayCollection();
        $this->groups = new ArrayCollection();
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

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getSender(): ?string
    {
        return $this->sender;
    }

    public function setSender(string $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection|Content[]
     */
    public function getContents(): Collection
    {
        return $this->contents;
    }

    public function addContent(Content $content): self
    {
        if (!$this->contents->contains($content)) {
            $this->contents[] = $content;
            $content->setNewsletter($this);
        }

        return $this;
    }

    public function removeContent(Content $content): self
    {
        if ($this->contents->contains($content)) {
            $this->contents->removeElement($content);
            // set the owning side to null (unless already changed)
            if ($content->getNewsletter() === $this) {
                $content->setNewsletter(null);
            }
        }

        return $this;
    }

    public function getContent($code){
        foreach ($this->contents as $content) {
            if ($content->getCode() === $code){
                return $content;
            }
        }

        return null;
    }


    /**
     * @return string|null
     */
    public function getEmailsMore(): ?string
    {
        return $this->emailsMore;
    }

    /**
     * @param string|null $emailsMore
     */
    public function setEmailsMore(?string $emailsMore): void
    {
        $this->emailsMore = $emailsMore;
    }

    public function getEmailsMoreArray(){

        $more = ['fr' => []];
        $cpt = 0;

        foreach (explode(',', str_replace(' ', '', $this->getEmailsMore())) as $email) {
           if ($email){
               $more['fr'][] = $email;
               $cpt++;
           }
        }

        return $cpt != 0 ? $more : [];
    }

    /**
     * @return ArrayCollection
     */
    public function getGroups()
    {
        return $this->groups;
    }


    public function addGroup(Group $group): self
    {
        if (!$this->groups->contains($group)) {
            $this->groups[] = $group;
        }

        return $this;
    }

    public function removeGroup(Group $group): self
    {
        if ($this->groups->contains($group)) {
            $this->groups->removeElement($group);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsSent()
    {
        return $this->isSent;
    }

    /**
     * @param mixed $isSent
     */
    public function setIsSent($isSent): void
    {
        $this->isSent = $isSent;
    }


}
