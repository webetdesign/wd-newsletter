<?php

namespace WebEtDesign\NewsletterBundle\Attribute;

use JetBrains\PhpStorm\Pure;
use WebEtDesign\CmsBundle\CMS\Template\ComponentInterface;
use WebEtDesign\CmsBundle\Vars\CmsVarsBag;
use WebEtDesign\CmsBundle\CMS\Configuration\BlockDefinition;

abstract class AbstractModel implements ComponentInterface
{
    protected const CODE = '';

    protected ?string     $name     = null;
    protected ?string     $help     = null;
    protected ?string     $sender   = null;
    protected ?string     $email    = null;
    protected ?string     $template = null;
    protected ?string     $txt      = null;
    protected ?CmsVarsBag $varsBag  = null;

    #[Pure] public function __toString(): string
    {
        return $this->getName() ?? $this->getCode();
    }


    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): AbstractModel
    {
        $this->name = $name;
        return $this;
    }

    public function getHelp(): ?string
    {
        return $this->help;
    }

    public function setHelp(?string $help): AbstractModel
    {
        $this->help = $help;
        return $this;
    }

    public function getSender(): ?string
    {
        return $this->sender;
    }

    public function setSender(?string $sender): AbstractModel
    {
        $this->sender = $sender;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): AbstractModel
    {
        $this->email = $email;
        return $this;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(?string $template): AbstractModel
    {
        $this->template = $template;
        return $this;
    }

    public function getTxt(): ?string
    {
        return $this->txt;
    }

    public function setTxt(?string $txt): AbstractModel
    {
        $this->txt = $txt;
        return $this;
    }

    /**
     * @return BlockDefinition[]
     */
    public function getBlocks(): iterable
    {
        return [];
    }

    public function getBlock(string $code): ?BlockDefinition
    {
        foreach ($this->getBlocks() as $block) {
            if ($block->getCode() === $code) {
                return $block;
            }
        };
        return null;
    }

    public function getCode(): string
    {
        return self::CODE;
    }

    public function getCollections(): ?array
    {
        return null;
    }

    public function getLabel(): string
    {
        return $this->__toString();
    }

    public function setCode(?string $code): AbstractModel
    {
        return $this;
    }

    public function configureVars(CmsVarsBag $varsBag): void { }

    /**
     * @return CmsVarsBag|null
     */
    public function getVarsBag(): ?CmsVarsBag
    {
        return $this->varsBag;
    }

    /**
     * @param CmsVarsBag|null $varsBag
     * @return AbstractComponent
     */
    public function setVarsBag(?CmsVarsBag $varsBag): AbstractModel
    {
        $this->varsBag = $varsBag;
        return $this;
    }
}