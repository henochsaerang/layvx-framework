<?php

namespace App\Core;

use PDO;
use Exception;

abstract class TestCase
{
    /**
     * Assert that a condition is true.
     *
     * @param bool $condition
     * @param string $message
     * @throws Exception
     */
    protected function assertTrue(bool $condition, string $message = 'Failed asserting that the condition is true.')
    {
        if ($condition !== true) {
            throw new Exception($message);
        }
    }

    /**
     * Assert that two values are equal.
     *
     * @param mixed $expected
     * @param mixed $actual
     * @param string $message
     * @throws Exception
     */
    protected function assertEquals($expected, $actual, string $message = 'Failed asserting that two values are equal.')
    {
        if ($expected != $actual) {
            throw new Exception($message . " Expected: " . print_r($expected, true) . ", Actual: " . print_r($actual, true));
        }
    }

    /**
     * Assert that a given record exists in the database.
     *
     * @param string $table
     * @param array $data
     * @param string $message
     * @throws Exception
     */
    protected function assertDatabaseHas(string $table, array $data, string $message = '')
    {
        $pdo = App::getContainer()->resolve(PDO::class);

        $whereClauses = [];
        foreach (array_keys($data) as $column) {
            $whereClauses[] = "`{$column}` = :{$column}";
        }

        $sql = "SELECT COUNT(*) as count FROM `{$table}` WHERE " . implode(' AND ', $whereClauses);
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result || $result['count'] === 0) {
            $defaultMessage = "A record in table [{$table}] matching the constraints was not found.";
            throw new Exception($message ?: $defaultMessage);
        }
    }
}
