<?php
namespace WebEtDesign\NewsletterBundle\Form\Transformer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;

class ContentCollectionTransformer implements DataTransformerInterface
{

    /**
     * @var EntityManagerInterface
     */
    private $em;
    private $class;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

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

    /**
     * @return mixed
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param mixed $class
     */
    public function setClass($class): void
    {
        $this->class = $class;
    }

}