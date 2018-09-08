<?php
/**
 * User repository
 */

namespace Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Utils\Paginator;
use Symfony\Component\Validator\Constraints\DateTime;
//use Repository\FriendsRepository;
/**
 * Class UserRepository.
 */
class UserRepository
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

    public function getIdByEmail($email)
    {
        $queryBuilder = $this->db->createQueryBuilder();
        $id = $queryBuilder ->select('u.PK_idUsers')
            ->from('users', 'u')
            ->where('u.email = :email')
            ->setParameter(':email', $email)
            ->execute()->fetch();

        return $id;
    }
    /**
     * Gets user data by login.
     *
     * @param  string $id User id
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return array Result
     */
    public function getUserById($id)
    {
        $queryBuilder = $this->queryAll();
        $queryBuilder->where('u.PK_idUsers = :id')
            ->setParameter(':id', $id, \PDO::PARAM_INT);
        $result = $queryBuilder->execute()->fetch();

        return !$result ? [] : $result;
    }

    //    public function getUserByEmail($email)
    //    {
    //        $queryBuilder = $this->queryAll();
    //        $queryBuilder->where('u.email = :email')
    //            ->setParameter(':email', $email, \PDO::PARAM_INT);
    //        $result = $queryBuilder->execute()->fetch();
    //
    //        return !$result ? [] : $result;
    //    }
    /**
     * Loads user by login.
     *
     * @param  string $login User login
     * @throws UsernameNotFoundException
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return array Result
     */
    public function loadUserByEmail($email)
    {
        try {
            $user = $this->getUserByEmail($email);

            if (!$user || !count($user)) {
                throw new UsernameNotFoundException(
                    sprintf('Username "%s" does not exist.', $email)
                );
            }

            $roles = $this->getUserRoles($user['PK_idUsers']);

            if (!$roles || !count($roles)) {
                throw new UsernameNotFoundException(
                    sprintf('Username "%s" does not exist.', $email)
                );
            }

            return [
                'id' => $user['PK_idUsers'],
                'email' => $user['email'],
                'password' => $user['password'],
                'roles' => $roles,
            ];
        } catch (DBALException $exception) {
            throw new UsernameNotFoundException(
                sprintf('Username "%s" does not exist.', $email)
            );
        } catch (UsernameNotFoundException $exception) {
            throw $exception;
        }
    }


    /**
     * Gets user data by login.
     *
     * @param  string $login User login
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return array Result
     */
    public function getUserByEmail($email)
    {
        try {
            $queryBuilder = $this->db->createQueryBuilder();
            $queryBuilder->select('u.PK_idUsers', 'u.email', 'u.password')
                ->from('users', 'u')
                ->where('u.email = :email')
                ->setParameter(':email', $email, \PDO::PARAM_STR);

            return $queryBuilder->execute()->fetch();
        } catch (DBALException $exception) {
            return [];
        }
    }


    /**
     * Gets user roles by User ID.
     *
     * @param  integer $userId User ID
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return array Result
     */
    public function getUserRoles($id)
    {
        $roles = [];

        try {
            $queryBuilder = $this->db->createQueryBuilder();
            $queryBuilder->select('r.name')
                ->from('users', 'u')
                ->innerJoin('u', 'roles', 'r', 'u.role_id = r.id')
                ->where('u.PK_idUsers = :id')
                ->setParameter(':id', $id, \PDO::PARAM_INT);
            $result = $queryBuilder->execute()->fetchAll();

            if ($result) {
                $roles = array_column($result, 'name');
            }

            return $roles;
        } catch (DBALException $exception) {
            return $roles;
        }
    }

    /**
     * Save record.
     *
     * @param array $post Post
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function save($user)
    {
        $this->db->beginTransaction();

        try{
            if (isset($user['PK_idUsers']) && ctype_digit((string) $user['PK_idUsers'])) {
                // update record
                $userId = $user['PK_idUsers'];
                unset($user['PK_idUsers']);
                $this->db->update('users', $user, ['PK_idUsers' => $userId]);
            } else {
                // add new user
                $user['birthDate'] = $user['birthDate'] ->format('Y-m-d');
                $this->db->insert('users', $user);
                $this->db->commit();
            }
        } catch (DBALException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * @param int               $page
     * @param $friendsRepository
     * @param $userId
     * @return array
     */
    public function findAllPaginated($page = 1, $friendsRepository, $userId)
    {
        $countQueryBuilder = $this->findStrangers($friendsRepository, $userId)
            ->select('COUNT(DISTINCT k.PK_idUsers) AS total_results')
            ->setMaxResults(1);


        $paginator = new Paginator($this->findStrangers($friendsRepository, $userId), $countQueryBuilder);
        $paginator->setCurrentPage($page);
        $paginator->setMaxPerPage(100);

        return $paginator->getCurrentPageResults();
    }

    /**
     * @param $friendsRepository
     * @param $userId
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    protected function findStrangers($friendsRepository, $userId)
    {

        $queryBuilder = $this->db->createQueryBuilder();
        $friends = $friendsRepository->getFriendsIds($userId)->execute();

        return $queryBuilder->select(
            'k.PK_idUsers',
            'k.name',
            'k.surname',
            'k.photo',
            'k.role_id',
            'k.birthDate'
        )
            ->from('users', 'k')
            ->where($queryBuilder -> expr()->notIn('k.PK_idUsers', $friends))
            ->andWhere('k.PK_idUsers <> :userId')
            ->setParameters(array(':userId' => $userId, ':friendId' => 1));

    }


    protected function queryAll()
    {
        $queryBuilder = $this->db->createQueryBuilder();

        return $queryBuilder->select(
            'u.PK_idUsers',
            'u.name',
            'u.surname',
            'u.email',
            'u.photo',
            'u.role_id',
            'u.birthDate'
        )
            ->from('users', 'u');
    }

}