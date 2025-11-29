<?php

namespace App\Core;

use PDO;

class Queue
{
    /**
     * Push a new job onto the queue.
     *
     * @param string $jobClass The fully qualified class name of the job.
     * @param array $data Data to be passed to the job.
     * @param string $queue The queue to push the job to.
     * @return void
     */
    public static function push(string $jobClass, array $data = [], string $queue = 'default')
    {
        $payload = json_encode(['displayName' => $jobClass, 'data' => $data]);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // Handle JSON encoding error, maybe log it
            error_log('Could not encode job payload for queueing: ' . json_last_error_msg());
            return;
        }

        $pdo = App::getContainer()->resolve(PDO::class);

        $sql = "INSERT INTO jobs (queue, payload, available_at, created_at) VALUES (:queue, :payload, :available_at, :created_at)";

        $statement = $pdo->prepare($sql);

        $statement->execute([
            ':queue' => $queue,
            ':payload' => $payload,
            ':available_at' => date('Y-m-d H:i:s'), // Available immediately
            ':created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
