-- TVS Website Database Schema (MySQL)
-- MySQL database for Tri-Valley Stargazers website

-- Users (multiple accounts)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    role VARCHAR(50) DEFAULT 'publisher',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME,
    is_active TINYINT DEFAULT 1
);

-- Site content (key-value for flexible updates)
CREATE TABLE IF NOT EXISTS site_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content_key VARCHAR(255) UNIQUE NOT NULL,
    content_value TEXT,
    updated_by INT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id)
);

-- Monthly meetings (public meetings include presentation details)
CREATE TABLE IF NOT EXISTS meetings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meeting_type VARCHAR(50) NOT NULL,  -- 'public' or 'board'
    meeting_date DATE NOT NULL,
    meeting_time TIME,
    location VARCHAR(255),
    location_address VARCHAR(255),
    description TEXT,
    meeting_format VARCHAR(50) DEFAULT 'hybrid',  -- 'in-person', 'zoom', 'hybrid'
    -- Presentation fields (for public meetings only)
    presentation_topic VARCHAR(500),
    presenter_name VARCHAR(255),
    presenter_title VARCHAR(500),
    presentation_abstract TEXT,
    presenter_bio TEXT,
    -- Status and metadata
    is_active TINYINT DEFAULT 1,
    updated_by INT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id)
);

-- Events (H2O, Tesla, announcements)
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_type VARCHAR(50) NOT NULL,  -- 'h2o', 'tesla', 'announcement', 'bbq', 'potluck'
    event_date DATE,
    title VARCHAR(255),
    description TEXT,
    is_visible TINYINT DEFAULT 1,
    sort_order INT DEFAULT 0,
    updated_by INT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id)
);

-- Calendar cache (from groups.io)
CREATE TABLE IF NOT EXISTS calendar_cache (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id VARCHAR(255) UNIQUE,
    event_date DATE,
    event_time VARCHAR(50),
    title VARCHAR(255),
    description TEXT,
    location VARCHAR(255),
    fetched_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Newsletters
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
);

-- Audit log
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
);

-- Login attempts (for rate limiting)
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255),
    ip_address VARCHAR(50),
    success TINYINT DEFAULT 0,
    attempted_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Official documents (Bylaws, Articles of Incorporation, 501(c)(3), etc.)
CREATE TABLE IF NOT EXISTS official_documents (
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
);

-- Create indexes for better performance
CREATE INDEX idx_meetings_type_active ON meetings(meeting_type, is_active);
CREATE INDEX idx_newsletters_year_month ON newsletters(year, month);
CREATE INDEX idx_events_type ON events(event_type);
CREATE INDEX idx_events_date ON events(event_date);
CREATE INDEX idx_calendar_cache_date ON calendar_cache(event_date);
CREATE INDEX idx_audit_log_user ON audit_log(user_id);
CREATE INDEX idx_login_attempts_ip ON login_attempts(ip_address);
CREATE INDEX idx_documents_active ON official_documents(is_active, sort_order);

-- =====================================================
-- INITIAL DATA
-- =====================================================

-- Default admin user (password: changeme123)
INSERT IGNORE INTO users (username, password_hash, email, role)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@trivalleystargazers.org', 'admin');

-- Sample public meeting with presentation
INSERT IGNORE INTO meetings (meeting_type, meeting_date, meeting_time, location, location_address, description, meeting_format, presentation_topic, presenter_name, presenter_title, presentation_abstract, presenter_bio)
VALUES ('public', '2026-02-20', '19:30', 'Unitarian Universalist Church', '1893 N. Vasco Rd., Livermore', 'Monthly public meeting', 'hybrid',
    'Stars Without Nuclear Fusion -- Much of the physics without all of the confusion',
    'Dr. Kirk T. Korista',
    'Professor of Astronomy, Western Michigan University',
    'The phenomenon of nuclear fusion influences the structure and evolution of stars in many important ways, but several key physical processes in and behaviors of stars are ubiquitously mis-attributed to nuclear fusion.',
    'Dr. Kirk T. Korista is a Professor of Astronomy and Graduate Programs Advisor, Department of Physics, Western Michigan University.');

-- Sample board meeting
INSERT IGNORE INTO meetings (meeting_type, meeting_date, meeting_time, location, location_address, description, meeting_format)
VALUES ('board', '2026-02-23', '19:30', 'Video Conference', NULL, 'Board meetings are usually held using video conferencing. Members are always welcome at board meetings.', 'zoom');

-- Sample events
INSERT IGNORE INTO events (event_type, event_date, title, description, is_visible)
VALUES ('h2o', '2026-02-15', 'H2O Open House', 'Open house for the club''s dark sky site, Hidden Hill Observatory (H2O)', 1);

INSERT IGNORE INTO events (event_type, event_date, title, description, is_visible)
VALUES ('tesla', '2026-02-22', 'Tesla Vineyard Star Party', 'Member star party at Tesla Vintners', 1);

-- Default official document types
INSERT IGNORE INTO official_documents (doc_type, title, description, sort_order) VALUES
('bylaws', 'Bylaws', 'The official bylaws of the Tri-Valley Stargazers astronomy club.', 1),
('articles', 'Articles of Incorporation', 'The articles of incorporation for the Tri-Valley Stargazers.', 2),
('501c3', '501(c)(3) Status', 'Documentation of our tax-exempt status as a 501(c)(3) non-profit organization.', 3);
