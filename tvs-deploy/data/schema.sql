-- TVS Website Database Schema
-- SQLite database for Tri-Valley Stargazers website

-- Users (multiple accounts)
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    email TEXT,
    role TEXT DEFAULT 'publisher',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME,
    is_active INTEGER DEFAULT 1
);

-- Site content (key-value for flexible updates)
CREATE TABLE IF NOT EXISTS site_content (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    content_key TEXT UNIQUE NOT NULL,
    content_value TEXT,
    updated_by INTEGER,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id)
);

-- Monthly meetings
CREATE TABLE IF NOT EXISTS meetings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    meeting_type TEXT NOT NULL,  -- 'public', 'board'
    meeting_date DATE NOT NULL,
    meeting_time TIME,
    location TEXT,
    location_address TEXT,
    description TEXT,
    meeting_format TEXT DEFAULT 'hybrid', -- 'in-person', 'zoom', 'hybrid'
    is_active INTEGER DEFAULT 1,
    updated_by INTEGER,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id)
);

-- Monthly presentations
CREATE TABLE IF NOT EXISTS presentations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    month INTEGER NOT NULL,
    year INTEGER NOT NULL,
    topic TEXT NOT NULL,
    presenter_name TEXT,
    presenter_title TEXT,
    abstract TEXT,
    bio TEXT,
    is_hybrid INTEGER DEFAULT 1,
    updated_by INTEGER,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(month, year),
    FOREIGN KEY (updated_by) REFERENCES users(id)
);

-- Events (H2O, Tesla, announcements)
CREATE TABLE IF NOT EXISTS events (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    event_type TEXT NOT NULL,  -- 'h2o', 'tesla', 'announcement', 'bbq', 'potluck'
    event_date DATE,
    title TEXT,
    description TEXT,
    is_visible INTEGER DEFAULT 1,
    sort_order INTEGER DEFAULT 0,
    updated_by INTEGER,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id)
);

-- Calendar cache (from groups.io)
CREATE TABLE IF NOT EXISTS calendar_cache (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    event_id TEXT UNIQUE,  -- groups.io event ID
    event_date DATE,
    event_time TEXT,
    title TEXT,
    description TEXT,
    location TEXT,
    fetched_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Newsletters
CREATE TABLE IF NOT EXISTS newsletters (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    year INTEGER NOT NULL,
    month INTEGER NOT NULL,
    filename TEXT NOT NULL,
    file_path TEXT NOT NULL,
    file_type TEXT DEFAULT 'pdf', -- 'pdf' or 'html'
    file_size INTEGER,
    uploaded_by INTEGER,
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_current INTEGER DEFAULT 0,
    UNIQUE(year, month),
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
);

-- Audit log
CREATE TABLE IF NOT EXISTS audit_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    action TEXT,
    table_name TEXT,
    record_id INTEGER,
    old_value TEXT,
    new_value TEXT,
    ip_address TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Login attempts (for rate limiting)
CREATE TABLE IF NOT EXISTS login_attempts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT,
    ip_address TEXT,
    success INTEGER DEFAULT 0,
    attempted_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_newsletters_year_month ON newsletters(year, month);
CREATE INDEX IF NOT EXISTS idx_events_type ON events(event_type);
CREATE INDEX IF NOT EXISTS idx_events_date ON events(event_date);
CREATE INDEX IF NOT EXISTS idx_calendar_cache_date ON calendar_cache(event_date);
CREATE INDEX IF NOT EXISTS idx_audit_log_user ON audit_log(user_id);
CREATE INDEX IF NOT EXISTS idx_login_attempts_ip ON login_attempts(ip_address);

-- =====================================================
-- INITIAL DATA MIGRATION
-- =====================================================

-- Default admin user (password: changeme123 - MUST be changed on first login)
-- Password hash generated with PHP password_hash('changeme123', PASSWORD_DEFAULT)
INSERT OR IGNORE INTO users (username, password_hash, email, role)
VALUES ('admin', '$2y$10$placeholder_hash_change_on_first_run', 'admin@trivalleystargazers.org', 'admin');

-- Current meeting information (from index.shtml as of January 2026)
INSERT OR IGNORE INTO meetings (meeting_type, meeting_date, meeting_time, location, location_address, description, meeting_format)
VALUES ('public', '2026-01-16', '19:30', 'Unitarian Universalist Church', '1893 N. Vasco Rd., Livermore', 'Monthly public meeting', 'hybrid');

INSERT OR IGNORE INTO meetings (meeting_type, meeting_date, meeting_time, location, location_address, description, meeting_format)
VALUES ('board', '2026-01-19', '19:30', 'Video Conference', NULL, 'Board meetings are usually held using video conferencing. Members are always welcome at board meetings.', 'zoom');

-- Current presentation (January 2026)
INSERT OR IGNORE INTO presentations (month, year, topic, presenter_name, presenter_title, abstract, bio, is_hybrid)
VALUES (1, 2026,
    'Stars Without Nuclear Fusion -- Much of the physics without all of the confusion',
    'Dr. Kirk T. Korista',
    'Professor of Astronomy and Graduate Programs Advisor, Department of Physics, Western Michigan University',
    'The phenomenon of nuclear fusion influences the structure and evolution of stars in many important ways, but several key physical processes in and behaviors of stars are ubiquitously mis-attributed to nuclear fusion. These serve as major misconceptions that lead to widespread confusion about how stars work. Nuclear fusion does not support stars against the force of gravity and gravitational collapse, nor does it generate significant radiation pressure to do so as is mysteriously invoked. In fact, radiation pressure rarely contributes significantly to supporting stars at all. In point of fact, stars do not require nuclear fusion in order to exist as hot, luminous objects that are in force balance against gravity. In order to illuminate their fundamental natures, we explore stars without nuclear processes and compare their evolutionary behavior to stars with nuclear fusion',
    'Dr. Kirk T. Korista is a Professor of Astronomy and Graduate Programs Advisor, Department of Physics, Western Michigan University.',
    1);

-- Current events
INSERT OR IGNORE INTO events (event_type, event_date, title, description, is_visible)
VALUES ('h2o', '2025-08-16', 'H2O Open House', 'Open house for the club''s dark sky site, Hidden Hill Observatory (H2O)', 1);

INSERT OR IGNORE INTO events (event_type, event_date, title, description, is_visible)
VALUES ('tesla', '2025-07-19', 'Tesla Vineyard Star Party', 'Member star party at Tesla Vintners', 1);

-- =====================================================
-- NEWSLETTER ARCHIVE MIGRATION
-- All newsletters from 1996-2026
-- =====================================================

-- 2026 PDF Newsletters
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type, is_current) VALUES (2026, 1, 'tvsnews0126.pdf', 'newsletters/2026/tvsnews0126.pdf', 'pdf', 1);

