<?php

namespace App\Services;

use PDO;
use RuntimeException;

class CenterDatabaseDumpService
{
    public function stream(string $database, string $centerName = ''): void
    {
        if (preg_match('/^[A-Za-z0-9_-]+$/', $database) !== 1) {
            throw new RuntimeException('Invalid database name.');
        }

        @set_time_limit(0);
        $pdo = $this->connection($database);

        $this->line('-- Luzori center database dump');
        $this->line('-- Center: ' . str_replace(["\r", "\n"], ' ', $centerName));
        $this->line('-- Database: `' . $database . '`');
        $this->line('-- Generated: ' . date('Y-m-d H:i:s'));
        $this->line('');
        $this->line('SET NAMES utf8mb4;');
        $this->line('SET FOREIGN_KEY_CHECKS=0;');
        $this->line('SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";');
        $this->line('SET time_zone="+00:00";');
        $this->line('');
        $this->line('CREATE DATABASE IF NOT EXISTS ' . $this->quoteIdent($database) . ' DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');
        $this->line('USE ' . $this->quoteIdent($database) . ';');
        $this->line('');

        $tables = [];
        $views = [];
        $list = $pdo->query('SHOW FULL TABLES');
        while ($row = $list->fetch(PDO::FETCH_NUM)) {
            $name = (string) ($row[0] ?? '');
            $type = (string) ($row[1] ?? 'BASE TABLE');
            if ($name === '') {
                continue;
            }
            if (strcasecmp($type, 'VIEW') === 0) {
                $views[] = $name;
            } else {
                $tables[] = $name;
            }
        }
        $list->closeCursor();

        foreach ($views as $view) {
            $this->line('DROP VIEW IF EXISTS ' . $this->quoteIdent($view) . ';');
        }
        if ($views) {
            $this->line('');
        }

        foreach ($tables as $table) {
            $this->dumpTable($pdo, $table);
        }

        foreach ($views as $view) {
            try {
                $this->dumpView($pdo, $view);
            } catch (\Throwable $e) {
                $this->line('-- View ' . $this->quoteIdent($view) . ' skipped: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
            }
        }

        try {
            $this->dumpTriggers($pdo, $database);
        } catch (\Throwable $e) {
            $this->line('-- Triggers skipped: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        }
        try {
            $this->dumpRoutines($pdo, $database, 'PROCEDURE');
            $this->dumpRoutines($pdo, $database, 'FUNCTION');
        } catch (\Throwable $e) {
            $this->line('-- Routines skipped: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        }

        $this->line('SET FOREIGN_KEY_CHECKS=1;');
    }

    private function connection(string $database): PDO
    {
        $config = config('database.connections.tenant');
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $config['host'],
            $config['port'],
            $database
        );

        return new PDO($dsn, (string) $config['username'], (string) $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false,
        ]);
    }

    private function dumpTable(PDO $pdo, string $table): void
    {
        $quoted = $this->quoteIdent($table);
        $fetched = $pdo->query('SHOW CREATE TABLE ' . $quoted);
        $create = $fetched->fetch(PDO::FETCH_NUM);
        $fetched->closeCursor();
        $statement = $create[1] ?? null;
        if (!$statement) {
            return;
        }

        $this->line('--');
        $this->line('-- Table ' . $quoted);
        $this->line('--');
        $this->line('DROP TABLE IF EXISTS ' . $quoted . ';');
        $this->line($statement . ';');
        $this->line('');

        $rows = $pdo->query('SELECT * FROM ' . $quoted);
        $batch = [];
        $columns = null;
        while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
            if ($columns === null) {
                $columns = array_keys($row);
            }
            $batch[] = '(' . implode(',', array_map(fn ($value) => $this->sqlValue($pdo, $value), array_values($row))) . ')';
            if (count($batch) >= 100) {
                $this->writeInsert($quoted, $columns, $batch);
                $batch = [];
            }
        }
        $rows->closeCursor();
        if ($batch && $columns) {
            $this->writeInsert($quoted, $columns, $batch);
        }
        $this->line('');
    }

    private function dumpView(PDO $pdo, string $view): void
    {
        $quoted = $this->quoteIdent($view);
        $fetched = $pdo->query('SHOW CREATE VIEW ' . $quoted);
        $create = $fetched->fetch(PDO::FETCH_NUM);
        $fetched->closeCursor();
        if (empty($create[1])) {
            return;
        }
        $this->line('--');
        $this->line('-- View ' . $quoted);
        $this->line('--');
        $this->line($create[1] . ';');
        $this->line('');
    }

    private function dumpTriggers(PDO $pdo, string $database): void
    {
        $fetched = $pdo->query('SHOW TRIGGERS FROM ' . $this->quoteIdent($database));
        $triggers = $fetched->fetchAll(PDO::FETCH_ASSOC);
        $fetched->closeCursor();

        foreach ($triggers as $trigger) {
            $name = (string) ($trigger['Trigger'] ?? '');
            if ($name === '') {
                continue;
            }
            $quoted = $this->quoteIdent($name);
            $create = $pdo->query('SHOW CREATE TRIGGER ' . $quoted);
            $row = $create->fetch(PDO::FETCH_NUM);
            $create->closeCursor();
            if (empty($row[2])) {
                continue;
            }
            $this->line('DROP TRIGGER IF EXISTS ' . $quoted . ';');
            $this->line('DELIMITER ;;');
            $this->line($row[2] . ';;');
            $this->line('DELIMITER ;');
            $this->line('');
        }
    }

    private function dumpRoutines(PDO $pdo, string $database, string $type): void
    {
        $sql = $type === 'FUNCTION'
            ? 'SHOW FUNCTION STATUS WHERE Db = ' . $pdo->quote($database)
            : 'SHOW PROCEDURE STATUS WHERE Db = ' . $pdo->quote($database);
        $fetched = $pdo->query($sql);
        $routines = $fetched->fetchAll(PDO::FETCH_ASSOC);
        $fetched->closeCursor();

        foreach ($routines as $routine) {
            $name = (string) ($routine['Name'] ?? '');
            if ($name === '') {
                continue;
            }
            $quoted = $this->quoteIdent($name);
            $create = $pdo->query('SHOW CREATE ' . $type . ' ' . $quoted);
            $row = $create->fetch(PDO::FETCH_NUM);
            $create->closeCursor();
            $statement = $row[2] ?? null;
            if (!$statement) {
                continue;
            }
            $this->line('DROP ' . $type . ' IF EXISTS ' . $quoted . ';');
            $this->line('DELIMITER ;;');
            $this->line($statement . ';;');
            $this->line('DELIMITER ;');
            $this->line('');
        }
    }

    private function writeInsert(string $table, array $columns, array $batch): void
    {
        $columnList = implode(',', array_map(fn ($column) => $this->quoteIdent($column), $columns));
        $this->line('INSERT INTO ' . $table . ' (' . $columnList . ') VALUES');
        $this->line(implode(",\n", $batch) . ';');
    }

    private function sqlValue(PDO $pdo, mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        $string = (string) $value;
        if (!mb_check_encoding($string, 'UTF-8')) {
            return '0x' . bin2hex($string);
        }

        return $pdo->quote($string);
    }

    private function quoteIdent(string $name): string
    {
        return '`' . str_replace('`', '``', $name) . '`';
    }

    private function line(string $sql): void
    {
        echo $sql . "\n";
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
    }
}
