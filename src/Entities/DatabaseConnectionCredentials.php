<?php

namespace App\Entities;

class DatabaseConnectionCredentials 
{
    private string $dbhost;
    private string $dbuser;
    private string $dbpass;
    private string $dbname;

    public function __construct(string $host, string $user, string $pass, string $name) 
    {
        $this->dbhost = $host;
        $this->dbuser = $user;
        $this->dbpass = $pass;
        $this->dbname = $name;
    }

    public function host() {
        return $this->dbhost;
    }
    public function user() {
        return $this->dbuser;
    }
    public function pass() {
        return $this->dbpass;
    }
    public function name() {
        return $this->dbname;
    }
}