-- 2025 PDF Newsletters
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2025, 12, 'tvsnews1225.pdf', 'newsletters/2025/tvsnews1225.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2025, 11, 'tvsnews1125.pdf', 'newsletters/2025/tvsnews1125.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2025, 10, 'tvsnews1025.pdf', 'newsletters/2025/tvsnews1025.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2025, 9, 'tvsnews0925.pdf', 'newsletters/2025/tvsnews0925.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2025, 8, 'tvsnews0825.pdf', 'newsletters/2025/tvsnews0825.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2025, 7, 'tvsnews0725.pdf', 'newsletters/2025/tvsnews0725.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2025, 6, 'tvsnews0625.pdf', 'newsletters/2025/tvsnews0625.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2025, 5, 'tvsnews0525.pdf', 'newsletters/2025/tvsnews0525.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2025, 4, 'tvsnews0425.pdf', 'newsletters/2025/tvsnews0425.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2025, 3, 'tvsnews0325.pdf', 'newsletters/2025/tvsnews0325.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2025, 2, 'tvsnews0225.pdf', 'newsletters/2025/tvsnews0225.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2025, 1, 'tvsnews0125.pdf', 'newsletters/2025/tvsnews0125.pdf', 'pdf');

-- 2024 PDF Newsletters
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2024, 12, 'tvsnews1224.pdf', 'newsletters/2024/tvsnews1224.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2024, 11, 'tvsnews1124.pdf', 'newsletters/2024/tvsnews1124.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2024, 10, 'tvsnews1024.pdf', 'newsletters/2024/tvsnews1024.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2024, 9, 'tvsnews0924.pdf', 'newsletters/2024/tvsnews0924.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2024, 8, 'tvsnews0824.pdf', 'newsletters/2024/tvsnews0824.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2024, 7, 'tvsnews0724.pdf', 'newsletters/2024/tvsnews0724.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2024, 6, 'tvsnews0624.pdf', 'newsletters/2024/tvsnews0624.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2024, 5, 'tvsnews0524.pdf', 'newsletters/2024/tvsnews0524.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2024, 4, 'tvsnews0424.pdf', 'newsletters/2024/tvsnews0424.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2024, 3, 'tvsnews0324.pdf', 'newsletters/2024/tvsnews0324.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2024, 2, 'tvsnews0224.pdf', 'newsletters/2024/tvsnews0224.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2024, 1, 'tvsnews0124.pdf', 'newsletters/2024/tvsnews0124.pdf', 'pdf');

-- 2023 PDF Newsletters
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2023, 12, 'tvsnews1223.pdf', 'newsletters/2023/tvsnews1223.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2023, 11, 'tvsnews1123.pdf', 'newsletters/2023/tvsnews1123.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2023, 10, 'tvsnews1023.pdf', 'newsletters/2023/tvsnews1023.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2023, 9, 'tvsnews0923.pdf', 'newsletters/2023/tvsnews0923.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2023, 8, 'tvsnews0823.pdf', 'newsletters/2023/tvsnews0823.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2023, 7, 'tvsnews0723.pdf', 'newsletters/2023/tvsnews0723.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2023, 6, 'tvsnews0623.pdf', 'newsletters/2023/tvsnews0623.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2023, 5, 'tvsnews0523.pdf', 'newsletters/2023/tvsnews0523.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2023, 4, 'tvsnews0423.pdf', 'newsletters/2023/tvsnews0423.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2023, 3, 'tvsnews0323.pdf', 'newsletters/2023/tvsnews0323.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2023, 2, 'tvsnews0223.pdf', 'newsletters/2023/tvsnews0223.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2023, 1, 'tvsnews0123.pdf', 'newsletters/2023/tvsnews0123.pdf', 'pdf');

