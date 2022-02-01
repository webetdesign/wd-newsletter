<?php
namespace WebEtDesign\NewsletterBundle\Form\Transformer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;

class ContentCollectionTransformer implements DataTransformerInterface
{
    private ?string $class;

    public function __construct(private EntityManagerInterface $em) {}

    public function transform($value): string
    {
        $er = $this->em->getRepository($this->getClass());
        $data = [];
        foreach (explode(',', $value) as $id) {
            $object = $er->find($id);
            if ($object){
                $data[] = [
                    'text' => (string)$object,
                    'id' => $object->getId()
                ];
            }
        }
        return json_encode($data);
    }

    public function reverseTransform($value)
    {
        return $value;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function setClass($class): self
    {
        $this->class = $class;

        return $this;
    }

}