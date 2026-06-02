<?php
/**
 * Migration script to add contacts table to MySQL database
 * Run this once to create the table and populate initial data
 */

// Database credentials - update these or use environment variables
$host = getenv('TVS_DB_HOST') ?: 'localhost';
$dbname = getenv('TVS_DB_NAME') ?: '';
$user = getenv('TVS_DB_USER') ?: '';
$pass = getenv('TVS_DB_PASS') ?: '';

if (empty($dbname) || empty($user)) {
    // Try to load from config if environment variables not set
    if (file_exists(__DIR__ . '/../includes/config.php')) {
        require_once __DIR__ . '/../includes/config.php';
        $host = DB_HOST;
        $dbname = DB_NAME;
        $user = DB_USER;
        $pass = DB_PASS;
    }
}

echo "TVS Contacts Table Migration\n";
echo "============================\n\n";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to database.\n\n";

    // Create contacts table
    echo "Creating contacts table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS contacts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            category ENUM('officer', 'board', 'volunteer', 'astrophoto') NOT NULL,
            position VARCHAR(100) NOT NULL,
            name VARCHAR(100) NOT NULL,
            email_user VARCHAR(50) DEFAULT NULL,
            email_domain VARCHAR(100) DEFAULT 'trivalleystargazers.org',
            title VARCHAR(200) DEFAULT NULL,
            website_url VARCHAR(255) DEFAULT NULL,
            website_title VARCHAR(100) DEFAULT NULL,
            sort_order INT DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            updated_by INT DEFAULT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "Table created.\n\n";

    // Check if data already exists
    $count = $pdo->query("SELECT COUNT(*) FROM contacts")->fetchColumn();
    if ($count > 0) {
        echo "Table already has $count records. Skipping data insertion.\n";
        echo "To reset, run: DELETE FROM contacts;\n\n";
    } else {
        echo "Inserting initial contact data...\n";

        // Officers
        $pdo->exec("INSERT INTO contacts (category, position, name, email_user, title, sort_order) VALUES
            ('officer', 'President', 'Eric Dueltgen', 'president', NULL, 1),
            ('officer', 'Vice President', 'Aris Pope', 'vice_president', NULL, 2),
            ('officer', 'Treasurer', 'John Forrest', 'treasurer', NULL, 3),
            ('officer', 'Secretary', 'Dave Lackey', 'secretary', NULL, 4)
        ");
        echo "  - Officers added\n";

        // Board Members
        $pdo->exec("INSERT INTO contacts (category, position, name, email_user, title, sort_order) VALUES
            ('board', 'Past President', 'Ron Kane', 'past_president', NULL, 1),
            ('board', 'At Large', 'Gert Gottschalk', 'astrophotography', NULL, 2),
            ('board', 'At Large', 'Chuck Grant', 'observatory', NULL, 3),
            ('board', 'At Large', 'Swaroop Shere', 'webmaster', NULL, 4)
        ");
        echo "  - Board members added\n";

        // Volunteers
        $pdo->exec("INSERT INTO contacts (category, position, name, email_user, title, sort_order) VALUES
            ('volunteer', 'Astronomical League Representative', 'Don Dossa', 'alrep', NULL, 1),
            ('volunteer', 'Del Valle Coordinator', 'Dave Wilzius', 'delvalle', NULL, 2),
            ('volunteer', 'Historian', 'Open', 'historian', NULL, 3),
            ('volunteer', 'Librarian', 'Ron Kane', 'librarian', NULL, 4),
            ('volunteer', 'Loaner Scope Manager', 'Ron Kane', 'telescopes', NULL, 5),
            ('volunteer', 'Night Sky Network Representative', 'Ross Gaunt', 'nnsn', NULL, 6),
            ('volunteer', 'Newsletter Editor', 'Scott Schneider', 'newsletter', NULL, 7),
            ('volunteer', 'Observatory Director / Rebuild Chairman', 'Chuck Grant', 'observatory', NULL, 8),
            ('volunteer', 'Observatory Co-Director', 'Ross Gaunt', 'H2O-Events', NULL, 9),
            ('volunteer', 'Potluck Coordinator', 'Ron Kane', 'potluck', NULL, 10),
            ('volunteer', 'Programs', 'Ron Kane', 'programs', NULL, 11),
            ('volunteer', 'Publicity and Fundraising', 'Open', 'publicity', NULL, 12),
            ('volunteer', 'Refreshments', 'Open', NULL, NULL, 13),
            ('volunteer', 'Star Party Coordinator', 'Johnathan Bailey', 'coordinator', NULL, 14),
            ('volunteer', 'Webmaster', 'Swaroop Shere', 'webmaster', NULL, 15)
        ");
        echo "  - Volunteers added\n";

        // Astrophoto Links
        $pdo->exec("INSERT INTO contacts (category, position, name, email_user, email_domain, website_url, website_title, sort_order) VALUES
            ('astrophoto', 'Member', 'Deniz Demirci', NULL, NULL, 'http://denizdemirci.ca/', 'Deniz''s Web Page', 1),
            ('astrophoto', 'Member', 'Gert Gottschalk', NULL, NULL, 'http://www.trivalleystargazers.org/gert/Astro_en.htm', 'Gert''s Astrophoto Page', 2),
            ('astrophoto', 'Member', 'Hilary Jones', NULL, NULL, 'http://www.darklights.org/gallery', 'Hilary''s Astrophoto Page', 3),
            ('astrophoto', 'Member', 'Axel Mellinger', NULL, NULL, 'http://www.milkywaysky.com/', 'Axel''s Milky Way Page', 4),
            ('astrophoto', 'Member', 'Ken Sperber', NULL, NULL, 'http://www.trivalleystargazers.org/ken/index.html', 'Ken''s Astrophoto Page', 5),
            ('astrophoto', 'Member', 'Chuck Vaughn', NULL, NULL, 'http://astrophotography.aa6g.org/', 'Chuck''s Astrophoto Page', 6)
        ");
        echo "  - Astrophoto links added\n";

        echo "\nInitial data inserted successfully.\n";
    }

    // Create index
    echo "\nCreating indexes...\n";
    try {
        $pdo->exec("CREATE INDEX idx_contacts_category ON contacts(category)");
        $pdo->exec("CREATE INDEX idx_contacts_active ON contacts(is_active)");
    } catch (PDOException $e) {
        // Index might already exist
        echo "  (indexes may already exist)\n";
    }

    echo "\n============================\n";
    echo "Migration complete!\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
