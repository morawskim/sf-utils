<?php

namespace mmo\sf\tests\Form\DataTransformer;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\Persistence\ObjectRepository;
use mmo\sf\Form\DataTransformer\PrimaryKeyToEntityTransformer;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Form\Exception\TransformationFailedException;

class PrimaryKeyToEntityTransformerTest extends TestCase
{
    public function testEntityToId(): void
    {
        $entityName = 'Person';
        $object = new stdClass();
        $object->id = 345;

        $metadataMock = $this->createMock(ClassMetadata::class);
        $metadataMock->expects(self::once())
            ->method('getSingleIdentifierFieldName')
            ->willReturn('id');
        $metadataMock->expects(self::once())
            ->method('getIdentifierValues')
            ->with($object)
            ->willReturnCallback(function ($entity) {
                return ['id' => $entity->id];
            });

        $mock = $this->createMock(EntityManagerInterface::class);
        $mock->method('getClassMetadata')
            ->with($entityName)
            ->willReturn($metadataMock);

        $sut = new PrimaryKeyToEntityTransformer($mock, $entityName);
        $this->assertSame($object->id, $sut->transform($object));
    }

    public function testTransformIfNull(): void
    {
        $entityName = 'Person';
        $mock = $this->createStub(EntityManagerInterface::class);


        $sut = new PrimaryKeyToEntityTransformer($mock, $entityName);
        $this->assertSame('', $sut->transform(null));
    }
//
    public function testExceptionIfCompoundPrimaryKey(): void
    {
        $entityName = 'Person';
        $object = new stdClass();
        $object->id = 345;

        $metadataMock = $this->createPartialMock(ClassMetadata::class, ['getIdentifierValues']);
        $metadataMock->setIdentifier(['foo', 'bar']);

        $metadataMock->expects(self::never())
            ->method('getIdentifierValues')
            ->with($object)
            ->willReturnCallback(function ($entity) {
                return ['id' => $entity->id];
            });

        $mock = $this->createMock(EntityManagerInterface::class);
        $mock->method('getClassMetadata')
            ->with($entityName)
            ->willReturn($metadataMock);

        $this->expectException(MappingException::class);
        $sut = new PrimaryKeyToEntityTransformer($mock, $entityName);
        $sut->transform($object);
    }

    public function testReverseTransformEntityExists(): void
    {
        $entityName = 'Person';
        $object = new stdClass();
        $object->id = 123;

        $repositoryMock = $this->createMock(ObjectRepository::class);
        $repositoryMock->expects(self::once())
            ->method('findOneBy')
            ->with(['id' => $object->id])
            ->willReturn($object);

        $mock = $this->createMock(EntityManagerInterface::class);
        $mock->expects(self::once())
            ->method('getRepository')
            ->willReturn($repositoryMock);

        $sut = new PrimaryKeyToEntityTransformer($mock, $entityName);
        $this->assertSame($object, $sut->reverseTransform($object->id));
    }

    public function testThrowExceptionIfEntityNotExists(): void
    {
        $entityName = 'Person';
        $object = new stdClass();
        $object->id = 123;

        $repositoryMock = $this->createMock(ObjectRepository::class);
        $repositoryMock->expects(self::once())
            ->method('findOneBy')
            ->with(['id' => $object->id])
            ->willReturn(null);

        $mock = $this->createMock(EntityManagerInterface::class);
        $mock->expects(self::once())
            ->method('getRepository')
            ->willReturn($repositoryMock);

        $this->expectException(TransformationFailedException::class);
        $sut = new PrimaryKeyToEntityTransformer($mock, $entityName);
        $sut->reverseTransform($object->id);
    }
}
