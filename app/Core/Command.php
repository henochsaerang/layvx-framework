<?php

namespace App\Core;

abstract class Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    abstract public function handle(array $args = []);
}
