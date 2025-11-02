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

use SQLite3;
use SQLite3Result;
use SQLite3Stmt;

final readonly class SqliteConnection implements Connection
{
    private SQLite3 $connection;

    private function __construct(private string $database) {}

    public static function memory(): self
    {
        return new self(':memory:');
    }

    public static function from(string $database): self
    {
        return new self($database);
    }

    public function isInMemoryDatabase(): bool
    {
        return $this->database === ':memory:';
    }

    public function database(): string
    {
        return $this->database;
    }

    public function prepare(string $statement): SQLite3Stmt
    {
        return $this->connection()->prepare($statement);
    }

    public function exec(string $statement): bool
    {
        return $this->connection()->exec($statement);
    }

    public function query(string $query): SQLite3Result
    {
        return $this->connection()->query($query);
    }

    public function escapeString(string $value): string
    {
        return $this->connection()->escapeString($value);
    }

    public function connection(): Sqlite3
    {
        if (!isset($this->connection)) {
            $this->connection = new SQLite3($this->database);
            $this->connection->enableExceptions(true);
            $this->connection->exec('PRAGMA busy_timeout=10000');
        }

        return $this->connection;
    }
}
