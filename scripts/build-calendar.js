const fetch = require('node-fetch');
const fs = require('fs');
const path = require('path');
require('dotenv').config();

// Configuration
const API_KEY = process.env.GROUPS_IO_API_KEY;
const GROUP_NAME = 'trivalleystargazers';

// Function to format date as YYYY-MM-DD
function formatDate(date) {
    return date.toISOString().split('T')[0];
}

// Function to get events for a specific month
async function getEventsForMonth(year, month) {
    const startDate = new Date(year, month, 1);
    const endDate = new Date(year, month, 31);
    
    try {
        const url = new URL('https://groups.io/api/v1/getevents');
        url.searchParams.append('start', formatDate(startDate));
        url.searchParams.append('end', formatDate(endDate));
        url.searchParams.append('group_name', GROUP_NAME);

        const response = await fetch(url.toString(), {
            headers: {
                'Authorization': `Basic ${Buffer.from(API_KEY + ':').toString('base64')}`
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        return data.data || []; // Extract events from the data array
    } catch (error) {
        console.error('Error fetching events:', error.message);
        if (error.response) {
            console.error('Response data:', error.response.data);
        }
        return [];
    }
}

// Function to process and format events
function processEvents(events) {
    return events.map(event => {
        // Extract time from start_time
        const startTime = new Date(event.start_time);
        const time = startTime.toLocaleTimeString('en-US', { 
            hour: 'numeric', 
            minute: '2-digit',
            hour12: true 
        });

        // Clean up description by removing HTML tags
        const cleanDescription = event.description.replace(/<[^>]*>/g, '').trim();

        return {
            date: event.start_time.split('T')[0], // Extract YYYY-MM-DD from ISO string
            title: event.name,
            description: cleanDescription,
            location: event.location,
            time: time
        };
    });
}

// Function to filter out member-only events
function filterPublicEvents(events) {
    return events.filter(event => {
        const title = event.title.toLowerCase();
        const description = event.description.toLowerCase();
        
        return !(
            title.includes('member') ||
            title.includes('tesla vintner') ||
            description.includes('member only') ||
            description.includes('members only') ||
            title.includes('board meeting')
        );
    });
}

// Main function to build the calendar
async function buildCalendar() {
    try {
        // Get current date
        const now = new Date();
        console.log("Today's date " + now);
        const currentYear = now.getUTCFullYear();
        const currentMonth = now.getUTCMonth();
        console.log('getting calendar dates for month ' + currentMonth + '/' + currentYear);

        // Get events for current month and next month
        const currentMonthEvents = await getEventsForMonth(currentYear, currentMonth);
        const nextMonthEvents = await getEventsForMonth(
            currentMonth === 12 ? currentYear + 1 : currentYear,
            currentMonth === 12 ? 1 : currentMonth + 1
        );

        // Combine and process events
        const allEvents = [...currentMonthEvents, ...nextMonthEvents];
        const processedEvents = processEvents(allEvents);
        const publicEvents = filterPublicEvents(processedEvents);

        // Create the calendar data object
        const calendarData = {
            events: publicEvents
        };

        // Write to file
        const outputPath = path.join(__dirname, '..', 'calendar-data.json');
        fs.writeFileSync(outputPath, JSON.stringify(calendarData, null, 4));
        
        console.log('Calendar data has been updated successfully!');
    } catch (error) {
        console.error('Error building calendar:', error.message);
    }
}

// Run the build process
buildCalendar(); 