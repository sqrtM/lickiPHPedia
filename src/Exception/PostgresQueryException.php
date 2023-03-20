<?php

namespace App\Exception;

/**
 * Exception class for specific issues relating to queries which were successfully sent
 * but contain a parsing issue of some kind.
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
class PostgresQueryException extends \Exception
{
    private \PgSql\Connection $con;

    public function __construct(\PgSql\Connection $con)
    {
        $this->message = 'Query failed: ' . pg_last_error($con);
    }
}
