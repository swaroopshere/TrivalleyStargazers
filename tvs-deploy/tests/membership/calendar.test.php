<?php
require __DIR__.'/../../includes/calendar_data.php';
$events=parsePublishedCalendarData(['events'=>[
    ['date'=>'2026-08-15','time'=>'7:30 PM','title'=>'Public star party','description'=>'<b>Welcome</b>'],
    ['date'=>'2026-08-16','title'=>'Members only session'],
    ['date'=>'2026-08-17','title'=>'Private event','is_private'=>true],
    ['date'=>'invalid','title'=>'Invalid event']]]);
if (count($events)!==1 || $events[0]['event_time']!=='19:30:00' || $events[0]['description']!=='Welcome') throw new RuntimeException('Published calendar parsing regressed');
$legacy=parsePublishedCalendarData([['startDate'=>'2026-08-16T02:30:00Z','title'=>'Public star party']]);
if ($legacy[0]['event_date']!=='2026-08-15' || $legacy[0]['event_time']!=='19:30:00') throw new RuntimeException('Legacy calendar/timezone parsing regressed');
if ($legacy[0]['event_id']!==$events[0]['event_id']) throw new RuntimeException('Calendar identity differs from content import');
echo "PASS published/legacy calendar formats, local timezone, stable IDs, private-event filtering and malformed entries\n";
$identity='groupsio-public-'.str_repeat('a',64);
$normalized=parsePublishedCalendarData(['events'=>[
 ['id'=>$identity,'startDate'=>'2027-01-15T02:00:00Z','title'=>'School outreach'],
 ['startDate'=>'2027-01-15T03:00:00Z','title'=>'Cancelled session','is_canceled'=>true]]]);
if(count($normalized)!==1 || $normalized[0]['event_id']!==$identity || $normalized[0]['event_time']!=='18:00:00') throw new RuntimeException('Public feed identity, cancellation or real time regressed');
