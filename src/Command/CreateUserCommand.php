<?php
namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateUserCommand extends Command
{
    protected static $defaultName = 'app:create-user';
    protected static $defaultDescription = 'Créer un utilisateur';
    private EntityManagerInterface $em;
    private UserPasswordHasherInterface $hasher;

    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $hasher)
    {
        parent::__construct();
        $this->em = $em;
        $this->hasher = $hasher;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Créer un utilisateur')
            ->addArgument('email', InputArgument::REQUIRED)
            ->addArgument('password', InputArgument::REQUIRED)
            ->addArgument('role', InputArgument::OPTIONAL, 'ROLE_ADMIN|ROLE_RH|ROLE_USER', 'ROLE_ADMIN');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        $plain = $input->getArgument('password');
        $role = $input->getArgument('role');

        $user = new User();
        $user->setEmail($email);
        $user->setRoles([$role]);
        $user->setPassword($this->hasher->hashPassword($user, $plain));

        $this->em->persist($user);
        $this->em->flush();

        $output->writeln("Utilisateur créé : $email ($role)");
        return Command::SUCCESS;
    }
}
