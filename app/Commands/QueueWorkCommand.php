<?php

namespace App\Commands;

use App\Core\App;
use App\Core\Command;
use App\Core\Job;
use PDO;
use Throwable;

class QueueWorkCommand extends Command
{
    protected $signature = 'queue:work';
    protected $description = 'Process jobs from the database queue.';

    public function handle(array $args = [])
    {
        echo "Queue worker started...\n";
        $pdo = App::getContainer()->resolve(PDO::class);

        while (true) {
            $jobData = null;
            try {
                $pdo->beginTransaction();
                
                $stmt = $pdo->prepare(
                    "SELECT * FROM jobs WHERE queue = 'default' AND available_at <= ? ORDER BY created_at ASC LIMIT 1 FOR UPDATE SKIP LOCKED"
                );
                $stmt->execute([date('Y-m-d H:i:s')]);
                $jobData = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($jobData) {
                    $this->process($jobData);
                    
                    // If process is successful, delete the job
                    $deleteStmt = $pdo->prepare("DELETE FROM jobs WHERE id = ?");
                    $deleteStmt->execute([$jobData['id']]);
                    
                    echo "Job ID: {$jobData['id']} processed and deleted.\n";
                }
                
                $pdo->commit();

            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                echo "Error: {$e->getMessage()}\n";
                if ($jobData) {
                    $this->failJob($pdo, $jobData);
                }
            }

            if (!$jobData) {
                // If no job, wait for a second to prevent busy-looping
                sleep(1);
            }
        }
    }

    protected function process(array $jobData)
    {
        $payload = json_decode($jobData['payload'], true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed to decode JSON payload for job ID: ' . $jobData['id']);
        }

        $jobClass = $payload['displayName'];
        $jobPayloadData = $payload['data'];

        if (!class_exists($jobClass)) {
            throw new \Exception("Job class {$jobClass} not found for job ID: " . $jobData['id']);
        }

        // Here we assume the job class can be instantiated without constructor args,
        // or the job itself handles its data. A better way is to pass data to handle().
        // For now, we will pass it to a setData method if it exists, or constructor.
        $jobInstance = new $jobClass();

        if (!$jobInstance instanceof Job) {
            throw new \Exception("Job class {$jobClass} does not implement App\Core\Job interface.");
        }
        
        // A simple dependency injection or data-passing mechanism
        if(method_exists($jobInstance, 'setData')){
            $jobInstance->setData($jobPayloadData);
        }

        $jobInstance->handle();
    }

    protected function failJob(PDO $pdo, array $jobData)
    {
        $stmt = $pdo->prepare("UPDATE jobs SET attempts = attempts + 1 WHERE id = ?");
        $stmt->execute([$jobData['id']]);
        echo "Job ID: {$jobData['id']} failed and attempts incremented.\n";
    }
}