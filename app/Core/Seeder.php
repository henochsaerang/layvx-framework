<?php

namespace App\Core;

use PDO;

abstract class Seeder
{
    protected $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    abstract public function run();

    public function call($class)
    {
        $seeder = new $class($this->db);
        $seeder->run();
    }
}