-- 2022 PDF Newsletters
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2022, 12, 'tvsnews1222.pdf', 'newsletters/2022/tvsnews1222.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2022, 11, 'tvsnews1122.pdf', 'newsletters/2022/tvsnews1122.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2022, 10, 'tvsnews1022.pdf', 'newsletters/2022/tvsnews1022.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2022, 9, 'tvsnews0922.pdf', 'newsletters/2022/tvsnews0922.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2022, 8, 'tvsnews0822.pdf', 'newsletters/2022/tvsnews0822.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2022, 7, 'tvsnews0722.pdf', 'newsletters/2022/tvsnews0722.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2022, 6, 'tvsnews0622.pdf', 'newsletters/2022/tvsnews0622.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2022, 5, 'tvsnews0522.pdf', 'newsletters/2022/tvsnews0522.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2022, 4, 'tvsnews0422.pdf', 'newsletters/2022/tvsnews0422.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2022, 3, 'tvsnews0322.pdf', 'newsletters/2022/tvsnews0322.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2022, 2, 'tvsnews0222.pdf', 'newsletters/2022/tvsnews0222.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2022, 1, 'tvsnews0122.pdf', 'newsletters/2022/tvsnews0122.pdf', 'pdf');

-- 2021 PDF Newsletters
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2021, 12, 'tvsnews1221.pdf', 'newsletters/2021/tvsnews1221.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2021, 11, 'tvsnews1121.pdf', 'newsletters/2021/tvsnews1121.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2021, 10, 'tvsnews1021.pdf', 'newsletters/2021/tvsnews1021.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2021, 9, 'tvsnews0921.pdf', 'newsletters/2021/tvsnews0921.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2021, 8, 'tvsnews0821.pdf', 'newsletters/2021/tvsnews0821.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2021, 7, 'tvsnews0721.pdf', 'newsletters/2021/tvsnews0721.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2021, 6, 'tvsnews0621.pdf', 'newsletters/2021/tvsnews0621.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2021, 5, 'tvsnews0521.pdf', 'newsletters/2021/tvsnews0521.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2021, 4, 'tvsnews0421.pdf', 'newsletters/2021/tvsnews0421.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2021, 3, 'tvsnews0321.pdf', 'newsletters/2021/tvsnews0321.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2021, 2, 'tvsnews0221.pdf', 'newsletters/2021/tvsnews0221.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2021, 1, 'tvsnews0121.pdf', 'newsletters/2021/tvsnews0121.pdf', 'pdf');

-- 2020 PDF Newsletters
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2020, 12, 'tvsnews1220.pdf', 'newsletters/2020/tvsnews1220.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2020, 11, 'tvsnews1120.pdf', 'newsletters/2020/tvsnews1120.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2020, 10, 'tvsnews1020.pdf', 'newsletters/2020/tvsnews1020.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2020, 9, 'tvsnews0920.pdf', 'newsletters/2020/tvsnews0920.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2020, 8, 'tvsnews0820.pdf', 'newsletters/2020/tvsnews0820.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2020, 7, 'tvsnews0720.pdf', 'newsletters/2020/tvsnews0720.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2020, 6, 'tvsnews0620.pdf', 'newsletters/2020/tvsnews0620.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2020, 5, 'tvsnews0520.pdf', 'newsletters/2020/tvsnews0520.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2020, 4, 'tvsnews0420.pdf', 'newsletters/2020/tvsnews0420.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2020, 3, 'tvsnews0320.pdf', 'newsletters/2020/tvsnews0320.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2020, 2, 'tvsnews0220.pdf', 'newsletters/2020/tvsnews0220.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2020, 1, 'tvsnews0120.pdf', 'newsletters/2020/tvsnews0120.pdf', 'pdf');

-- 2019 PDF Newsletters
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2019, 12, 'tvsnews1219.pdf', 'newsletters/2019/tvsnews1219.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2019, 11, 'tvsnews1119.pdf', 'newsletters/2019/tvsnews1119.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2019, 10, 'tvsnews1019.pdf', 'newsletters/2019/tvsnews1019.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2019, 9, 'tvsnews0919.pdf', 'newsletters/2019/tvsnews0919.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2019, 8, 'tvsnews0819.pdf', 'newsletters/2019/tvsnews0819.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2019, 7, 'tvsnews0719.pdf', 'newsletters/2019/tvsnews0719.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2019, 6, 'tvsnews0619.pdf', 'newsletters/2019/tvsnews0619.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2019, 5, 'tvsnews0519.pdf', 'newsletters/2019/tvsnews0519.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2019, 4, 'tvsnews0419.pdf', 'newsletters/2019/tvsnews0419.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2019, 3, 'tvsnews0319.pdf', 'newsletters/2019/tvsnews0319.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2019, 2, 'tvsnews0219.pdf', 'newsletters/2019/tvsnews0219.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2019, 1, 'tvsnews0119.pdf', 'newsletters/2019/tvsnews0119.pdf', 'pdf');

