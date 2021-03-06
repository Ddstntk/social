<?php
/**
 * PHP Version 5.6
 * User provider.
 *
 * @category  Social_Network
 *
 * @author    Konrad Szewczuk <konrad3szewczuk@gmail.com>
 *
 * @copyright 2018 Konrad Szewczuk
 *
 * @license   https://opensource.org/licenses/MIT MIT license
 *
 * @link      cis.wzks.uj.edu.pl/~16_szewczuk
 */
namespace Provider;

use Doctrine\DBAL\Connection;
use Repository\UserRepository;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

//use CustomUser;

/**
 * Class UserProvider
 *
 * @category  Social_Network
 *
 * @author    Konrad Szewczuk <konrad3szewczuk@gmail.com>
 *
 * @copyright 2018 Konrad Szewczuk
 *
 * @license   https://opensource.org/licenses/MIT MIT license
 *
 * @link      cis.wzks.uj.edu.pl/~16_szewczuk
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
     * @param \Doctrine\DBAL\Connection $db Database
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Load user by username
     *
     * @param string $email Email
     *
     * @return CustomUser|UserInterface Userinterface
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
     * Refresh user
     *
     * @param UserInterface $user User instance
     *
     * @return CustomUser|UserInterface Userinterface
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
