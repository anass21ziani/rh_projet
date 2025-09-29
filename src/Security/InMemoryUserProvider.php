<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Security\User;

class InMemoryUserProvider implements UserProviderInterface
{
    private $users;
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
        $this->users = [];

        $this->addUser('admin@test.com', 'password123', ['ROLE_ADMIN']);
        $this->addUser('rh@test.com', 'password123', ['ROLE_RH']);
        $this->addUser('user@test.com', 'password123', ['ROLE_USER']);
    }

    private function addUser(string $email, string $plainPassword, array $roles): void
    {
        $user = new User();
        $user->setEmail($email);
        $user->setRoles($roles);
        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        $this->users[$email] = $user;
    }

    public function loadUserByUsername(string $username): UserInterface
    {
        return $this->loadUserByIdentifier($username);
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        if (!isset($this->users[$identifier])) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $identifier));
        }
        return $this->users[$identifier];
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class;
    }

    public function addNewUser(User $user): void
    {
        $this->users[$user->getEmail()] = $user;
    }
}