<?php

namespace App\Service;

use App\Exception\InvalidPasswordException;
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
class UserCreator implements IUser
{
    private string $email;
    private string $password;
    private \PgSql\Connection $con;

    public function __construct(Request $request, \PgSql\Connection $connection)
    {
        $this->email = json_decode($request->getContent())->{'email'};
        $this->password = json_decode($request->getContent())->{'password'};

        $this->con = $connection;

        if (strlen($this->password) > 72) {
            throw new InvalidPasswordException();
        }
    }

    public function getUserInfo(): array
    {
        $results = pg_query_params(
            $this->con,
            "SELECT * FROM users WHERE email = '$1'",
            array($this->email)
        ) or throw new PostgresQueryException($this->con);

        return pg_fetch_all($results);
    }

    public function createUser()
    {
        pg_send_query_params(
            $this->con,
            "INSERT INTO users (email, password) VALUES ($1, crypt($2, gen_salt('bf')));",
            array($this->email, $this->password)
        ) or throw new PostgresQueryException($this->con);
    }
}