-- 2018 PDF Newsletters
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2018, 12, 'tvsnews1218.pdf', 'newsletters/2018/tvsnews1218.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2018, 11, 'tvsnews1118.pdf', 'newsletters/2018/tvsnews1118.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2018, 10, 'tvsnews1018.pdf', 'newsletters/2018/tvsnews1018.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2018, 9, 'tvsnews0918.pdf', 'newsletters/2018/tvsnews0918.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2018, 8, 'tvsnews0818.pdf', 'newsletters/2018/tvsnews0818.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2018, 7, 'tvsnews0718.pdf', 'newsletters/2018/tvsnews0718.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2018, 6, 'tvsnews0618.pdf', 'newsletters/2018/tvsnews0618.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2018, 5, 'tvsnews0518.pdf', 'newsletters/2018/tvsnews0518.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2018, 4, 'tvsnews0418.pdf', 'newsletters/2018/tvsnews0418.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2018, 3, 'tvsnews0318.pdf', 'newsletters/2018/tvsnews0318.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2018, 2, 'tvsnews0218.pdf', 'newsletters/2018/tvsnews0218.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2018, 1, 'tvsnews0118.pdf', 'newsletters/2018/tvsnews0118.pdf', 'pdf');

-- 2017 PDF Newsletters
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2017, 12, 'tvsnews1217.pdf', 'newsletters/2017/tvsnews1217.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2017, 11, 'tvsnews1117.pdf', 'newsletters/2017/tvsnews1117.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2017, 10, 'tvsnews1017.pdf', 'newsletters/2017/tvsnews1017.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2017, 9, 'tvsnews0917.pdf', 'newsletters/2017/tvsnews0917.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2017, 8, 'tvsnews0817.pdf', 'newsletters/2017/tvsnews0817.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2017, 7, 'tvsnews0717.pdf', 'newsletters/2017/tvsnews0717.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2017, 6, 'tvsnews0617.pdf', 'newsletters/2017/tvsnews0617.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2017, 5, 'tvsnews0517.pdf', 'newsletters/2017/tvsnews0517.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2017, 4, 'tvsnews0417.pdf', 'newsletters/2017/tvsnews0417.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2017, 3, 'tvsnews0317.pdf', 'newsletters/2017/tvsnews0317.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2017, 2, 'tvsnews0217.pdf', 'newsletters/2017/tvsnews0217.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2017, 1, 'tvsnews0117.pdf', 'newsletters/2017/tvsnews0117.pdf', 'pdf');

-- 2016 PDF Newsletters
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2016, 12, 'tvsnews1216.pdf', 'newsletters/2016/tvsnews1216.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2016, 11, 'tvsnews1116.pdf', 'newsletters/2016/tvsnews1116.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2016, 10, 'tvsnews1016.pdf', 'newsletters/2016/tvsnews1016.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2016, 9, 'tvsnews0916.pdf', 'newsletters/2016/tvsnews0916.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2016, 8, 'tvsnews0816.pdf', 'newsletters/2016/tvsnews0816.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2016, 7, 'tvsnews0716.pdf', 'newsletters/2016/tvsnews0716.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2016, 6, 'tvsnews0616.pdf', 'newsletters/2016/tvsnews0616.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2016, 5, 'tvsnews0516.pdf', 'newsletters/2016/tvsnews0516.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2016, 4, 'tvsnews0416.pdf', 'newsletters/2016/tvsnews0416.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2016, 3, 'tvsnews0316.pdf', 'newsletters/2016/tvsnews0316.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2016, 2, 'tvsnews0216.pdf', 'newsletters/2016/tvsnews0216.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2016, 1, 'tvsnews0116.pdf', 'newsletters/2016/tvsnews0116.pdf', 'pdf');

-- 2015 PDF Newsletters
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2015, 12, 'tvsnews1215.pdf', 'newsletters/2015/tvsnews1215.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2015, 11, 'tvsnews1115.pdf', 'newsletters/2015/tvsnews1115.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2015, 10, 'tvsnews1015.pdf', 'newsletters/2015/tvsnews1015.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2015, 9, 'tvsnews0915.pdf', 'newsletters/2015/tvsnews0915.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2015, 8, 'tvsnews0815.pdf', 'newsletters/2015/tvsnews0815.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2015, 7, 'tvsnews0715.pdf', 'newsletters/2015/tvsnews0715.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2015, 6, 'tvsnews0615.pdf', 'newsletters/2015/tvsnews0615.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2015, 5, 'tvsnews0515.pdf', 'newsletters/2015/tvsnews0515.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2015, 4, 'tvsnews0415.pdf', 'newsletters/2015/tvsnews0415.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2015, 3, 'tvsnews0315.pdf', 'newsletters/2015/tvsnews0315.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2015, 2, 'tvsnews0215.pdf', 'newsletters/2015/tvsnews0215.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2015, 1, 'tvsnews0115.pdf', 'newsletters/2015/tvsnews0115.pdf', 'pdf');

