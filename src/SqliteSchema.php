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

/**
 * @codeCoverageIgnore
 */
abstract class SqliteSchema
{
    protected function __construct(private readonly Connection $connection) {}

    public static function from(Connection $connection): static
    {
        return new static($connection);
    }

    public function createIfNotExists(): void
    {
        if ($this->databaseSchemaExists($this->connection)) {
            return;
        }

        $this->createDatabaseSchema($this->connection);
    }

    private function databaseSchemaExists(Connection $connection): bool
    {
        if ($this->databaseDoesNotExist($connection)) {
            return false;
        }

        return $this->schemaExists($connection);
    }

    private function databaseDoesNotExist(Connection $connection): bool
    {
        if ($connection->isInMemoryDatabase()) {
            return false;
        }

        return !$this->databaseExists($connection->database());
    }

    private function databaseExists(string $database): bool
    {
        return is_file($database);
    }

    private function createDatabaseSchema(Connection $connection): void
    {
        if (!$connection->isInMemoryDatabase()) {
            if ($this->databaseDirectoryDoesNotExist($connection->database())) {
                $this->createDatabaseDirectory($connection->database());
            }
        }

        $this->createSchema($connection);
    }

    private function createDatabaseDirectory(string $database): void
    {
        mkdir($this->databaseDirectory($database), 0755, true);
    }

    private function databaseDirectoryDoesNotExist(string $database): bool
    {
        return !is_dir($this->databaseDirectory($database));
    }

    private function databaseDirectory($database): string
    {
        return dirname($database);
    }

    abstract protected function schemaExists(Connection $connection): bool;

    abstract protected function createSchema(Connection $connection): void;
}
