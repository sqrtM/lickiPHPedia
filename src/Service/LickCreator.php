<?php

namespace App\Service;

use App\Exception\PostgresQueryException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Manages the creation of new licks being sent to the database.
 * PHP Version 8.2.0.
 *
 * @category  Service
 * @package   Database Management
 *
 * @author    Mason Pike <masonapike@gmail.com>
 * @license   unlicense https://unlicense.org/
 *
 * @see       http://url.com
 */
class LickCreator
{
    private string $uuid;
    private string $music_string;
    private string $parent;
    private string $date;
    private \PgSql\Connection $con;

    public function __construct(Request $request, \PgSql\Connection $connection)
    {
        $this->uuid = json_decode($request->getContent())->{'uuid'};
        $this->music_string = json_decode($request->getContent())->{'music_string'};
        $this->parent = json_decode($request->getContent())->{'parent'};
        $this->date = json_decode($request->getContent())->{'date'};

        $this->con = $connection;
    }

    /**
     * Prepares a Postgres statement and executes it, putting the recieved lick into the database.
     *
     * @return void
     *
     * @throws PostgresQueryException if operation fails
     */
    public function insertLickIntoDatabase()
    {
        $insertQuery = 'INSERT INTO licks (uuid, music_string, parent, date) VALUES ($1, $2, $3, $4);';
        pg_send_query_params(
            $this->con,
            $insertQuery,
            array($this->uuid, $this->music_string, $this->parent, $this->date)
        ) or throw new PostgresQueryException($this->con);
    }

    public function hasParent(): bool
    {
        return strlen($this->parent) > 0 ? true : false;
    }

    public function appendChildToParent(): void
    {
        $updateQuery = 'UPDATE licks SET children = array_append(children, $1) WHERE uuid = $2;';
        pg_send_query_params(
            $this->con,
            $updateQuery,
            array($this->uuid, $this->parent)
        ) or throw new PostgresQueryException($this->con);
    }
}