-- 2014 PDF Newsletters
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2014, 12, 'tvsnews1214.pdf', 'newsletters/2014/tvsnews1214.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2014, 11, 'tvsnews1114.pdf', 'newsletters/2014/tvsnews1114.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2014, 10, 'tvsnews1014.pdf', 'newsletters/2014/tvsnews1014.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2014, 9, 'tvsnews0914.pdf', 'newsletters/2014/tvsnews0914.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2014, 8, 'tvsnews0814.pdf', 'newsletters/2014/tvsnews0814.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2014, 7, 'tvsnews0714.pdf', 'newsletters/2014/tvsnews0714.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2014, 6, 'tvsnews0614.pdf', 'newsletters/2014/tvsnews0614.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2014, 5, 'tvsnews0514.pdf', 'newsletters/2014/tvsnews0514.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2014, 4, 'tvsnews0414.pdf', 'newsletters/2014/tvsnews0414.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2014, 3, 'tvsnews0314.pdf', 'newsletters/2014/tvsnews0314.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2014, 2, 'tvsnews0214.pdf', 'newsletters/2014/tvsnews0214.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2014, 1, 'tvsnews0114.pdf', 'newsletters/2014/tvsnews0114.pdf', 'pdf');

-- 2013 PDF Newsletters
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2013, 12, 'tvsnews1213.pdf', 'newsletters/2013/tvsnews1213.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2013, 11, 'tvsnews1113.pdf', 'newsletters/2013/tvsnews1113.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2013, 10, 'tvsnews1013.pdf', 'newsletters/2013/tvsnews1013.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2013, 9, 'tvsnews0913.pdf', 'newsletters/2013/tvsnews0913.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2013, 8, 'tvsnews0813.pdf', 'newsletters/2013/tvsnews0813.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2013, 7, 'tvsnews0713.pdf', 'newsletters/2013/tvsnews0713.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2013, 6, 'tvsnews0613.pdf', 'newsletters/2013/tvsnews0613.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2013, 5, 'tvsnews0513.pdf', 'newsletters/2013/tvsnews0513.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2013, 4, 'tvsnews0413.pdf', 'newsletters/2013/tvsnews0413.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2013, 3, 'tvsnews0313.pdf', 'newsletters/2013/tvsnews0313.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2013, 2, 'tvsnews0213.pdf', 'newsletters/2013/tvsnews0213.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2013, 1, 'tvsnews0113.pdf', 'newsletters/2013/tvsnews0113.pdf', 'pdf');

-- 2012 PDF Newsletters
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2012, 12, 'tvsnews1212.pdf', 'newsletters/2012/tvsnews1212.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2012, 11, 'tvsnews1112.pdf', 'newsletters/2012/tvsnews1112.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2012, 10, 'tvsnews1012.pdf', 'newsletters/2012/tvsnews1012.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2012, 9, 'tvsnews0912.pdf', 'newsletters/2012/tvsnews0912.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2012, 8, 'tvsnews0812.pdf', 'newsletters/2012/tvsnews0812.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2012, 7, 'tvsnews0712.pdf', 'newsletters/2012/tvsnews0712.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2012, 6, 'tvsnews0612.pdf', 'newsletters/2012/tvsnews0612.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2012, 5, 'tvsnews0512.pdf', 'newsletters/2012/tvsnews0512.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2012, 4, 'tvsnews0412.pdf', 'newsletters/2012/tvsnews0412.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2012, 3, 'tvsnews0312.pdf', 'newsletters/2012/tvsnews0312.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2012, 2, 'tvsnews0212.pdf', 'newsletters/2012/tvsnews0212.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2012, 1, 'tvsnews0112.pdf', 'newsletters/2012/tvsnews0112.pdf', 'pdf');

-- 2011 PDF Newsletters
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2011, 12, 'tvsnews1211.pdf', 'newsletters/2011/tvsnews1211.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2011, 11, 'tvsnews1111.pdf', 'newsletters/2011/tvsnews1111.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2011, 10, 'tvsnews1011.pdf', 'newsletters/2011/tvsnews1011.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2011, 9, 'tvsnews0911.pdf', 'newsletters/2011/tvsnews0911.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2011, 8, 'tvsnews0811.pdf', 'newsletters/2011/tvsnews0811.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2011, 7, 'tvsnews0711.pdf', 'newsletters/2011/tvsnews0711.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2011, 6, 'tvsnews0611.pdf', 'newsletters/2011/tvsnews0611.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2011, 5, 'tvsnews0511.pdf', 'newsletters/2011/tvsnews0511.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2011, 4, 'tvsnews0411.pdf', 'newsletters/2011/tvsnews0411.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2011, 3, 'tvsnews0311.pdf', 'newsletters/2011/tvsnews0311.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2011, 2, 'tvsnews0211.pdf', 'newsletters/2011/tvsnews0211.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2011, 1, 'tvsnews0111.pdf', 'newsletters/2011/tvsnews0111.pdf', 'pdf');

-- 2010 PDF Newsletters
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2010, 12, 'tvsnews1210.pdf', 'newsletters/2010/tvsnews1210.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2010, 11, 'tvsnews1110.pdf', 'newsletters/2010/tvsnews1110.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2010, 10, 'tvsnews1010.pdf', 'newsletters/2010/tvsnews1010.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2010, 9, 'tvsnews0910.pdf', 'newsletters/2010/tvsnews0910.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2010, 8, 'tvsnews0810.pdf', 'newsletters/2010/tvsnews0810.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2010, 7, 'tvsnews0710.pdf', 'newsletters/2010/tvsnews0710.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2010, 6, 'tvsnews0610.pdf', 'newsletters/2010/tvsnews0610.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2010, 5, 'tvsnews0510.pdf', 'newsletters/2010/tvsnews0510.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2010, 4, 'tvsnews0410.pdf', 'newsletters/2010/tvsnews0410.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2010, 3, 'tvsnews0310.pdf', 'newsletters/2010/tvsnews0310.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2010, 2, 'tvsnews0210.pdf', 'newsletters/2010/tvsnews0210.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2010, 1, 'tvsnews0110.pdf', 'newsletters/2010/tvsnews0110.pdf', 'pdf');

