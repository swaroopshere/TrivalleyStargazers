<?php
/**
 * Migration: 001_add_contacts_table
 * Description: Create contacts table and seed initial data from contacts.php
 */

return new class {
    public function up(PDO $pdo): array {
        $messages = [];

        $exists = $pdo->query("SHOW TABLES LIKE 'contacts'")->rowCount() > 0;

        if ($exists) {
            $messages[] = "Table 'contacts' already exists - skipping create";
        } else {
            $pdo->exec("
                CREATE TABLE contacts (
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
            $pdo->exec("CREATE INDEX idx_contacts_category ON contacts(category)");
            $pdo->exec("CREATE INDEX idx_contacts_active ON contacts(is_active)");
            $messages[] = "Created table 'contacts'";
        }

        $count = $pdo->query("SELECT COUNT(*) FROM contacts")->fetchColumn();
        if ($count > 0) {
            $messages[] = "Contacts table already has data - skipping seed";
            return $messages;
        }

        // Officers
        $pdo->exec("INSERT INTO contacts (category, position, name, email_user, sort_order) VALUES
            ('officer', 'President',      'Eric Dueltgen', 'president',      1),
            ('officer', 'Vice President', 'Aris Pope',     'vice_president', 2),
            ('officer', 'Treasurer',      'John Forrest',  'treasurer',      3),
            ('officer', 'Secretary',      'Dave Lackey',   'secretary',      4)
        ");
        $messages[] = "Seeded officers";

        // Board Members
        $pdo->exec("INSERT INTO contacts (category, position, name, email_user, sort_order) VALUES
            ('board', 'Past President', 'Ron Kane',       'past_president',  1),
            ('board', 'At Large',       'Gert Gottschalk','astrophotography',2),
            ('board', 'At Large',       'Chuck Grant',    'observatory',     3),
            ('board', 'At Large',       'Swaroop Shere',  'webmaster',       4)
        ");
        $messages[] = "Seeded board members";

        // Volunteers — Open positions have NULL email_user (no link shown)
        $pdo->exec("INSERT INTO contacts (category, position, name, email_user, sort_order) VALUES
            ('volunteer', 'Astronomical League Representative', 'Don Dossa',        'alrep',      1),
            ('volunteer', 'Del Valle Coordinator',              'Dave Wilzius',     'delvalle',   2),
            ('volunteer', 'Historian',                          'Open',             NULL,         3),
            ('volunteer', 'Librarian',                          'Ron Kane',         'librarian',  4),
            ('volunteer', 'Loaner Scope Manager',               'Ron Kane',         'telescopes', 5),
            ('volunteer', 'Night Sky Network Representative',   'Ross Gaunt',       'nnsn',       6),
            ('volunteer', 'Newsletter Editor',                  'Scott Schneider',  'newsletter', 7),
            ('volunteer', 'Observatory Director / Rebuild Chairman', 'Chuck Grant', 'observatory',8),
            ('volunteer', 'Observatory Co-Director',            'Ross Gaunt',       'H2O-Events', 9),
            ('volunteer', 'Potluck Coordinator',                'Ron Kane',         'potluck',    10),
            ('volunteer', 'Programs',                           'Ron Kane',         'programs',   11),
            ('volunteer', 'Publicity and Fundraising',          'Open',             NULL,         12),
            ('volunteer', 'Refreshments',                       'Open',             NULL,         13),
            ('volunteer', 'Star Party Coordinator',             'Johnathan Bailey', 'coordinator',14),
            ('volunteer', 'Webmaster',                          'Swaroop Shere',    'webmaster',  15)
        ");
        $messages[] = "Seeded volunteers";

        // Astrophoto links
        $pdo->exec("INSERT INTO contacts (category, position, name, website_url, website_title, sort_order) VALUES
            ('astrophoto', 'Member', 'Deniz Demirci',   'http://denizdemirci.ca/',                                    'Deniz''s Web Page',      1),
            ('astrophoto', 'Member', 'Gert Gottschalk', 'http://www.trivalleystargazers.org/gert/Astro_en.htm',       'Gert''s Astrophoto Page', 2),
            ('astrophoto', 'Member', 'Hilary Jones',    'http://www.darklights.org/gallery',                          'Hilary''s Astrophoto Page',3),
            ('astrophoto', 'Member', 'Axel Mellinger',  'http://www.milkywaysky.com/',                                'Axel''s Milky Way Page', 4),
            ('astrophoto', 'Member', 'Ken Sperber',     'http://www.trivalleystargazers.org/ken/index.html',          'Ken''s Astrophoto Page',  5),
            ('astrophoto', 'Member', 'Chuck Vaughn',    'http://astrophotography.aa6g.org/',                          'Chuck''s Astrophoto Page',6)
        ");
        $messages[] = "Seeded astrophoto links";

        return $messages;
    }
};
