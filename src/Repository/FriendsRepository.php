<?php
/**
 * Friends repository.
 */
namespace Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Utils\Paginator;

/**
 * Class FriendsRepository.
 */
class FriendsRepository
{
    /**
     * Number of items per page.
     *
     * const int NUM_ITEMS
     */
    const NUM_ITEMS = 100;

    /**
     * Doctrine DBAL connection.
     *
     * @var \Doctrine\DBAL\Connection $db
     */
    protected $db;

    /**
     * PostsRepository constructor.
     *
     * @param \Doctrine\DBAL\Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }
    /**
     * Fetch all records.
     *
     * @return array Result
     */
    public function findAll()
    {
        $queryBuilder = $this->queryAll();

        return $queryBuilder->execute()->fetchAll();
    }

    public function friendsNames($userId)
    {

        $queryBuilder = $this->db->createQueryBuilder();

        $x = $queryBuilder->select(
            'y.PK_idUsers',
            'y.name',
            'y.surname'
        )
            ->from('users', 'y')
            ->innerJoin('y', 'friends', 'f', 'y.PK_idUsers = f.FK_idUserA')
            ->innerJoin('f', 'users', 'u', 'u.PK_idUsers = f.FK_idUserB')
            ->where('u.PK_idUsers = :userId')
            ->setParameters(array(':userId'=> $userId));

        return $x->execute()->fetchAll();

    }

    /**
     * @param $userId
     * @param $friendId
     * @throws DBALException
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function invite($userId, $friendId)
    {

        try {
            $relation = [];
            unset($relation['FK_idUsersA']);

            $this->db->beginTransaction();

            // add new record
            $relation['FK_idUserA'] = $userId;
            $relation['FK_idUserB'] = $friendId;

            $this->db->insert('invitations', $relation);

            $this->db->commit();

        } catch (DBALException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * @param $userId
     * @param $friendId
     * @throws DBALException
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function addFriend($userId, $friendId)
    {

        try {
            $relation = [];

            $this->db->beginTransaction();

            // add new record
            $relation['FK_idUserA'] = $userId;
            $relation['FK_idUserB'] = $friendId;

            $this->db->insert('friends', $relation);
            $this->db->commit();

            $this->db->beginTransaction();
            $queryBuilder = $this->db->createQueryBuilder();

            $queryBuilder -> delete('invitations')
                ->where('FK_idUserA = '.$userId)
                ->andWhere('FK_idUserB = '.$friendId)
                ->execute();
            $this->db->commit();

            $this->db->beginTransaction();

            $relation['FK_idUserB'] = $userId;
            $relation['FK_idUserA'] = $friendId;

            $this->db->insert('friends', $relation);
            $this->db->commit();

            $this->db->beginTransaction();
            $queryBuilder = $this->db->createQueryBuilder();

            $queryBuilder -> delete('invitations')
                ->where('FK_idUserB = '.$userId)
                ->andWhere('FK_idUserA = '.$friendId)
                ->execute();
            $this->db->commit();

        } catch (DBALException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * @param $userId
     * @param $friendId
     * @throws DBALException
     * @throws \Doctrine\DBAL\ConnectionException
     */


    public function delete($userId, $friendId)
    {
        $this->db->beginTransaction();

        try {
            $this->db->delete('friends', ['FK_idUserA' => $userId, 'FK_idUserB' => $friendId]);
            $this->db->delete('friends', ['FK_idUserA' => $friendId, 'FK_idUserB' => $userId]);
            $this->db->commit();
        } catch (DBALException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    /**
     * @param int $page
     * @return array
     */
    public function findAllPaginated($page = 1, $userId)
    {
        $countQueryBuilder = $this->findFriends($userId)
            ->select('COUNT(DISTINCT u.PK_idUsers) AS total_results')
            ->setMaxResults(1);

        $paginator = new Paginator($this->findFriends($userId), $countQueryBuilder);
        $paginator->setCurrentPage($page);
        $paginator->setMaxPerPage(100);

        return $paginator->getCurrentPageResults();
    }

    /**
     * @param int    $page
     * @param $userId
     * @return array
     */
    public function findAllInvitesPaginated($page = 1, $userId)
    {
        $countQueryBuilder = $this->findInvites($userId)
            ->select('COUNT(DISTINCT u.PK_idUsers) AS total_results')
            ->setMaxResults(1);

        $paginator = new Paginator($this->findInvites($userId), $countQueryBuilder);
        $paginator->setCurrentPage($page);
        $paginator->setMaxPerPage(100);

        return $paginator->getCurrentPageResults();
    }

    /**
     * @param $userId
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function getFriendsIds($userId)
    {
        $queryBuilder = $this->db->createQueryBuilder();

        return $queryBuilder->select(
            'y.PK_idUsers'
        )
            ->from('users', 'y')
            ->innerJoin('y', 'friends', 'f', 'y.PK_idUsers = f.FK_idUserA')
            ->innerJoin('f', 'users', 'u', 'u.PK_idUsers = f.FK_idUserB')
            ->where('u.PK_idUsers = '.$userId);

    }

    /**
     * @param $userId
     * @param $friendId
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function areFriends($userId, $friendId)
    {
        $queryBuilder = $this->db->createQueryBuilder();

        return $queryBuilder->select(
            'f.FK_idUserB'
        )
            ->from('friends', 'f')
            ->where('f.FK_idUserA = :userId')
            ->andWhere('f.FK_idUserB = :friendId')
            ->select('COUNT(DISTINCT u.PK_idUsers) AS total_results')
            ->setParameters(array(':userId'=> $userId, ':friendId' => $friendId));

    }
    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    protected function findFriends($userId)
    {
        $queryBuilder = $this->db->createQueryBuilder();

        return $queryBuilder->select(
            'y.PK_idUsers',
            'y.name',
            'y.surname',
            'y.photo',
            'y.role_id',
            'y.birthDate'
        )
            ->from('users', 'y')
            ->innerJoin('y', 'friends', 'f', 'y.PK_idUsers = f.FK_idUserA')
            ->innerJoin('f', 'users', 'u', 'u.PK_idUsers = f.FK_idUserB')
            ->where('u.PK_idUsers = :userId')
            ->setParameters(array(':userId'=> $userId));


    }

    /**
     * @param $userId
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    protected function findInvites($userId)
    {
        $queryBuilder = $this->db->createQueryBuilder();

        return $queryBuilder->select(
            'y.PK_idUsers',
            'y.name',
            'y.surname',
            'y.photo',
            'y.role_id',
            'y.birthDate'
        )
            ->from('users', 'y')
            ->innerJoin('y', 'invitations', 'i', 'y.PK_idUsers = i.FK_idUserA')
            ->innerJoin('i', 'users', 'u', 'u.PK_idUsers = i.FK_idUserB')
            ->where('i.FK_idUserB = :userId')
            ->setParameters(array(':userId'=> $userId));
    }
    /**
     * Query all records.
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder Result
     */
    protected function queryAll()
    {
        $queryBuilder = $this->db->createQueryBuilder();

        return $queryBuilder->select(
            'm.PK_time',
            'm.FK_idConversations',
            'm.FK_idUsers',
            'm.content'
        )->from('messages', 'm');
    }
}
