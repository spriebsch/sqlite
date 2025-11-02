<?php declare(strict_types=1);

/*
 * This file is part of Sqlite.
 *
 * (c) Stefan Priebsch <stefan@priebsch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spriebsch\sqlite;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use const SQLITE3_ASSOC;

#[CoversClass(SqliteConnection::class)]
class SqliteConnectionTest extends TestCase
{
    private SqliteConnection $connection;

    protected function setUp(): void
    {
        $this->connection = SqliteConnection::from(':memory:');

        $schema = new class($this->connection) extends SqliteSchema {
            public function __construct(Connection $connection)
            {
                parent::__construct($connection);
            }

            protected function schemaExists(Connection $connection): bool
            {
                return false;
            }

            protected function createSchema(Connection $connection): void
            {
                $connection->exec(
                    'CREATE TABLE `test` (
                        `id` INTEGER PRIMARY KEY,
                        `value` TEXT
                    );'
                );
            }
        };

        $schema->createIfNotExists();
    }

    public function test_can_use_in_memory_database(): void
    {
        $this->assertEquals(':memory:', SqliteConnection::memory()->database());
    }

    public function test_executes_statements(): void
    {
        $this->assertTrue($this->connection->exec("INSERT INTO test (value) VALUES ('the-value')"));
    }

    public function test_exception_when_executing_statement_fails(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('no such table');

        $this->connection->exec("INSERT INTO doesNotExist (value) VALUES ('the-value')");
    }

    public function test_prepares_statements(): void
    {
        $this->insertRow($this->connection);

        $result = $this->connection->prepare('SELECT * FROM test')->execute();

        $this->assertEquals($this->rowAsArray(), $result->fetchArray(SQLITE3_ASSOC));
    }

    public function test_exception_when_preparing_statement_fails(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('no such table');

        $this->connection->prepare('SELECT * FROM doesNotExist');
    }

    public function test_query_returns_result(): void
    {
        $this->insertRow($this->connection);

        $result = $this->connection->query('SELECT * FROM test');

        $this->assertEquals($this->rowAsArray(), $result->fetchArray(SQLITE3_ASSOC));
    }

    public function test_escape_string_allows_manual_value_insertion(): void
    {
        $original = "O'Reilly";
        $escaped = $this->connection->escapeString($original);

        // Basic expectation: single quotes are doubled for SQL string literals
        $this->assertSame("O''Reilly", $escaped);

        // Use the escaped value in a raw SQL statement and ensure round-trip integrity
        $this->connection->exec("INSERT INTO test (value) VALUES ('$escaped')");

        $result = $this->connection->query('SELECT value FROM test ORDER BY id DESC LIMIT 1');
        $row = $result->fetchArray(SQLITE3_ASSOC);
        $this->assertSame($original, $row['value']);
    }

    private function insertRow(SqliteConnection $connection): void
    {
        $connection->exec("INSERT INTO test (value) VALUES ('the-value')");
    }

    private function rowAsArray(): array
    {
        return ['id' => 1, 'value' => 'the-value'];
    }
}
