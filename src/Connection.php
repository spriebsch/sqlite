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

use SQLite3Result;
use SQLite3Stmt;

interface Connection
{
    public function database(): string;

    public function isInMemoryDatabase(): bool;

    public function prepare(string $statement): SQLite3Stmt;

    public function exec(string $statement): bool;

    public function query(string $query): SQLite3Result;

    /**
     * Escapes a string for safe inclusion in SQL.
     */
    public function escapeString(string $value): string;
}
