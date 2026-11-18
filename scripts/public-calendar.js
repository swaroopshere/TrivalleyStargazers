const fs = require('node:fs');
const path = require('node:path');
const crypto = require('node:crypto');
const ZONE = 'America/Los_Angeles';
const SOURCE = 'https://groups.io/g/trivalleystargazers/calendar';

function cleanText(value) {
    const named = {nbsp:' ',amp:'&',quot:'"',apos:"'",lt:'<',gt:'>',rsquo:"'",lsquo:"'",rdquo:'"',ldquo:'"',ndash:'–',mdash:'—'};
    return String(value || '').replace(/<(script|style)\b[^>]*>[\s\S]*?<\/\1>/gi, '')
        .replace(/<[^>]*>/g, ' ').replace(/&(#x[0-9a-f]+|#\d+|[a-z]+);/gi, (all, entity) => {
            if (entity[0] !== '#') return named[entity.toLowerCase()] ?? all;
            const n = entity[1].toLowerCase() === 'x' ? parseInt(entity.slice(2),16) : Number(entity.slice(1));
            return n > 0 && n <= 0x10ffff ? String.fromCodePoint(n) : '';
        }).replace(/\u00c2(?=\s)/g, '').replace(/\s+/g, ' ').trim();
}
function localDate(date) { return new Intl.DateTimeFormat('en-CA',{timeZone:ZONE,year:'numeric',month:'2-digit',day:'2-digit'}).format(date); }
function localTime(date) { return new Intl.DateTimeFormat('en-US',{timeZone:ZONE,hour:'numeric',minute:'2-digit',hour12:true}).format(date); }
function marked(value) { return value === true || value === 1 || value === 'true'; }

function normalizeCalendar(raw, startDate, endDate, overrides=[]) {
    if (!Array.isArray(raw)) throw new Error('Public calendar did not return an event array');
    const unique = new Map();
    for (const entry of raw) {
        const title = cleanText(entry.title);
        let description = cleanText(entry.description);
        if (marked(entry.is_canceled) || marked(entry.cancelled) || marked(entry.is_private) || marked(entry.is_member_only)
            || /\bboard\b|\bmembers?[ -]+only\b|tesla vintner/i.test(title)
            || /\bmembers?[ -]+only\b/i.test(description)) continue;
        const start = new Date(entry.start);
        const end = new Date(entry.end || entry.start);
        if (!title || !Number.isFinite(start.getTime()) || !Number.isFinite(end.getTime()) || end < start || !entry.id) throw new Error('Calendar contains an invalid event');
        const date = localDate(start);
        if (date < startDate || date >= endDate) continue;
        // Keep meeting access details in the original calendar rather than exporting passwords.
        if (/\bclub meeting\b/i.test(title)) description = 'Club meetings are open to members and guests. See the calendar for current attendance and Zoom access details.';
        const time = entry.allDay ? 'All day' : localTime(start) + (end > start ? '–' + localTime(end) : '');
        const id = 'groupsio-public-' + crypto.createHash('sha256').update(String(entry.id)+'|'+start.toISOString()).digest('hex');
        const url = new URL(entry.view_url || SOURCE);
        if (url.origin !== 'https://groups.io' || !url.pathname.startsWith('/g/trivalleystargazers/')) throw new Error('Unexpected event details URL');
        let location = cleanText(entry.location);
        const override = overrides.find(row => row.meeting_type === 'public' && row.meeting_date === date && /\bclub meeting\b/i.test(title));
        if (override) {
            location = [override.location,override.location_address].filter(Boolean).join(', ');
            description = override.description+' Topic: '+override.presentation_topic+'. Presenter: '+override.presenter_name+'. See the website meeting announcement for full details.';
        }
        unique.set(id,{id,date,title,description,location,time,startDate:start.toISOString(),endDate:end.toISOString(),url:url.href,
            category:/club meeting/i.test(title)?'meeting':/h2o|open house/i.test(title)?'h2o':/star party/i.test(title)?'starparty':/outreach|science|steam|school|elementary/i.test(title)?'outreach':'event'});
    }
    return [...unique.values()].sort((a,b)=>a.startDate.localeCompare(b.startDate)||a.id.localeCompare(b.id));
}

async function buildCalendar({root=path.join(__dirname,'..'),now=new Date(),request=fetch}={}) {
    const startDate = localDate(now);
    const endDate = (Number(startDate.slice(0,4))+2)+'-01-01';
    const url = new URL('https://groups.io/g/trivalleystargazers/fetchevents');
    url.search = new URLSearchParams({start:startDate+'T00:00:00.000Z',end:endDate+'T23:59:59.999Z',rsvponly:'false',timeZone:'local'}).toString();
    const response = await request(url,{headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},signal:AbortSignal.timeout(45000)});
    if (!response.ok) throw new Error('Public calendar HTTP '+response.status);
    const raw = await response.json();
    const overrides=[]; const dataPath=path.join(root,'tvs-deploy','data');
    if (fs.existsSync(dataPath)) for (const file of fs.readdirSync(dataPath).filter(name=>/^meeting-\d{8}\.json$/.test(name))) {
        const data=JSON.parse(fs.readFileSync(path.join(dataPath,file),'utf8')); if(data.public) overrides.push(data.public);
    }
    const calendar={source:SOURCE,generated_at:now.toISOString(),range_start:startDate,range_end:endDate,events:normalizeCalendar(raw,startDate,endDate,overrides)};
    const targets=[path.join(root,'calendar-data.json')];
    if (fs.existsSync(path.join(root,'tvs-deploy'))) targets.push(path.join(root,'tvs-deploy','calendar-data.json'));
    for (const target of targets) {
        fs.writeFileSync(target+'.tmp',JSON.stringify(calendar,null,4)+'\n'); fs.renameSync(target+'.tmp',target);
    }
    return calendar;
}
module.exports={cleanText,normalizeCalendar,buildCalendar};
