const fetch = require('node-fetch');

exports.handler = async function(event, context) {
  const API_KEY = process.env.GROUPS_IO_API_KEY;
  
  try {
    const response = await fetch('https://groups.io/api/v1/calendar/group/trivalleystargazers', {
      headers: {
        'Authorization': `Bearer ${API_KEY}`
      }
    });
    const data = await response.json();
    
    return {
      statusCode: 200,
      body: JSON.stringify(data)
    };
  } catch (error) {
    return {
      statusCode: 500,
      body: JSON.stringify({ error: 'Failed to fetch calendar data' })
    };
  }
}; 