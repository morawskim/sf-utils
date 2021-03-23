<?php

namespace mmo\sf\Security\Test;

use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;

/**
 * FakePasswordEncoder does not do any encoding but is useful in testing environments.
 *
 * The main difference to PlaintextPasswordEncoder is prefix a password with a string.
 * So in tests, we know whether sut encode a password or not.
 *
 * As this encoder is not cryptographically secure, usage of it in production environments is discouraged.
 *
 * @see PlaintextPasswordEncoder
 */
class FakePasswordEncoder extends PlaintextPasswordEncoder
{
    /**
     * @var string
     */
    private $passwordPrefix;

    public function __construct(string $passwordPrefix, bool $ignorePasswordCase = false)
    {
        parent::__construct($ignorePasswordCase);

        $this->passwordPrefix = $passwordPrefix;
    }

    protected function mergePasswordAndSalt($password, $salt): string
    {
        return $this->passwordPrefix . parent::mergePasswordAndSalt($password, $salt);
    }
}
