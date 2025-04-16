// tvs.js: JavaScript functions common to all Tri-Valley Stargazer web pages

// Last modified: March 25, 2025

// Names for banner image files.  They must all be in folder images/banner.
var bannerNames = [
	"01-H2O-daytime.jpg", 
	"02-H2O-nighttime.jpg", 
	"03-Comet-ISON.jpg", 
	"04-Milky-Way.jpg", 
	"05-NGC-4631.jpg", 
	"06-Horsehead-Nebula.jpg",
	"07-Solar-Eclipse.jpg"];

var bannerTitles = [
	'Our "Hidden Hill Observatory" site, H2O',
	"H2O at night",
	"Comet ISON, by Ken Sperber",
	"The Milky Way, by Alex Mellinger",
	"The Whale Galaxy, by Hilary Jones",
	"Horsehead Nebula, by Chuck Vaughn",
	"Solar Eclipse, by Gert Gottschalk"];

// Variables used to keep track of the shopping cart
var explanation="";			// Explanation for the other payment item
var item_count;				// Total number of items user has chosen
var otherValue=0;			// Amount for the other payment item
var total;				// Total amount the user will have to pay
var url;				// PayPal's URL, including query terms, etc.

// Parameters that control animated banners
var bannerIndex = 0;			// Index into bannerNames and bannerTitles for the current banner image
var blendID;				// The ID for the setInterval function that blends images
var blendPercent;			// The opacity percentage for the top banner image
var fadeTime = 1000;			// Time that it takes to fade from one banner to the next, in msec
var numChanges = 20;			// Number of times we change opacity.  Larger values => less jerky transition.
var swapTime = 10000;			// The time between banner swaps, in msec

var contactWidth = "300px";		// The width of the contact table entries
var currentTopic = null;		// Which links topic is displayed
var lastUpdateDate;			// The time this web page was last updated

// Code for pop-down menus
var timeout = 500;
var closetimer = 0;
var ddmenuitem = 0;

// open hidden layer
function mopen(id) {	
	// cancel close timer
	mcancelclosetime();

	// close old layer
	if(ddmenuitem) ddmenuitem.style.visibility = 'hidden';

	// get new layer and show it
	ddmenuitem = document.getElementById(id);
	ddmenuitem.style.visibility = 'visible';
}

// close showed layer
function mclose() {
    if (ddmenuitem) ddmenuitem.style.visibility = 'hidden';
}

// go close timer
function mclosetime() {
	closetimer = window.setTimeout(mclose, timeout);
}

// cancel close timer
function mcancelclosetime() {
	if(closetimer) {
		window.clearTimeout(closetimer);
		closetimer = null;
	}
}

// close layer when click-out
document.onclick = mclose;

// Add one item to the list of things that user wants to pay for
function addItem(name, value, details) {
	total += parseFloat(value);
	item_count++;
	var term = "&item_name_" + item_count + "=" + name;
	term = term + "&amount_" + item_count + "=" + value;
	if (details)
		term = term + "&on0_" + item_count + "=Details&os0_" + item_count + "=" + details;
	url = url + term;
}

// Display Observing Program award info for the named person
function award(cert_no, name, date) {
	document.write("<tr><td>" + cert_no + "</td><td>" + name + "</td><td>" + date + "</td></tr>");
}

// Set the opacity to blend the top and bottom banners
function blendBanners() {
	blendPercent += 100/numChanges;
	if (blendPercent > 100) {
		clearInterval(blendID);	// Stop the setInterval function that is calling us
		return;
	}
	changeOpacity("bannerTop", blendPercent);
	return;
}

// Find what the user wants to pay for, and call PayPal
function callPayPal() {
	updateItems();
	if (item_count == 0) {
		alert("You didn't order anything");
		return;
	}
	if (otherValue != 0 && explanation == "") {
		alert("Please enter an explanation for the other payment");
		item_count = 0;			// Prevent accidentally calling PayPal
		return;
	}

	// To test w/ PayPal's sandbox, the URL must look like http://www.trivalleystargazers.org/pay.shtml?sandbox
	var usingSandbox = (window.location.search.substring(1) == "sandbox");
	if (usingSandbox)
		alert("Testing with PayPal's sandbox");
	document.body.onbeforeunload = "";		// Let the user leave this page w/o a warning.
	window.location.assign(url);
}

