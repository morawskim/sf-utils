<?php

namespace mmo\sf\tests\ImagineBundle;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use mmo\sf\ImagineBundle\ResolverAlwaysStoredDecorator;
use PHPUnit\Framework\TestCase;

class ResolverAlwaysStoredDecoratorTest extends TestCase
{
    public function testStoreIsAlwaysTrue(): void
    {
        $mock = $this->createMock(ResolverInterface::class);
        $mock->expects(self::never())
            ->method('isStored');

        $sut = new ResolverAlwaysStoredDecorator($mock);

        $this->assertTrue($sut->isStored('foo', 'bar'));
    }

    public function testDelegateMethodsToPassedResolver(): void
    {
        $path = 'foo';
        $filter = 'bar';

        $binaryInterfaceStub = $this->createStub(BinaryInterface::class);

        $mock = $this->createMock(ResolverInterface::class);
        $mock->expects(self::once())
            ->method('remove')
            ->with([$path], [$filter]);

        $mock->expects(self::once())
            ->method('store')
            ->with(self::identicalTo($binaryInterfaceStub), $path, $filter);

        $mock->expects(self::once())
            ->method('resolve')
            ->with($path, $filter);

        $sut = new ResolverAlwaysStoredDecorator($mock);
        $sut->resolve($path, $filter);
        $sut->remove([$path], [$filter]);
        $sut->store($binaryInterfaceStub, $path, $filter);
    }
}
