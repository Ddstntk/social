<?php
namespace Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Utils\Paginator;class PhotosRepository
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
     * Save record.
     *
     * @param array $photo Photo
     *
     * @return boolean Result
     */
    public function save($photo, $userId)
    {
//        if (isset($photo['id']) && ctype_digit((string) $photo['id'])) {
            // update record
//            $id = $photo['id'];
            unset($photo['id']);
            var_dump($photo);
            return $this->db->update('users', $photo, ['PK_idUsers' => $userId]);
//        } else {
//            // add new record
//            return $this->db->insert('users', $photo);
//        }
    }
// ...
}