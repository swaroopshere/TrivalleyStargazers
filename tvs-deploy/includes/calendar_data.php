<?php
/** Normalize the published calendar JSON and the older flat event format. */
function parsePublishedCalendarData(array $data): array {
    $entries=$data['events'] ?? $data;
    if (!is_array($entries)) return [];
    $events=[];
    foreach ($entries as $entry) {
        if (!is_array($entry) || empty($entry['title'])) continue;
        $title=(string)$entry['title'];
        $description=html_entity_decode(strip_tags((string)($entry['description'] ?? '')),ENT_QUOTES,'UTF-8');
        if (!empty($entry['is_private']) || !empty($entry['is_member_only'])
            || preg_match('/member|board meeting|tesla vintner/i',$title)
            || preg_match('/members?[ -]only/i',$description)) continue;
        $raw=$entry['startDate'] ?? (($entry['date'] ?? '').' '.($entry['time'] ?? '00:00'));
        try { $date=new DateTimeImmutable($raw,new DateTimeZone('America/Los_Angeles')); }
        catch (Exception $e) { continue; }
        $date=$date->setTimezone(new DateTimeZone('America/Los_Angeles'));
        if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}/',$raw)) continue;
        $day=$date->format('Y-m-d');
        $events[]=['event_id'=>'published-'.hash('sha256',$day.$title),'event_date'=>$day,
            'event_time'=>$date->format('H:i:s'),'title'=>$title,'description'=>$description,'location'=>$entry['location'] ?? ''];
    }
    return $events;
}
