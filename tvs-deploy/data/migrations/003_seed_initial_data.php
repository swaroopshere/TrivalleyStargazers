<?php
/**
 * Migration: 003_seed_initial_data
 * Description: Replace schema.sql placeholder rows with real current data;
 *              seed all newsletter records (Sep 2024 – May 2026).
 *
 * Meetings:  removes the Feb 2026 sample rows, inserts May 2026 meeting + board meeting.
 * Events:    removes the Feb 2026 sample rows, inserts Tesla (Jun 6) and Summer BBQ (Jun 20).
 * Newsletters: 20 issues inserted with INSERT IGNORE (safe if already added via admin).
 */

return new class {
    public function up(PDO $pdo): array {
        $messages = [];

        // =========================================
        // MEETINGS
        // =========================================

        // Remove only the specific placeholder rows inserted by schema.sql
        $pdo->exec("DELETE FROM meetings WHERE meeting_date IN ('2026-02-20', '2026-02-23')");
        $messages[] = 'Removed placeholder meetings from schema.sql';

        // Check whether real meetings already exist before inserting
        $existing = $pdo->query("SELECT COUNT(*) FROM meetings WHERE meeting_date IN ('2026-05-15', '2026-05-18')")->fetchColumn();

        if ((int)$existing === 0) {
            $pdo->exec("
                INSERT INTO meetings
                    (meeting_type, meeting_date, meeting_time, location, location_address,
                     meeting_format, presentation_topic, presenter_name, presenter_title,
                     presentation_abstract, presenter_bio, description, is_active)
                VALUES
                    (
                        'public', '2026-05-15', '19:30:00',
                        'Unitarian Universalist Church', '1893 N. Vasco Rd., Livermore',
                        'hybrid',
                        'NASA Mission Control',
                        'Ben Hayman',
                        'NASA Flight Controller, ISS Program',
                        'NASA''s human spaceflight programs are a global endeavor that bring together cutting edge engineering, international partnerships, and decades of operational expertise to explore low Earth orbit, the Moon, and beyond. This presentation offers an inside look at how Mission Control in Houston directs the daily operation of spacecraft such as the International Space Station (ISS), Boeing''s CST-100 Starliner, and future lunar missions under the Artemis and Moon2Mars architecture.',
                        'Ben Hayman is a NASA contractor working as a Station Power, ARticulation, Thermal, and ANalysis (SPARTAN) Front room Flight Controller (FCR) and Instructor for the International Space Station (ISS) Program, and Mechanical and Power Officer (MPO) Exploration Flight Controller providing technical expertise and insight into lunar exploration systems for the Moon to Mars (M2M) Program and Extravehicular Activity and Human Surface Mobility Program (EHP), with previous experience flying Boeing''s CST-100 Starliner spacecraft.',
                        NULL,
                        1
                    ),
                    (
                        'board', '2026-05-18', '19:30:00',
                        'Video Conference', NULL,
                        'zoom',
                        NULL, NULL, NULL, NULL, NULL,
                        'Board meetings are usually held using video conferencing. Members are always welcome at board meetings.',
                        1
                    )
            ");
            $messages[] = 'Inserted public meeting: May 15, 2026 (Ben Hayman, NASA Mission Control)';
            $messages[] = 'Inserted board meeting: May 18, 2026';
        } else {
            $messages[] = 'May 2026 meetings already exist — skipped insert';
        }

        // =========================================
        // EVENTS
        // =========================================

        // Remove only the specific placeholder rows inserted by schema.sql
        $pdo->exec("DELETE FROM events WHERE event_date IN ('2026-02-15', '2026-02-22')");
        $messages[] = 'Removed placeholder events from schema.sql';

        // Check whether real events already exist before inserting
        $existingEvents = $pdo->query("SELECT COUNT(*) FROM events WHERE event_date IN ('2026-06-06', '2026-06-20')")->fetchColumn();

        if ((int)$existingEvents === 0) {
            $pdo->exec("
                INSERT INTO events
                    (event_type, event_date, title, description, is_visible, sort_order)
                VALUES
                    (
                        'tesla', '2026-06-06',
                        'Member Star Party at Tesla Vintners',
                        'Member-only star party at Tesla Vintners, open to club members and their guests. Star party runs 8:00 PM until midnight. Use the unmarked west entrance (closest to Mines Road) to enter the winery grounds.',
                        1, 1
                    ),
                    (
                        'bbq', '2026-06-20',
                        'Summer Barbecue',
                        'Annual Summer BBQ. Set up at 6:00 p.m., dinner starts at 7:30 p.m. The club will provide hamburgers, hotdogs, and vegetarian black bean burgers.',
                        1, 2
                    )
            ");
            $messages[] = 'Inserted Tesla Vintners star party: June 6, 2026';
            $messages[] = 'Inserted Summer BBQ: June 20, 2026';
        } else {
            $messages[] = 'June 2026 events already exist — skipped insert';
        }

        // =========================================
        // NEWSLETTERS
        // 20 issues: September 2024 – May 2026.
        // INSERT IGNORE respects the UNIQUE KEY on (year, month):
        // rows already uploaded via the admin panel are left unchanged.
        // =========================================

        // Clear is_current on any existing rows so exactly one ends up current
        $pdo->exec("UPDATE newsletters SET is_current = 0");

        $newsletters = [
            // 2024 (4 issues)
            [2024,  9, 'tvsnews0924.pdf'],
            [2024, 10, 'tvsnews1024.pdf'],
            [2024, 11, 'tvsnews1124.pdf'],
            [2024, 12, 'tvsnews1224.pdf'],
            // 2025 (11 issues — no March 2025 PDF in archive)
            [2025,  1, 'tvsnews0125.pdf'],
            [2025,  2, 'tvsnews0225.pdf'],
            [2025,  4, 'tvsnews0425.pdf'],
            [2025,  5, 'tvsnews0525.pdf'],
            [2025,  6, 'tvsnews0625.pdf'],
            [2025,  7, 'tvsnews0725.pdf'],
            [2025,  8, 'tvsnews0825.pdf'],
            [2025,  9, 'tvsnews0925.pdf'],
            [2025, 10, 'tvsnews1025.pdf'],
            [2025, 11, 'tvsnews1125.pdf'],
            [2025, 12, 'tvsnews1225.pdf'],
            // 2026 (5 issues through May)
            [2026,  1, 'tvsnews0126.pdf'],
            [2026,  2, 'tvsnews0226.pdf'],
            [2026,  3, 'tvsnews0326.pdf'],
            [2026,  4, 'tvsnews0426.pdf'],
            [2026,  5, 'tvsnews0526.pdf'],
        ];

        $stmt = $pdo->prepare("
            INSERT IGNORE INTO newsletters (year, month, filename, file_path, file_type, is_current)
            VALUES (?, ?, ?, ?, 'pdf', 0)
        ");

        $inserted = 0;
        foreach ($newsletters as [$year, $month, $filename]) {
            $path = sprintf('newsletters/%d/%s', $year, $filename);
            $stmt->execute([$year, $month, $filename, $path]);
            if ($stmt->rowCount() > 0) {
                $inserted++;
            }
        }

        $skipped = count($newsletters) - $inserted;
        $messages[] = "Newsletters: inserted {$inserted}, skipped {$skipped} (already existed)";

        // Set May 2026 as the current newsletter — covers both the INSERT IGNORE
        // path (row already existed and was not modified above) and the fresh-insert path.
        $pdo->exec("UPDATE newsletters SET is_current = 1 WHERE year = 2026 AND month = 5");
        $messages[] = 'Set May 2026 as current newsletter';

        return $messages;
    }
};
