<?php

namespace App\Service;

use App\Exception\PostgresQueryException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Undocumented class
 * PHP Version 8.2.0.
 *
 * @category  Groups a series of packages together.
 * @package   Categorizes the associated element into a logical grouping or subdivision.
 *
 * @author    Mason Pike <masonapike@gmail.com>
 * @license   unlicense https://unlicense.org/
 *
 * @see       http://url.com
 */
class LickManager
{
    private string $uuid;
    private \PgSql\Connection $con;

    public function __construct(\PgSql\Connection $connection, Request $request = null)
    {
        if ($request !== null) {
            $this->uuid = json_decode($request->getContent())->{'uuid'};
        }

        $this->con = $connection;
    }

    public function getLick(): array
    {
        $results = pg_query_params(
            $this->con,
            "SELECT * FROM licks WHERE uuid = $1",
            array($this->uuid,)
        ) or throw new PostgresQueryException($this->con);
        return pg_fetch_all($results);
    }

    public function exists()
    {
        return !empty($this->getLick());
    }

    public function getAllLicks(): array
    {
        $selectQuery = 'SELECT * FROM licks ORDER BY index';
        $results = pg_query_params(
            $this->con,
            $selectQuery,
            array()
        ) or throw new PostgresQueryException($this->con);
        return pg_fetch_all($results);
    }

    public function delete(): void
    {
        $this->removeReferenceFromParents();
        $this->deleteGivenLick();
        //$this->removeReferenceFromChildren();
    }

    private function deleteGivenLick()
    {
        $deleteQuery = 'DELETE FROM licks WHERE uuid = $1;';
        pg_send_query_params(
            $this->con,
            $deleteQuery,
            array($this->uuid)
        ) or throw new PostgresQueryException($this->con);
        pg_get_result($this->con);
    }

    private function removeReferenceFromParents()
    {
        $removeQuery = "UPDATE licks SET parent = '' WHERE parent = $1;";
        pg_send_query_params(
            $this->con,
            $removeQuery,
            array($this->uuid)
        ) or throw new PostgresQueryException($this->con);
        pg_get_result($this->con);
    }

    private function removeReferenceFromChildren()
    {
        $removeQuery = "UPDATE licks SET children = array_remove(children, $1)";
        pg_send_query_params(
            $this->con,
            $removeQuery,
            array($this->uuid)
        ) or throw new PostgresQueryException($this->con);
        pg_get_result($this->con);
    }
}