// Change the opacity of an element
function changeOpacity(id, opacity) { 
	var object = document.getElementById(id).style; 
	object.opacity = (opacity / 100); 
	object.MozOpacity = (opacity / 100); 
	object.KhtmlOpacity = (opacity / 100); 
	object.filter = "alpha(opacity=" + opacity + ")"; 
}

// Display information about a contact
function contact(e_name, e_site, fullname, title) {
	if (title != null) {
		document.write("<div style=\'min-width:" + contactWidth + ";float:left;\'>" + title + "</div>");
		document.write("<div style='min-width:" + contactWidth + ";float:left;'>");
	}

	if (e_name == "" || e_site == "") {
		// No email address supplied; so don't provide a link
		document.write(fullname);
	}
	else {
		// Email address supplied, so present the contact's name as a link
		var email = e_name + "@" + e_site;
		var mailto = "mailto:" + email;
		document.write("<a href='" + mailto + "'" + " title='" + mailto + "'>" + fullname + "</a>");
	}
	if (title != null) {
		document.write("&nbsp;</div>");
		document.write("<br>");
	}
}

// Modern version of contact function using DOM manipulation
function contactNew(e_name, e_site, fullname, title) {
    let container = document.createElement('div');

    if (title != null) {
        let titleDiv = document.createElement('div');
        titleDiv.style.minWidth = contactWidth;
        titleDiv.style.float = 'left';
        titleDiv.textContent = title;
        container.appendChild(titleDiv);

        let nameDiv = document.createElement('div');
        nameDiv.style.minWidth = contactWidth;
        nameDiv.style.float = 'left';

        if (e_name === "" || e_site === "") {
            nameDiv.textContent = fullname;
        } else {
            let email = e_name + "@" + e_site;
            let mailto = "mailto:" + email;
            let link = document.createElement('a');
            link.href = mailto;
            link.title = mailto;
            link.textContent = fullname;
            nameDiv.appendChild(link);
        }
        container.appendChild(nameDiv);
        container.appendChild(document.createTextNode('\u00A0')); // &nbsp;
        container.appendChild(document.createElement('br'));
    } else {
        if (e_name === "" || e_site === "") {
            container.textContent = fullname;
        } else {
            let email = e_name + "@" + e_site;
            let mailto = "mailto:" + email;
            let link = document.createElement('a');
            link.href = mailto;
            link.title = mailto;
            link.textContent = fullname;
            container.appendChild(link);
        }
    }

    document.body.appendChild(container);
}

// Set up the newsletter web page to show all years for which newsletters are available
function defineNewsletterYears() {
	var thisYear = Number((new Date()).getFullYear());
	var select = document.getElementById("theYear");
	var year;
	for (year=1996; year<=thisYear; year++) {
		var option = document.createElement("option");
		option.text = String(year);
		option.value = String(year);
		if (year == thisYear)
			option.selected = true;
		try {
			// for IE earlier than version 8
			select.add(option, select.options[null]);
		}
		catch (e) {
			// for IE8 and later
			select.add(option, null);
		}
	}
}

// Display an event in the calendar
function event(day, time, description) {
	document.write("<tr><td>" + day + "</td><td>" + time + "</td><td>" + description + "</td></tr>");
}

