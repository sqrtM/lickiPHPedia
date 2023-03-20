<?php

namespace App\Service;

use App\Exception\NoMatchingLickException;
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
class UserManager implements IUser
{
    private string $email;
    private string|null $password;
    private string|null $uuid;
    private \PgSql\Connection $con;

    public function __construct(Request $request, \PgSql\Connection $connection)
    {
        $this->email = json_decode($request->getContent())->{'email'};
        $this->password = json_decode($request->getContent())->{'password'} or null;
        $this->email = json_decode($request->getContent())->{'uuid'} or null;

        $this->con = $connection;
    }

    public function getUserInfo(): array
    {
        $results = pg_query_params(
            $this->con,
            'SELECT id FROM users WHERE email = $1 AND password = crypt($2, password);',
            array($this->email, $this->password)
        ) or throw new PostgresQueryException($this->con);

        return pg_fetch_all($results);
    }

    public function getSavedLicks()
    {
        $results = pg_query_params(
            $this->con,
            'SELECT saved_licks FROM users WHERE email = $1',
            array($this->email)
        ) or throw new PostgresQueryException($this->con);

        return pg_fetch_all($results);
    }

    public function addSavedLick()
    {
        if ($this->lickExists()) {
            pg_query_params(
                $this->con,
                'UPDATE users SET saved_licks = array_append(saved_licks, $1) WHERE email = $2;',
                array($this->uuid, $this->email)
            ) or throw new PostgresQueryException($this->con);
        } else {
            throw new NoMatchingLickException();
        }
    }

    private function lickExists()
    {
        return false === pg_query_params(
            $this->con,
            'SELECT uuid FROM licks WHERE uuid = $1',
            array($this->uuid)
        ) ? false : true;
    }
}