-- 2009 PDF Newsletters
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2009, 12, 'tvsnews1209.pdf', 'newsletters/2009/tvsnews1209.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2009, 11, 'tvsnews1109.pdf', 'newsletters/2009/tvsnews1109.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2009, 10, 'tvsnews1009.pdf', 'newsletters/2009/tvsnews1009.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2009, 9, 'tvsnews0909.pdf', 'newsletters/2009/tvsnews0909.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2009, 8, 'tvsnews0809.pdf', 'newsletters/2009/tvsnews0809.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2009, 7, 'tvsnews0709.pdf', 'newsletters/2009/tvsnews0709.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2009, 6, 'tvsnews0609.pdf', 'newsletters/2009/tvsnews0609.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2009, 5, 'tvsnews0509.pdf', 'newsletters/2009/tvsnews0509.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2009, 4, 'tvsnews0409.pdf', 'newsletters/2009/tvsnews0409.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2009, 3, 'tvsnews0309.pdf', 'newsletters/2009/tvsnews0309.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2009, 2, 'tvsnews0209.pdf', 'newsletters/2009/tvsnews0209.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2009, 1, 'tvsnews0109.pdf', 'newsletters/2009/tvsnews0109.pdf', 'pdf');

-- 2008 PDF Newsletters
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2008, 12, 'tvsnews1208.pdf', 'newsletters/2008/tvsnews1208.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2008, 11, 'tvsnews1108.pdf', 'newsletters/2008/tvsnews1108.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2008, 10, 'tvsnews1008.pdf', 'newsletters/2008/tvsnews1008.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2008, 9, 'tvsnews0908.pdf', 'newsletters/2008/tvsnews0908.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2008, 8, 'tvsnews0808.pdf', 'newsletters/2008/tvsnews0808.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2008, 7, 'tvsnews0708.pdf', 'newsletters/2008/tvsnews0708.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2008, 6, 'tvsnews0608.pdf', 'newsletters/2008/tvsnews0608.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2008, 5, 'tvsnews0508.pdf', 'newsletters/2008/tvsnews0508.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2008, 4, 'tvsnews0408.pdf', 'newsletters/2008/tvsnews0408.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2008, 3, 'tvsnews0308.pdf', 'newsletters/2008/tvsnews0308.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2008, 2, 'tvsnews0208.pdf', 'newsletters/2008/tvsnews0208.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2008, 1, 'tvsnews0108.pdf', 'newsletters/2008/tvsnews0108.pdf', 'pdf');

-- 2007 PDF Newsletters
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2007, 12, 'tvsnews1207.pdf', 'newsletters/2007/tvsnews1207.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2007, 11, 'tvsnews1107.pdf', 'newsletters/2007/tvsnews1107.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2007, 10, 'tvsnews1007.pdf', 'newsletters/2007/tvsnews1007.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2007, 9, 'tvsnews0907.pdf', 'newsletters/2007/tvsnews0907.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2007, 8, 'tvsnews0807.pdf', 'newsletters/2007/tvsnews0807.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2007, 7, 'tvsnews0707.pdf', 'newsletters/2007/tvsnews0707.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2007, 6, 'tvsnews0607.pdf', 'newsletters/2007/tvsnews0607.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2007, 5, 'tvsnews0507.pdf', 'newsletters/2007/tvsnews0507.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2007, 4, 'tvsnews0407.pdf', 'newsletters/2007/tvsnews0407.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2007, 3, 'tvsnews0307.pdf', 'newsletters/2007/tvsnews0307.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2007, 2, 'tvsnews0207.pdf', 'newsletters/2007/tvsnews0207.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2007, 1, 'tvsnews0107.pdf', 'newsletters/2007/tvsnews0107.pdf', 'pdf');

-- 2006 PDF Newsletters
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2006, 12, 'tvsnews1206.pdf', 'newsletters/2006/tvsnews1206.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2006, 11, 'tvsnews1106.pdf', 'newsletters/2006/tvsnews1106.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2006, 10, 'tvsnews1006.pdf', 'newsletters/2006/tvsnews1006.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2006, 9, 'tvsnews0906.pdf', 'newsletters/2006/tvsnews0906.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2006, 8, 'tvsnews0806.pdf', 'newsletters/2006/tvsnews0806.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2006, 7, 'tvsnews0706.pdf', 'newsletters/2006/tvsnews0706.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2006, 6, 'tvsnews0606.pdf', 'newsletters/2006/tvsnews0606.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2006, 5, 'tvsnews0506.pdf', 'newsletters/2006/tvsnews0506.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2006, 4, 'tvsnews0406.pdf', 'newsletters/2006/tvsnews0406.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2006, 3, 'tvsnews0306.pdf', 'newsletters/2006/tvsnews0306.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2006, 2, 'tvsnews0206.pdf', 'newsletters/2006/tvsnews0206.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2006, 1, 'tvsnews0106.pdf', 'newsletters/2006/tvsnews0106.pdf', 'pdf');

