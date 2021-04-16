<?php

namespace mmo\sf\Form\DataTransformer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class PrimaryKeyToEntityTransformer implements DataTransformerInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var string */
    private $entityName;

    /** @var string */
    private $identifier;

    public function __construct(EntityManagerInterface $entityManager, string $entityName, string $identifier = 'id')
    {
        $this->entityManager = $entityManager;
        $this->entityName = $entityName;
        $this->identifier = $identifier;
    }

    /**
     * @inheritDoc
     */
    public function transform($object)
    {
        if (null === $object) {
            return '';
        }

        $classMetadata = $this->entityManager->getClassMetadata($this->entityName);
        $idField = $classMetadata->getSingleIdentifierFieldName();

        return $classMetadata->getIdentifierValues($object)[$idField] ?? '';
    }

    /**
     * @inheritDoc
     */
    public function reverseTransform($id)
    {
        if (!is_numeric($id)) {
            return null;
        }

        $object = $this->entityManager
            ->getRepository($this->entityName)
            ->findOneBy([$this->identifier => $id]);

        if (null === $object) {
            throw new TransformationFailedException(sprintf('An object with identifier key "%s" and value "%s" does not exist!', $this->identifier, $id));
        }

        return $object;
    }
}
