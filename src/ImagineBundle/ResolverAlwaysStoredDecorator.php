<?php

namespace mmo\sf\ImagineBundle;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;

/**
 * This resolver always returns true for the method isStored.
 *
 * Other methods are delegated to a passed ResolverInterface.
 */
class ResolverAlwaysStoredDecorator implements ResolverInterface
{
    /**
     * @var ResolverInterface Decorated ResolverInterface
     */
    private $resolver;

    public function __construct(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function isStored($path, $filter)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($path, $filter)
    {
        return $this->resolver->resolve($path, $filter);
    }

    /**
     * {@inheritdoc}
     */
    public function store(BinaryInterface $binary, $path, $filter)
    {
        return $this->resolver->store($binary, $path, $filter);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(array $paths, array $filters)
    {
        return $this->resolver->remove($paths, $filters);
    }
}
