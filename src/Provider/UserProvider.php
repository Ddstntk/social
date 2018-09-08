<?php
/**
 * User provider.
 */
namespace Provider;


use Doctrine\DBAL\Connection;
use Repository\UserRepository;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
//use CustomUser;

/**
 * Class UserProvider.
 */
class UserProvider implements UserProviderInterface
{
    /**
     * Doctrine DBAL connection.
     *
     * @var \Doctrine\DBAL\Connection $db
     */
    protected $db;

    /**
     * TagRepository constructor.
     *
     * @param \Doctrine\DBAL\Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Load user by username.
     *
     * @param string $login User login
     *
     * @return CustomUser Result
     */
    public function loadUserByUsername($email)
    {
        $userRepository = new UserRepository($this->db);
        $user = $userRepository->loadUserByEmail($email);
        return new CustomUser(
            $user['id'],
            $user['email'],
            $user['password'],
            $user['roles'],
            true,
            true,
            true
        );
    }

    /**
     * Refresh user.
     *
     * @param UserInterface $user User
     *
     * @return CustomUser Result
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof CustomUser) {
            throw new UnsupportedUserException(
                sprintf(
                    'Instances of "%s" are not supported.',
                    get_class($user)
                )
            );
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * Check if supports selected class.
     *
     * @param string $class Class name
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return $class === '\app\src\Provider\User';
    }
}