<?php
/**
 * TVS Calendar Sync API
 *
 * Fetches events from groups.io calendar and stores in database cache.
 * Can be called via cron job or manually from admin panel.
 *
 * This replaces the Node.js build-calendar.js script.
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

/**
 * Main sync function
 */
function syncGroupsIoCalendar(): array {
    try {
        // Try to fetch from groups.io API
        $events = fetchGroupsIoEvents();

        if (empty($events)) {
            // Fall back to reading existing calendar-data.json
            $events = readLocalCalendarData();
        }

        if (empty($events)) {
            return ['success' => false, 'error' => 'No events found', 'count' => 0];
        }

        // Store events in database
        $count = storeEvents($events);

        return ['success' => true, 'count' => $count];
    } catch (Exception $e) {
        error_log('Calendar sync failed: ' . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage(), 'count' => 0];
    }
}

/**
 * Fetch events from groups.io API
 */
function fetchGroupsIoEvents(): array {
    $apiKey = GROUPS_IO_API_KEY;

    if (empty($apiKey)) {
        // No API key configured, return empty
        return [];
    }

    $groupName = GROUPS_IO_GROUP;
    $url = "https://api.groups.io/v1/group/{$groupName}/events";

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => [
                "Authorization: Bearer {$apiKey}",
                'Accept: application/json'
            ],
            'timeout' => 30
        ]
    ]);

    $response = @file_get_contents($url, false, $context);

    if ($response === false) {
        return [];
    }

    $data = json_decode($response, true);

    if (!isset($data['events']) || !is_array($data['events'])) {
        return [];
    }

    return array_map(function($event) {
        return [
            'event_id' => $event['id'] ?? uniqid(),
            'title' => $event['title'] ?? 'Untitled Event',
            'event_date' => $event['start_date'] ?? null,
            'event_time' => $event['start_time'] ?? null,
            'description' => $event['description'] ?? '',
            'location' => $event['location'] ?? ''
        ];
    }, $data['events']);
}

/**
 * Read from local calendar-data.json file (fallback)
 */
function readLocalCalendarData(): array {
    $filePath = ROOT_PATH . '/calendar-data.json';

    if (!file_exists($filePath)) {
        return [];
    }

    $content = file_get_contents($filePath);
    $data = json_decode($content, true);

    if (!is_array($data)) {
        return [];
    }

    $events = [];

    foreach ($data as $event) {
        // Parse the event format from calendar-data.json
        $events[] = [
            'event_id' => md5($event['title'] . ($event['startDate'] ?? '')),
            'title' => $event['title'] ?? 'Star Party',
            'event_date' => isset($event['startDate']) ? date('Y-m-d', strtotime($event['startDate'])) : null,
            'event_time' => isset($event['startDate']) ? date('H:i:s', strtotime($event['startDate'])) : null,
            'description' => $event['description'] ?? '',
            'location' => $event['location'] ?? ''
        ];
    }

    return $events;
}

/**
 * Store events in database
 */
function storeEvents(array $events): int {
    $count = 0;

    foreach ($events as $event) {
        if (empty($event['event_date'])) {
            continue;
        }

        // Check if event exists
        $existing = dbQueryOne(
            "SELECT id FROM calendar_cache WHERE event_id = ?",
            [$event['event_id']]
        );

        if ($existing) {
            // Update existing
            dbExecute(
                "UPDATE calendar_cache SET
                 title = ?, event_date = ?, event_time = ?,
                 description = ?, location = ?, fetched_at = NOW()
                 WHERE id = ?",
                [
                    $event['title'],
                    $event['event_date'],
                    $event['event_time'],
                    $event['description'],
                    $event['location'],
                    $existing['id']
                ]
            );
        } else {
            // Insert new
            dbInsert(
                "INSERT INTO calendar_cache (event_id, title, event_date, event_time, description, location)
                 VALUES (?, ?, ?, ?, ?, ?)",
                [
                    $event['event_id'],
                    $event['title'],
                    $event['event_date'],
                    $event['event_time'],
                    $event['description'],
                    $event['location']
                ]
            );
        }

        $count++;
    }

    // Clean up old events (more than 1 year old)
    dbExecute("DELETE FROM calendar_cache WHERE event_date < DATE_SUB(CURDATE(), INTERVAL 1 YEAR)");

    return $count;
}

// If called directly from command line or as API
if (php_sapi_name() === 'cli' || !defined('INCLUDED_FROM_ADMIN')) {
    define('INCLUDED_FROM_ADMIN', false);

    // CLI access always allowed
    if (php_sapi_name() === 'cli') {
        $result = syncGroupsIoCalendar();
        if ($result['success']) {
            echo "Sync complete. {$result['count']} events updated.\n";
        } else {
            echo "Sync failed: {$result['error']}\n";
            exit(1);
        }
    } else {
        // Web access requires authentication
        require_once __DIR__ . '/../includes/auth.php';

        header('Content-Type: application/json');

        // Check if user is authenticated
        if (!auth()->isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            exit;
        }

        // Rate limiting: only allow sync once per 5 minutes
        if (isset($_SESSION['last_calendar_sync']) &&
            (time() - $_SESSION['last_calendar_sync']) < 300) {
            http_response_code(429);
            echo json_encode(['success' => false, 'error' => 'Too many requests. Please wait 5 minutes.']);
            exit;
        }

        $_SESSION['last_calendar_sync'] = time();
        $result = syncGroupsIoCalendar();
        echo json_encode($result);
    }
}