-- 2005 PDF Newsletters
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2005, 12, 'tvsnews1205.pdf', 'newsletters/2005/tvsnews1205.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2005, 11, 'tvsnews1105.pdf', 'newsletters/2005/tvsnews1105.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2005, 10, 'tvsnews1005.pdf', 'newsletters/2005/tvsnews1005.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2005, 9, 'tvsnews0905.pdf', 'newsletters/2005/tvsnews0905.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2005, 8, 'tvsnews0805.pdf', 'newsletters/2005/tvsnews0805.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2005, 7, 'tvsnews0705.pdf', 'newsletters/2005/tvsnews0705.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2005, 6, 'tvsnews0605.pdf', 'newsletters/2005/tvsnews0605.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2005, 5, 'tvsnews0505.pdf', 'newsletters/2005/tvsnews0505.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2005, 4, 'tvsnews0405.pdf', 'newsletters/2005/tvsnews0405.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2005, 3, 'tvsnews0305.pdf', 'newsletters/2005/tvsnews0305.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2005, 2, 'tvsnews0205.pdf', 'newsletters/2005/tvsnews0205.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2005, 1, 'tvsnews0105.pdf', 'newsletters/2005/tvsnews0105.pdf', 'pdf');

-- 2004 PDF Newsletters
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2004, 12, 'tvsnews1204.pdf', 'newsletters/2004/tvsnews1204.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2004, 11, 'tvsnews1104.pdf', 'newsletters/2004/tvsnews1104.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2004, 10, 'tvsnews1004.pdf', 'newsletters/2004/tvsnews1004.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2004, 9, 'tvsnews0904.pdf', 'newsletters/2004/tvsnews0904.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2004, 8, 'tvsnews0804.pdf', 'newsletters/2004/tvsnews0804.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2004, 7, 'tvsnews0704.pdf', 'newsletters/2004/tvsnews0704.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2004, 6, 'tvsnews0604.pdf', 'newsletters/2004/tvsnews0604.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2004, 5, 'tvsnews0504.pdf', 'newsletters/2004/tvsnews0504.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2004, 4, 'tvsnews0404.pdf', 'newsletters/2004/tvsnews0404.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2004, 3, 'tvsnews0304.pdf', 'newsletters/2004/tvsnews0304.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2004, 2, 'tvsnews0204.pdf', 'newsletters/2004/tvsnews0204.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2004, 1, 'tvsnews0104.pdf', 'newsletters/2004/tvsnews0104.pdf', 'pdf');

-- 2003 PDF Newsletters
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2003, 12, 'tvsnews1203.pdf', 'newsletters/2003/tvsnews1203.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2003, 11, 'tvsnews1103.pdf', 'newsletters/2003/tvsnews1103.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2003, 10, 'tvsnews1003.pdf', 'newsletters/2003/tvsnews1003.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2003, 9, 'tvsnews0903.pdf', 'newsletters/2003/tvsnews0903.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2003, 8, 'tvsnews0803.pdf', 'newsletters/2003/tvsnews0803.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2003, 7, 'tvsnews0703.pdf', 'newsletters/2003/tvsnews0703.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2003, 6, 'tvsnews0603.pdf', 'newsletters/2003/tvsnews0603.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2003, 5, 'tvsnews0503.pdf', 'newsletters/2003/tvsnews0503.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2003, 4, 'tvsnews0403.pdf', 'newsletters/2003/tvsnews0403.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2003, 3, 'tvsnews0303.pdf', 'newsletters/2003/tvsnews0303.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2003, 2, 'tvsnews0203.pdf', 'newsletters/2003/tvsnews0203.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2003, 1, 'tvsnews0103.pdf', 'newsletters/2003/tvsnews0103.pdf', 'pdf');

-- 2002 PDF Newsletters
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2002, 12, 'tvsnews1202.pdf', 'newsletters/2002/tvsnews1202.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2002, 11, 'tvsnews1102.pdf', 'newsletters/2002/tvsnews1102.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2002, 10, 'tvsnews1002.pdf', 'newsletters/2002/tvsnews1002.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2002, 9, 'tvsnews0902.pdf', 'newsletters/2002/tvsnews0902.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2002, 8, 'tvsnews0802.pdf', 'newsletters/2002/tvsnews0802.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2002, 7, 'tvsnews0702.pdf', 'newsletters/2002/tvsnews0702.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2002, 6, 'tvsnews0602.pdf', 'newsletters/2002/tvsnews0602.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2002, 5, 'tvsnews0502.pdf', 'newsletters/2002/tvsnews0502.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2002, 4, 'tvsnews0402.pdf', 'newsletters/2002/tvsnews0402.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2002, 3, 'tvsnews0302.pdf', 'newsletters/2002/tvsnews0302.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2002, 2, 'tvsnews0202.pdf', 'newsletters/2002/tvsnews0202.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2002, 1, 'tvsnews0102.pdf', 'newsletters/2002/tvsnews0102.pdf', 'pdf');

