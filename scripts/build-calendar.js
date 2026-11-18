const {buildCalendar} = require('./public-calendar');
buildCalendar().then(data => console.log('Calendar refreshed: '+data.events.length+' public events through '+data.range_end))
    .catch(error => { console.error('Calendar was not replaced: '+error.message); process.exitCode=1; });
