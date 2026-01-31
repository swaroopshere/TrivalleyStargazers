<?php
/**
 * Migration: 001_initial_schema
 * Description: Base database schema (users, meetings, events, etc.)
 *
 * This migration represents the initial schema. It's marked as executed
 * automatically if the tables already exist.
 */

return new class {
    public function up(PDO $pdo): array {
        $messages = [];

        // Check if base tables exist (if so, this is an existing database)
        $usersExists = $pdo->query("SHOW TABLES LIKE 'users'")->rowCount() > 0;

        if ($usersExists) {
            $messages[] = "Base schema already exists - marking as migrated";
            return $messages;
        }

        // Create base tables for fresh installs
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(255) UNIQUE NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                email VARCHAR(255),
                role VARCHAR(50) DEFAULT 'publisher',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                last_login DATETIME,
                is_active TINYINT DEFAULT 1
            )
        ");
        $messages[] = "Created table 'users'";

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS site_content (
                id INT AUTO_INCREMENT PRIMARY KEY,
                content_key VARCHAR(255) UNIQUE NOT NULL,
                content_value TEXT,
                updated_by INT,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (updated_by) REFERENCES users(id)
            )
        ");
        $messages[] = "Created table 'site_content'";

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS meetings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                meeting_type VARCHAR(50) NOT NULL,
                meeting_date DATE NOT NULL,
                meeting_time TIME,
                location VARCHAR(255),
                location_address VARCHAR(255),
                description TEXT,
                meeting_format VARCHAR(50) DEFAULT 'hybrid',
                is_active TINYINT DEFAULT 1,
                updated_by INT,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (updated_by) REFERENCES users(id)
            )
        ");
        $messages[] = "Created table 'meetings'";

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS events (
                id INT AUTO_INCREMENT PRIMARY KEY,
                event_type VARCHAR(50) NOT NULL,
                event_date DATE,
                title VARCHAR(255),
                description TEXT,
                is_visible TINYINT DEFAULT 1,
                sort_order INT DEFAULT 0,
                updated_by INT,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (updated_by) REFERENCES users(id)
            )
        ");
        $messages[] = "Created table 'events'";

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS calendar_cache (
                id INT AUTO_INCREMENT PRIMARY KEY,
                event_id VARCHAR(255) UNIQUE,
                event_date DATE,
                event_time VARCHAR(50),
                title VARCHAR(255),
                description TEXT,
                location VARCHAR(255),
                fetched_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        $messages[] = "Created table 'calendar_cache'";

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS newsletters (
                id INT AUTO_INCREMENT PRIMARY KEY,
                year INT NOT NULL,
                month INT NOT NULL,
                filename VARCHAR(255) NOT NULL,
                file_path VARCHAR(500) NOT NULL,
                file_type VARCHAR(50) DEFAULT 'pdf',
                file_size INT,
                uploaded_by INT,
                uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                is_current TINYINT DEFAULT 0,
                UNIQUE KEY unique_year_month (year, month),
                FOREIGN KEY (uploaded_by) REFERENCES users(id)
            )
        ");
        $messages[] = "Created table 'newsletters'";

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS audit_log (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT,
                action VARCHAR(255),
                table_name VARCHAR(255),
                record_id INT,
                old_value TEXT,
                new_value TEXT,
                ip_address VARCHAR(50),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )
        ");
        $messages[] = "Created table 'audit_log'";

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS login_attempts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(255),
                ip_address VARCHAR(50),
                success TINYINT DEFAULT 0,
                attempted_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        $messages[] = "Created table 'login_attempts'";

        // Create indexes
        $pdo->exec("CREATE INDEX idx_meetings_type_active ON meetings(meeting_type, is_active)");
        $pdo->exec("CREATE INDEX idx_newsletters_year_month ON newsletters(year, month)");
        $pdo->exec("CREATE INDEX idx_events_type ON events(event_type)");
        $pdo->exec("CREATE INDEX idx_events_date ON events(event_date)");
        $pdo->exec("CREATE INDEX idx_calendar_cache_date ON calendar_cache(event_date)");
        $pdo->exec("CREATE INDEX idx_audit_log_user ON audit_log(user_id)");
        $pdo->exec("CREATE INDEX idx_login_attempts_ip ON login_attempts(ip_address)");
        $messages[] = "Created indexes";

        // Insert default admin user
        $pdo->exec("
            INSERT IGNORE INTO users (username, password_hash, email, role)
            VALUES ('admin', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@trivalleystargazers.org', 'admin')
        ");
        $messages[] = "Created default admin user";

        return $messages;
    }
};
