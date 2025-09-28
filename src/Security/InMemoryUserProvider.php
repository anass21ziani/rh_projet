<?php
namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class InMemoryUserProvider implements UserProviderInterface
{
    private array $users = [];

    public function __construct()
    {
        // Créer des utilisateurs de test en mémoire
        $admin = new User();
        $admin->setEmail('admin@test.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword('$2y$13$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); // password123
        
        $rh = new User();
        $rh->setEmail('rh@test.com');
        $rh->setRoles(['ROLE_RH']);
        $rh->setPassword('$2y$13$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); // password123
        
        $user = new User();
        $user->setEmail('user@test.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword('$2y$13$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); // password123

        $this->users = [
            'admin@test.com' => $admin,
            'rh@test.com' => $rh,
            'user@test.com' => $user,
        ];
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        if (!isset($this->users[$identifier])) {
            throw new UserNotFoundException(sprintf('Username "%s" does not exist.', $identifier));
        }

        return $this->users[$identifier];
    }
}




