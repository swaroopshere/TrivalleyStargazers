<?php
/**
 * Migration: 002_presentations_and_documents
 * Description: Add presentation fields to meetings, create official_documents table
 */

return new class {
    public function up(PDO $pdo): array {
        $messages = [];

        // =========================================
        // STEP 1: Add presentation columns to meetings
        // =========================================
        $columnsToAdd = [
            'presentation_topic' => 'VARCHAR(500)',
            'presenter_name' => 'VARCHAR(255)',
            'presenter_title' => 'VARCHAR(500)',
            'presentation_abstract' => 'TEXT',
            'presenter_bio' => 'TEXT'
        ];

        // Get existing columns
        $existingColumns = [];
        $result = $pdo->query("DESCRIBE meetings");
        foreach ($result as $row) {
            $existingColumns[] = $row['Field'];
        }

        foreach ($columnsToAdd as $column => $type) {
            if (in_array($column, $existingColumns)) {
                $messages[] = "Column 'meetings.$column' already exists - skipping";
            } else {
                $pdo->exec("ALTER TABLE meetings ADD COLUMN $column $type");
                $messages[] = "Added column 'meetings.$column'";
            }
        }

        // =========================================
        // STEP 2: Create official_documents table
        // =========================================
        $tableExists = $pdo->query("SHOW TABLES LIKE 'official_documents'")->rowCount() > 0;

        if ($tableExists) {
            $messages[] = "Table 'official_documents' already exists - skipping";
        } else {
            $pdo->exec("
                CREATE TABLE official_documents (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    doc_type VARCHAR(100) NOT NULL,
                    title VARCHAR(255) NOT NULL,
                    description TEXT,
                    filename VARCHAR(255),
                    file_path VARCHAR(500),
                    file_size INT,
                    sort_order INT DEFAULT 0,
                    is_active TINYINT DEFAULT 1,
                    uploaded_by INT,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY unique_doc_type (doc_type),
                    FOREIGN KEY (uploaded_by) REFERENCES users(id)
                )
            ");
            $messages[] = "Created table 'official_documents'";

            $pdo->exec("CREATE INDEX idx_documents_active ON official_documents(is_active, sort_order)");
            $messages[] = "Created index 'idx_documents_active'";
        }

        // =========================================
        // STEP 3: Migrate data from presentations table (if exists)
        // =========================================
        $presentationsExists = $pdo->query("SHOW TABLES LIKE 'presentations'")->rowCount() > 0;

        if (!$presentationsExists) {
            $messages[] = "No 'presentations' table found - skipping data migration";
        } else {
            $count = $pdo->query("SELECT COUNT(*) FROM presentations")->fetchColumn();
            $messages[] = "Found $count presentation(s) to migrate";

            if ($count > 0) {
                $migrated = $pdo->exec("
                    UPDATE meetings m
                    INNER JOIN presentations p
                        ON YEAR(m.meeting_date) = p.year
                        AND MONTH(m.meeting_date) = p.month
                    SET
                        m.presentation_topic = p.topic,
                        m.presenter_name = p.presenter_name,
                        m.presenter_title = p.presenter_title,
                        m.presentation_abstract = p.abstract,
                        m.presenter_bio = p.bio
                    WHERE m.meeting_type = 'public'
                        AND (m.presentation_topic IS NULL OR m.presentation_topic = '')
                ");
                $messages[] = "Migrated $migrated meeting(s) with presentation data";
            }
        }

        // =========================================
        // STEP 4: Insert default document types
        // =========================================
        $defaultDocs = [
            ['bylaws', 'Bylaws', 'The official bylaws of the Tri-Valley Stargazers astronomy club.', 1],
            ['articles', 'Articles of Incorporation', 'The articles of incorporation for the Tri-Valley Stargazers.', 2],
            ['501c3', '501(c)(3) Status', 'Documentation of our tax-exempt status as a 501(c)(3) non-profit organization.', 3]
        ];

        foreach ($defaultDocs as $doc) {
            $existing = $pdo->prepare("SELECT id FROM official_documents WHERE doc_type = ?");
            $existing->execute([$doc[0]]);

            if ($existing->fetch()) {
                $messages[] = "Document type '{$doc[0]}' already exists - skipping";
            } else {
                $stmt = $pdo->prepare("INSERT INTO official_documents (doc_type, title, description, sort_order) VALUES (?, ?, ?, ?)");
                $stmt->execute($doc);
                $messages[] = "Added document type '{$doc[0]}'";
            }
        }

        return $messages;
    }
};
