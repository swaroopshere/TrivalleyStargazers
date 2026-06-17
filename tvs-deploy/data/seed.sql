-- ============================================================
-- TVS Website Seed Data
-- ============================================================
-- Run this AFTER schema_mysql.sql on the production database.
--
-- What this does:
--   * Replaces the placeholder meetings/events from schema.sql
--     with real current data extracted from the live static site.
--   * Inserts all 20 newsletter records (Sep 2024 – May 2026).
--
-- Safe to re-run:
--   * Newsletters use INSERT IGNORE so no duplicate rows.
--   * A final UPDATE ensures exactly one is_current newsletter.
--   * Meetings/events are cleared first — do not run this on a
--     database that already has real admin-entered meetings or
--     events unless you want to reset them.
-- ============================================================

SET NAMES utf8mb4;

-- ============================================================
-- MEETINGS
-- Removes the placeholder February 2026 rows from schema.sql,
-- inserts the actual current meetings from the static site.
-- ============================================================

DELETE FROM meetings;

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
    );

-- ============================================================
-- EVENTS
-- Removes the placeholder February 2026 rows from schema.sql,
-- inserts real upcoming events from the static site.
--
-- NOT seeded (past-dated in static site — add via admin when
-- new dates are known):
--   H2O Open House      last listed as August 16, 2025
--   Winter Potluck      last listed as December 19, 2025
-- ============================================================

DELETE FROM events;

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
    );

-- ============================================================
-- NEWSLETTERS
-- 20 issues: September 2024 through May 2026.
-- March 2025 is absent from the file archive (no PDF exists).
-- Pre-2024 newsletters are not in the repository and are not
-- seeded here; add them via the admin panel if PDFs are found.
--
-- INSERT IGNORE skips rows that already exist (year+month unique).
-- The UPDATE at the top + final UPDATE ensure exactly one
-- is_current issue regardless of re-runs.
-- ============================================================

-- Clear any existing is_current flag so we set exactly one below
UPDATE newsletters SET is_current = 0;

INSERT IGNORE INTO newsletters (year, month, filename, file_path, file_type, is_current) VALUES

-- 2024 (4 issues)
(2024,  9, 'tvsnews0924.pdf', 'newsletters/2024/tvsnews0924.pdf', 'pdf', 0),
(2024, 10, 'tvsnews1024.pdf', 'newsletters/2024/tvsnews1024.pdf', 'pdf', 0),
(2024, 11, 'tvsnews1124.pdf', 'newsletters/2024/tvsnews1124.pdf', 'pdf', 0),
(2024, 12, 'tvsnews1224.pdf', 'newsletters/2024/tvsnews1224.pdf', 'pdf', 0),

-- 2025 (11 issues — no March 2025 PDF in archive)
(2025,  1, 'tvsnews0125.pdf', 'newsletters/2025/tvsnews0125.pdf', 'pdf', 0),
(2025,  2, 'tvsnews0225.pdf', 'newsletters/2025/tvsnews0225.pdf', 'pdf', 0),
(2025,  4, 'tvsnews0425.pdf', 'newsletters/2025/tvsnews0425.pdf', 'pdf', 0),
(2025,  5, 'tvsnews0525.pdf', 'newsletters/2025/tvsnews0525.pdf', 'pdf', 0),
(2025,  6, 'tvsnews0625.pdf', 'newsletters/2025/tvsnews0625.pdf', 'pdf', 0),
(2025,  7, 'tvsnews0725.pdf', 'newsletters/2025/tvsnews0725.pdf', 'pdf', 0),
(2025,  8, 'tvsnews0825.pdf', 'newsletters/2025/tvsnews0825.pdf', 'pdf', 0),
(2025,  9, 'tvsnews0925.pdf', 'newsletters/2025/tvsnews0925.pdf', 'pdf', 0),
(2025, 10, 'tvsnews1025.pdf', 'newsletters/2025/tvsnews1025.pdf', 'pdf', 0),
(2025, 11, 'tvsnews1125.pdf', 'newsletters/2025/tvsnews1125.pdf', 'pdf', 0),
(2025, 12, 'tvsnews1225.pdf', 'newsletters/2025/tvsnews1225.pdf', 'pdf', 0),

-- 2026 (5 issues through May — May is the current issue)
(2026,  1, 'tvsnews0126.pdf', 'newsletters/2026/tvsnews0126.pdf', 'pdf', 0),
(2026,  2, 'tvsnews0226.pdf', 'newsletters/2026/tvsnews0226.pdf', 'pdf', 0),
(2026,  3, 'tvsnews0326.pdf', 'newsletters/2026/tvsnews0326.pdf', 'pdf', 0),
(2026,  4, 'tvsnews0426.pdf', 'newsletters/2026/tvsnews0426.pdf', 'pdf', 0),
(2026,  5, 'tvsnews0526.pdf', 'newsletters/2026/tvsnews0526.pdf', 'pdf', 0);

-- Set May 2026 as the current newsletter.
-- This UPDATE handles both the INSERT IGNORE path (row already
-- existed and was not modified above) and the fresh-insert path.
UPDATE newsletters SET is_current = 1 WHERE year = 2026 AND month = 5;
