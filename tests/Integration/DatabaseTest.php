<?php

declare(strict_types=1);

namespace FluxbbArchiver\Tests\Integration;

use FluxbbArchiver\Database;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Integration tests for Database class.
 * These tests require a running MariaDB instance.
 *
 * @group integration
 */
class DatabaseTest extends TestCase
{
    private ?Database $db = null;

    protected function setUp(): void
    {
        $host = getenv('DB_HOST') ?: 'mariadb';
        $user = getenv('DB_USER') ?: 'fluxbb';
        $password = getenv('DB_PASSWORD') ?: 'fluxbb';
        $database = getenv('DB_NAME') ?: 'fluxbb';
        $prefix = getenv('DB_PREFIX') ?: 'fluxbb_';

        try {
            $this->db = new Database($host, 3306, $user, $password, $database, $prefix);
        } catch (RuntimeException $e) {
            $this->markTestSkipped('Database connection not available: ' . $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        if ($this->db !== null) {
            $this->db->close();
        }
    }

    public function testConnectionEstablished(): void
    {
        $this->assertInstanceOf(Database::class, $this->db);
    }

    public function testPrefixReturnsConfiguredPrefix(): void
    {
        $prefix = getenv('DB_PREFIX') ?: 'fluxbb_';
        $this->assertSame($prefix, $this->db->prefix());
    }

    public function testQueryReturnsResult(): void
    {
        $result = $this->db->query('SELECT 1 as test');
        $this->assertNotFalse($result);
    }

    public function testFetchAllReturnsArray(): void
    {
        $result = $this->db->fetchAll('SELECT 1 as value UNION SELECT 2 as value');

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertSame('1', $result[0]['value']);
        $this->assertSame('2', $result[1]['value']);
    }

    public function testFetchOneReturnsFirstRow(): void
    {
        $result = $this->db->fetchOne('SELECT 1 as value UNION SELECT 2 as value');

        $this->assertIsArray($result);
        $this->assertSame('1', $result['value']);
    }

    public function testFetchOneReturnsNullForEmptyResult(): void
    {
        $result = $this->db->fetchOne('SELECT 1 as value WHERE 1 = 0');

        $this->assertNull($result);
    }

    public function testTableExistsReturnsTrueForExistingTable(): void
    {
        // tableExists expects the full table name including prefix
        $prefix = $this->db->prefix();
        $exists = $this->db->tableExists($prefix . 'users');

        $this->assertTrue($exists);
    }

    public function testTableExistsReturnsFalseForNonExistingTable(): void
    {
        $exists = $this->db->tableExists('nonexistent_table_xyz');

        $this->assertFalse($exists);
    }

    public function testFetchAllWithUsersTable(): void
    {
        $prefix = $this->db->prefix();
        $result = $this->db->fetchAll("SELECT id, username FROM {$prefix}users LIMIT 5");

        $this->assertIsArray($result);
        // Should have at least the guest user
        $this->assertNotEmpty($result);
    }

    public function testFetchAllReturnsEmptyArrayForNoResults(): void
    {
        $result = $this->db->fetchAll('SELECT 1 WHERE 1 = 0');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}
