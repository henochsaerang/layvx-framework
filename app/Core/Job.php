<?php

namespace App\Core;

interface Job
{
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle();
}