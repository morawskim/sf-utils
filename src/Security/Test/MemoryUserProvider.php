<?php

namespace mmo\sf\Security\Test;

use LogicException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\InMemoryUserProvider;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * MemoryUserProvider is a simple non persistent user provider for tests.
 *
 * This provider compares to InMemoryUserProvider allows for store any user objects,
 * which implement the UserInterface interface instead of only the internal Symfony User class.
 *
 * @see InMemoryUserProvider
 */
class MemoryUserProvider implements UserProviderInterface
{
    /**
     * @var UserInterface[]
     */
    private $users;

    /**
     * @var string
     */
    private $supportedUserClass;

    /**
     * @param string $supportedUserClass
     * @param UserInterface[] $users
     */
    public function __construct(string $supportedUserClass, array $users = [])
    {
        $this->supportedUserClass = $supportedUserClass;

        foreach ($users as $user) {
            $this->createUser($user);
        }
    }

    /**
     * Adds a new User to the provider.
     *
     * @throws LogicException
     */
    public function createUser(UserInterface $user)
    {
        if (isset($this->users[strtolower($user->getUsername())])) {
            throw new LogicException('Another user with the same username already exists.');
        }

        $this->users[strtolower($user->getUsername())] = $user;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        return $this->getUser($username);
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        return $this->getUser($user->getUsername());
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $this->supportedUserClass === $class;
    }

    /**
     * Returns the user by given username.
     *
     * @param string $username The username
     *
     * @return UserInterface
     *
     * @throws UsernameNotFoundException if user whose given username does not exist
     */
    private function getUser($username)
    {
        if (!isset($this->users[strtolower($username)])) {
            $ex = new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
            $ex->setUsername($username);

            throw $ex;
        }

        return $this->users[strtolower($username)];
    }
}
