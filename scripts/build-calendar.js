const fs = require('fs');
const fetch = require('node-fetch');
const dotenv = require('dotenv');

// Load environment variables from .env file
dotenv.config();

async function generateCalendarData() {
  const API_KEY = process.env.GROUPS_IO_API_KEY;
  
  try {
    const response = await fetch('https://groups.io/api/v1/calendar/group/trivalleystargazers', {
      headers: {
        'Authorization': `Bearer ${API_KEY}`,
        'Accept': 'application/json'
      }
    });
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    const events = await response.json();
    
    // Filter and format events
    const formattedEvents = events.map(event => ({
      title: event.title,
      date: event.startTime,
      location: event.location || '',
      setupTime: event.setupTime || '',
      description: event.description || ''
    }))
    .filter(event => new Date(event.date) >= new Date()) // Only future events
    .sort((a, b) => new Date(a.date) - new Date(b.date)); // Sort by date
    
    // Write to a static JSON file
    fs.writeFileSync('calendar-data.json', JSON.stringify(formattedEvents, null, 2));
    console.log('Calendar data updated successfully');
    
  } catch (error) {
    console.error('Error generating calendar data:', error);
  }
}

generateCalendarData(); 