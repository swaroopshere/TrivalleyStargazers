<?php
/**
 * Unit Tests for db.php database functions
 *
 * Note: These tests use a test database or mock the Database class
 * to avoid modifying production data.
 */

use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    private static $dbInitialized = false;

    public static function setUpBeforeClass(): void
    {
        // Load database file if not already loaded
        if (!class_exists('Database')) {
            require_once dirname(__DIR__, 2) . '/includes/db.php';
        }
    }

    protected function setUp(): void
    {
        // Reset the Database singleton for testing
        $reflection = new ReflectionClass('Database');
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);
    }

    /**
     * Test Database singleton pattern
     */
    public function testDatabaseSingletonPattern(): void
    {
        // Skip if no database connection available in test environment
        if (!$this->canConnectToDatabase()) {
            $this->markTestSkipped('Database connection not available for testing');
        }

        $instance1 = Database::getInstance();
        $instance2 = Database::getInstance();

        $this->assertSame($instance1, $instance2);
    }

    /**
     * Test db() helper returns Database instance
     */
    public function testDbHelperReturnsInstance(): void
    {
        if (!$this->canConnectToDatabase()) {
            $this->markTestSkipped('Database connection not available for testing');
        }

        $db = db();
        $this->assertInstanceOf(Database::class, $db);
    }

    /**
     * Test getConnection returns PDO
     */
    public function testGetConnectionReturnsPdo(): void
    {
        if (!$this->canConnectToDatabase()) {
            $this->markTestSkipped('Database connection not available for testing');
        }

        $pdo = db()->getConnection();
        $this->assertInstanceOf(PDO::class, $pdo);
    }

    /**
     * Test query method returns array
     */
    public function testQueryReturnsArray(): void
    {
        if (!$this->canConnectToDatabase()) {
            $this->markTestSkipped('Database connection not available for testing');
        }

        $result = db()->query("SELECT 1 as test");
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals(1, $result[0]['test']);
    }

    /**
     * Test queryOne method returns single row or null
     */
    public function testQueryOneReturnsSingleRow(): void
    {
        if (!$this->canConnectToDatabase()) {
            $this->markTestSkipped('Database connection not available for testing');
        }

        $result = db()->queryOne("SELECT 1 as test");
        $this->assertIsArray($result);
        $this->assertEquals(1, $result['test']);
    }

    /**
     * Test queryOne returns null for no results
     */
    public function testQueryOneReturnsNullForNoResults(): void
    {
        if (!$this->canConnectToDatabase()) {
            $this->markTestSkipped('Database connection not available for testing');
        }

        $result = db()->queryOne("SELECT 1 as test WHERE 1 = 0");
        $this->assertNull($result);
    }

    /**
     * Test parameterized queries work correctly
     */
    public function testParameterizedQueries(): void
    {
        if (!$this->canConnectToDatabase()) {
            $this->markTestSkipped('Database connection not available for testing');
        }

        $result = db()->query("SELECT ? as val1, ? as val2", ['hello', 123]);
        $this->assertEquals('hello', $result[0]['val1']);
        $this->assertEquals('123', $result[0]['val2']);
    }

    /**
     * Test dbQuery helper function
     */
    public function testDbQueryHelper(): void
    {
        if (!$this->canConnectToDatabase()) {
            $this->markTestSkipped('Database connection not available for testing');
        }

        $result = dbQuery("SELECT 'test' as value");
        $this->assertIsArray($result);
        $this->assertEquals('test', $result[0]['value']);
    }

    /**
     * Test dbQueryOne helper function
     */
    public function testDbQueryOneHelper(): void
    {
        if (!$this->canConnectToDatabase()) {
            $this->markTestSkipped('Database connection not available for testing');
        }

        $result = dbQueryOne("SELECT 'single' as value");
        $this->assertIsArray($result);
        $this->assertEquals('single', $result['value']);
    }

    /**
     * Test transaction methods
     */
    public function testTransactionMethods(): void
    {
        if (!$this->canConnectToDatabase()) {
            $this->markTestSkipped('Database connection not available for testing');
        }

        $db = db();

        // Test begin transaction
        $this->assertTrue($db->beginTransaction());

        // Test rollback
        $this->assertTrue($db->rollback());
    }

    /**
     * Test transaction commit
     */
    public function testTransactionCommit(): void
    {
        if (!$this->canConnectToDatabase()) {
            $this->markTestSkipped('Database connection not available for testing');
        }

        $db = db();

        $this->assertTrue($db->beginTransaction());
        $this->assertTrue($db->commit());
    }

    /**
     * Check if database connection is available
     */
    private function canConnectToDatabase(): bool
    {
        try {
            db();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
