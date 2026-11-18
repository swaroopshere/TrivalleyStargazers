<?php
require __DIR__.'/../../includes/db.php';
$pdo=new PDO('sqlite::memory:',null,null,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
$pdo->sqliteCreateFunction('CURDATE',fn()=>'2026-11-18');
$pdo->sqliteCreateFunction('CONCAT',fn(...$values)=>implode('',$values));
$pdo->exec('CREATE TABLE meetings(meeting_type TEXT,meeting_date TEXT,meeting_time TEXT,location TEXT,description TEXT,is_active INT)');
$pdo->exec('CREATE TABLE events(event_type TEXT,event_date TEXT,title TEXT,is_visible INT)');
$pdo->exec('CREATE TABLE calendar_cache(event_date TEXT,event_time TEXT,title TEXT,location TEXT,description TEXT)');
$pdo->exec("INSERT INTO meetings VALUES('public','2026-11-20','19:30:00','Church','Curated',1)");
$pdo->exec("INSERT INTO calendar_cache VALUES('2026-11-20','19:30:00','TVS Club Meeting','Zoom','Recurring')");
$pdo->exec("INSERT INTO calendar_cache VALUES('2027-01-14','18:00:00','Outreach STEAM Night','School','Volunteers')");
$pdo->exec("INSERT INTO calendar_cache VALUES('2026-12-08','18:00:00','School Star Party','School','Telescopes')");
$pdo->exec("INSERT INTO calendar_cache VALUES('2026-11-01','20:00:00','Past Star Party','Park','Past')");
$reflection=new ReflectionClass(Database::class);$db=$reflection->newInstanceWithoutConstructor();
$property=$reflection->getProperty('pdo');$property->setValue($db,$pdo);
$property=$reflection->getProperty('instance');$property->setValue(null,$db);
$events=getAllUpcomingEvents(100);
if(count($events)!==3 || $events[0]['location']!=='Church' || $events[1]['event_time']!=='18:00:00' || $events[2]['title']!=='Outreach STEAM Night') throw new RuntimeException('Upcoming calendar merge, real start times, or curated meeting precedence regressed');
echo "PASS all public events, school outreach, actual times, past-event exclusion and curated meeting deduplication\n";