-- 2001 PDF Newsletters (Sept-Dec are PDF, earlier are HTML)
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2001, 12, 'tvsnews1201.pdf', 'newsletters/2001/tvsnews1201.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2001, 11, 'tvsnews1101.pdf', 'newsletters/2001/tvsnews1101.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2001, 10, 'tvsnews1001.pdf', 'newsletters/2001/tvsnews1001.pdf', 'pdf');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2001, 9, 'tvsnews0901.pdf', 'newsletters/2001/tvsnews0901.pdf', 'pdf');

-- 2001 HTML Newsletters (Jan-Aug)
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2001, 8, 'index.html', 'newsletters/2001/0801/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2001, 7, 'index.html', 'newsletters/2001/0701/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2001, 6, 'index.html', 'newsletters/2001/0601/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2001, 5, 'index.html', 'newsletters/2001/0501/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2001, 4, 'index.html', 'newsletters/2001/0401/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2001, 3, 'index.html', 'newsletters/2001/0301/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2001, 2, 'index.html', 'newsletters/2001/0201/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2001, 1, 'index.html', 'newsletters/2001/0101/index.html', 'html');

-- 2000 HTML Newsletters
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2000, 12, 'index.html', 'newsletters/2000/1200/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2000, 11, 'index.html', 'newsletters/2000/1100/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2000, 10, 'index.html', 'newsletters/2000/1000/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2000, 9, 'index.html', 'newsletters/2000/0900/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2000, 8, 'index.html', 'newsletters/2000/0800/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2000, 7, 'index.html', 'newsletters/2000/0700/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2000, 6, 'index.html', 'newsletters/2000/0600/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2000, 5, 'index.html', 'newsletters/2000/0500/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2000, 4, 'index.html', 'newsletters/2000/0400/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2000, 3, 'index.html', 'newsletters/2000/0300/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2000, 2, 'index.html', 'newsletters/2000/0200/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (2000, 1, 'index.html', 'newsletters/2000/0100/index.html', 'html');

-- 1999 HTML Newsletters
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1999, 12, 'index.html', 'newsletters/1999/1299/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1999, 11, 'index.html', 'newsletters/1999/1199/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1999, 10, 'index.html', 'newsletters/1999/1099/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1999, 9, 'index.html', 'newsletters/1999/0999/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1999, 8, 'index.html', 'newsletters/1999/0899/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1999, 7, 'index.html', 'newsletters/1999/0799/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1999, 6, 'index.html', 'newsletters/1999/0699/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1999, 5, 'index.html', 'newsletters/1999/0599/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1999, 4, 'index.html', 'newsletters/1999/0499/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1999, 3, 'index.html', 'newsletters/1999/0399/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1999, 2, 'index.html', 'newsletters/1999/0299/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1999, 1, 'index.html', 'newsletters/1999/0199/index.html', 'html');

-- 1998 HTML Newsletters
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1998, 12, 'index.html', 'newsletters/1998/1298/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1998, 11, 'index.html', 'newsletters/1998/1198/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1998, 10, 'index.html', 'newsletters/1998/1098/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1998, 9, 'index.html', 'newsletters/1998/0998/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1998, 8, 'index.html', 'newsletters/1998/0898/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1998, 7, 'index.html', 'newsletters/1998/0798/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1998, 6, 'index.html', 'newsletters/1998/0698/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1998, 5, 'index.html', 'newsletters/1998/0598/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1998, 4, 'index.html', 'newsletters/1998/0498/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1998, 3, 'index.html', 'newsletters/1998/0398/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1998, 2, 'index.html', 'newsletters/1998/0298/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1998, 1, 'index.html', 'newsletters/1998/0198/index.html', 'html');

-- 1997 HTML Newsletters
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1997, 12, 'index.html', 'newsletters/1997/1297/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1997, 11, 'index.html', 'newsletters/1997/1197/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1997, 10, 'index.html', 'newsletters/1997/1097/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1997, 9, 'index.html', 'newsletters/1997/0997/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1997, 8, 'index.html', 'newsletters/1997/0897/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1997, 7, 'index.html', 'newsletters/1997/0797/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1997, 6, 'index.html', 'newsletters/1997/0697/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1997, 5, 'index.html', 'newsletters/1997/0597/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1997, 4, 'index.html', 'newsletters/1997/0497/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1997, 3, 'index.html', 'newsletters/1997/0397/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1997, 2, 'index.html', 'newsletters/1997/0297/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1997, 1, 'index.html', 'newsletters/1997/0197/index.html', 'html');

-- 1996 HTML Newsletters (March-December)
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1996, 12, 'index.html', 'newsletters/1996/1296/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1996, 11, 'index.html', 'newsletters/1996/1196/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1996, 10, 'index.html', 'newsletters/1996/1096/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1996, 9, 'index.html', 'newsletters/1996/0996/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1996, 8, 'index.html', 'newsletters/1996/0896/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1996, 7, 'index.html', 'newsletters/1996/0796/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1996, 6, 'index.html', 'newsletters/1996/0696/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1996, 5, 'index.html', 'newsletters/1996/0596/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1996, 4, 'index.html', 'newsletters/1996/0496/index.html', 'html');
INSERT OR IGNORE INTO newsletters (year, month, filename, file_path, file_type) VALUES (1996, 3, 'index.html', 'newsletters/1996/0396/index.html', 'html');
