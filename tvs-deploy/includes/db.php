<?php
/**
 * TVS Database Connection
 *
 * MySQL database wrapper with connection management
 */

require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            throw new Exception('Database connection failed');
        }
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->pdo;
    }

    /**
     * Execute a query and return all results
     */
    public function query(string $sql, array $params = []): array {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Execute a query and return a single row
     */
    public function queryOne(string $sql, array $params = []): ?array {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Execute an INSERT/UPDATE/DELETE and return affected rows
     */
    public function execute(string $sql, array $params = []): int {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Insert a row and return the last insert ID
     */
    public function insert(string $sql, array $params = []): int {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Begin a transaction
     */
    public function beginTransaction(): bool {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commit a transaction
     */
    public function commit(): bool {
        return $this->pdo->commit();
    }

    /**
     * Rollback a transaction
     */
    public function rollback(): bool {
        return $this->pdo->rollBack();
    }
}

// Helper functions for quick database access

function db(): Database {
    return Database::getInstance();
}

function dbQuery(string $sql, array $params = []): array {
    return db()->query($sql, $params);
}

function dbQueryOne(string $sql, array $params = []): ?array {
    return db()->queryOne($sql, $params);
}

function dbExecute(string $sql, array $params = []): int {
    return db()->execute($sql, $params);
}

function dbInsert(string $sql, array $params = []): int {
    return db()->insert($sql, $params);
}

/**
 * Get the current public meeting
 */
function getCurrentPublicMeeting(): ?array {
    return dbQueryOne(
        "SELECT * FROM meetings WHERE meeting_type = 'public' AND is_active = 1
         ORDER BY meeting_date DESC LIMIT 1"
    );
}

/**
 * Get the current board meeting
 */
function getCurrentBoardMeeting(): ?array {
    return dbQueryOne(
        "SELECT * FROM meetings WHERE meeting_type = 'board' AND is_active = 1
         ORDER BY meeting_date DESC LIMIT 1"
    );
}

/**
 * Get visible events by type
 */
function getVisibleEvents(string $type): array {
    return dbQuery(
        "SELECT * FROM events WHERE event_type = ? AND is_visible = 1
         ORDER BY event_date ASC",
        [$type]
    );
}

/**
 * Get upcoming calendar events
 */
function getUpcomingEvents(int $limit = 10): array {
    return dbQuery(
        "SELECT * FROM calendar_cache WHERE event_date >= CURDATE()
         ORDER BY event_date ASC LIMIT ?",
        [$limit]
    );
}

/**
 * Get the current newsletter
 */
function getCurrentNewsletter(): ?array {
    return dbQueryOne(
        "SELECT * FROM newsletters WHERE is_current = 1 LIMIT 1"
    );
}

/**
 * Get newsletters by year
 */
function getNewslettersByYear(int $year): array {
    return dbQuery(
        "SELECT * FROM newsletters WHERE year = ? ORDER BY month DESC",
        [$year]
    );
}

/**
 * Get all newsletter years
 */
function getNewsletterYears(): array {
    return dbQuery(
        "SELECT DISTINCT year FROM newsletters ORDER BY year DESC"
    );
}

/**
 * Get all upcoming events chronologically (meetings, star parties, etc.)
 */
function getAllUpcomingEvents(int $limit = 15): array {
    $events = [];
    $today = date('Y-m-d');

    // Get upcoming public meetings
    $meetings = dbQuery(
        "SELECT 'meeting' as event_type, 'Club Meeting' as title, meeting_date as event_date,
                meeting_time as event_time, location, CONCAT('Public meeting at ', location) as description
         FROM meetings
         WHERE meeting_type = 'public' AND is_active = 1 AND meeting_date >= ?
         ORDER BY meeting_date ASC LIMIT 3",
        [$today]
    );
    $events = array_merge($events, $meetings);

    // Get upcoming board meetings
    $boardMeetings = dbQuery(
        "SELECT 'board' as event_type, 'Board Meeting' as title, meeting_date as event_date,
                meeting_time as event_time, location, description
         FROM meetings
         WHERE meeting_type = 'board' AND is_active = 1 AND meeting_date >= ?
         ORDER BY meeting_date ASC LIMIT 3",
        [$today]
    );
    $events = array_merge($events, $boardMeetings);

    // Get H2O events (star parties)
    $h2oEvents = dbQuery(
        "SELECT 'h2o' as event_type, 'H2O Star Party' as title, event_date,
                '17:00:00' as event_time, 'Hidden Hill Observatory' as location, title as description
         FROM events
         WHERE event_type = 'h2o' AND is_visible = 1 AND event_date >= ?
         ORDER BY event_date ASC LIMIT 5",
        [$today]
    );
    $events = array_merge($events, $h2oEvents);

    // Get Tesla Vineyard events
    $teslaEvents = dbQuery(
        "SELECT 'tesla' as event_type, 'Tesla Vineyard Party' as title, event_date,
                '20:00:00' as event_time, 'Tesla Vintners' as location, title as description
         FROM events
         WHERE event_type = 'tesla' AND is_visible = 1 AND event_date >= ?
         ORDER BY event_date ASC LIMIT 5",
        [$today]
    );
    $events = array_merge($events, $teslaEvents);

    // Get general star parties from calendar cache
    $starParties = dbQuery(
        "SELECT 'starparty' as event_type, title, event_date,
                '20:00:00' as event_time, location, title as description
         FROM calendar_cache
         WHERE event_date >= ? AND (title LIKE '%star party%' OR title LIKE '%Star Party%')
         ORDER BY event_date ASC LIMIT 5",
        [$today]
    );
    $events = array_merge($events, $starParties);

    // Sort all events by date
    usort($events, function($a, $b) {
        return strcmp($a['event_date'], $b['event_date']);
    });

    // Return limited results
    return array_slice($events, 0, $limit);
}

/**
 * Log an audit event
 */
function logAudit(int $userId, string $action, string $tableName = '', int $recordId = 0, string $oldValue = '', string $newValue = ''): void {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    dbInsert(
        "INSERT INTO audit_log (user_id, action, table_name, record_id, old_value, new_value, ip_address)
         VALUES (?, ?, ?, ?, ?, ?, ?)",
        [$userId, $action, $tableName, $recordId, $oldValue, $newValue, $ip]
    );
}