// Get a query parameter from the URL
function getQueryParam(name) {
	name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
	var regex = new RegExp("[\\?&]" + name + "=([^&#]*)");
	var results = regex.exec(location.search);
	return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

// Show or hide optional elements
function showOptional(id) {
    const element = document.getElementById(id);
    if (element) {
        element.style.display = 'block';
    }
}

// Start the banner swapping animation
function startBannerSwapping() {
    const bannerTop = document.getElementById('bannerTop');
    const bannerBottom = document.getElementById('bannerBottom');
    
    if (!bannerTop || !bannerBottom) return;
    
    function swapBanners() {
        bannerBottom.src = bannerTop.src;
        bannerBottom.alt = bannerTop.alt;
        bannerBottom.title = bannerTop.title;
        
        bannerIndex = (bannerIndex + 1) % bannerNames.length;
        bannerTop.src = 'images/banner/' + bannerNames[bannerIndex];
        bannerTop.alt = bannerTitles[bannerIndex];
        bannerTop.title = bannerTitles[bannerIndex];
        
        blendPercent = 0;
        blendID = setInterval(blendBanners, fadeTime/numChanges);
    }
    
    // Initial setup
    bannerTop.src = 'images/banner/' + bannerNames[0];
    bannerTop.alt = bannerTitles[0];
    bannerTop.title = bannerTitles[0];
    
    bannerBottom.src = 'images/banner/' + bannerNames[1];
    bannerBottom.alt = bannerTitles[1];
    bannerBottom.title = bannerTitles[1];
    
    // Start the swapping
    setInterval(swapBanners, swapTime);
}

// Fetch and display upcoming events
async function displayUpcomingEvents() {
    try {
        const response = await fetch('calendar-data.json');
        if (!response.ok) {
            throw new Error('Failed to fetch events');
        }
        const data = await response.json();
        
        const eventsDiv = document.getElementById('upcoming-events');
        if (!eventsDiv) return;
        
        const now = new Date();
        const upcomingEvents = data.events
            .filter(event => {
                // Filter out member-only events and club meetings
                const isMemberOnly = event.title.toLowerCase().includes('member') || 
                                   event.description.toLowerCase().includes('member only') ||
                                   event.title.toLowerCase().includes('tesla vintner');
                const isMeeting = event.title.toLowerCase().includes('meeting') ||
                                event.description.toLowerCase().includes('meeting') ||
                                event.title.toLowerCase().includes('board') ||
                                event.description.toLowerCase().includes('board');
                return new Date(event.date) >= now && !isMemberOnly && !isMeeting;
            })
            .sort((a, b) => new Date(a.date) - new Date(b.date));
            
        if (upcomingEvents.length === 0) {
            eventsDiv.innerHTML = '<p>No upcoming public star parties scheduled.</p>';
            return;
        }
        
        const table = document.createElement('table');
        table.className = 'star-party-table';
        
        // Create table header
        const thead = document.createElement('thead');
        const headerRow = document.createElement('tr');
        ['Date', 'Event', 'Location', 'Time'].forEach(headerText => {
            const th = document.createElement('th');
            th.textContent = headerText;
            headerRow.appendChild(th);
        });
        thead.appendChild(headerRow);
        table.appendChild(thead);
        
        // Create table body
        const tbody = document.createElement('tbody');
        upcomingEvents.forEach(event => {
            const row = document.createElement('tr');
            
            // Format date
            const date = new Date(event.date).toLocaleDateString('en-US', {
                weekday: 'short',
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
            
            // Create cells
            const cells = [
                date,
                event.title,
                event.location,
                event.time || 'Time TBD' // Use the time property directly from the JSON
            ];
            
            cells.forEach(cellText => {
                const td = document.createElement('td');
                td.textContent = cellText;
                row.appendChild(td);
            });
            
            tbody.appendChild(row);
        });
        table.appendChild(tbody);
        
        // Add table to the page
        eventsDiv.innerHTML = '<h3 class="subtitle">Upcoming Public Star Parties</h3>';
        eventsDiv.appendChild(table);
        
        // Add some basic styling for the table
        const style = document.createElement('style');
        style.textContent = `
            .star-party-table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
                font-size: 0.9em;
            }
            .star-party-table th, .star-party-table td {
                padding: 12px 15px;
                text-align: left;
                border-bottom: 1px solid #ddd;
            }
            .star-party-table th {
                background-color: #f8f9fa;
                color: #2c3e50;
                font-weight: bold;
            }
            .star-party-table tr:hover {
                background-color: #f5f5f5;
            }
            @media (max-width: 768px) {
                .star-party-table {
                    display: block;
                    overflow-x: auto;
                }
            }
        `;
        document.head.appendChild(style);
        
    } catch (error) {
        console.error('Error loading events:', error);
        const eventsDiv = document.getElementById('upcoming-events');
        if (eventsDiv) {
            eventsDiv.innerHTML = '<p>Unable to load upcoming star parties. Please check back later.</p>';
        }
    }
}

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    displayUpcomingEvents();
    startBannerSwapping();
});
