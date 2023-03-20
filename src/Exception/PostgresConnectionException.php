<?php

namespace App\Exception;

/**
 * Exception class for Postgres Connection issues
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
class PostgresConnectionException extends \Exception
{
    private \PgSql\Connection $con;

    public function __construct()
    {
        $this->message =
        'Failed to connect to database. 
        Please check that there are not too many simulataneous connections
        and consider trying again.';
    }
}
