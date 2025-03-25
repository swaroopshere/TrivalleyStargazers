const fs = require('fs');
const fetch = require('node-fetch');

async function generateCalendarData() {
  const API_KEY = process.env.GROUPS_IO_API_KEY;
  
  try {
    const response = await fetch('https://groups.io/api/v1/calendar/group/trivalleystargazers', {
      headers: {
        'Authorization': `Bearer ${API_KEY}`
      }
    });
    const events = await response.json();
    
    // Write to a static JSON file
    fs.writeFileSync('calendar-data.json', JSON.stringify(events));
    
  } catch (error) {
    console.error('Error generating calendar data:', error);
  }
}

// Run this as part of your build process
generateCalendarData(); 