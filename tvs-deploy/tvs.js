// tvs.js: JavaScript functions common to all Tri-Valley Stargazer web pages

// Last modified: January 2026 - Updated for UI Refresh

// Names for banner image files. They must all be in folder images/banners.
var bannerNames = [
	"01-H2O-daytime.jpg",
	"02-H2O-nighttime.jpg",
	"03-Comet-ISON.jpg",
	"04-Milky-Way.jpg",
	"05-NGC-4631.jpg",
	"06-Horsehead-Nebula.jpg",
	"07-Solar-Eclipse.jpg"
];

var bannerTitles = [
	'Our "Hidden Hill Observatory" site, H2O',
	"H2O at night",
	"Comet ISON, by Ken Sperber",
	"The Milky Way, by Alex Mellinger",
	"The Whale Galaxy, by Hilary Jones",
	"Horsehead Nebula, by Chuck Vaughn",
	"Solar Eclipse, by Gert Gottschalk"
];

// PayPal shopping cart variables
var explanation = "";
var item_count;
var otherValue = 0;
var total;
var url;

// Banner animation parameters
var bannerIndex = 0;
var blendID;
var blendPercent;
var fadeTime = 1000;
var numChanges = 20;
var swapTime = 10000;

// Links page state
var currentTopic = null;

// Pop-down menu code (legacy support)
var timeout = 500;
var closetimer = 0;
var ddmenuitem = 0;

function mopen(id) {
	mcancelclosetime();
	if (ddmenuitem) ddmenuitem.style.visibility = 'hidden';
	ddmenuitem = document.getElementById(id);
	ddmenuitem.style.visibility = 'visible';
}

function mclose() {
	if (ddmenuitem) ddmenuitem.style.visibility = 'hidden';
}

function mclosetime() {
	closetimer = window.setTimeout(mclose, timeout);
}

function mcancelclosetime() {
	if (closetimer) {
		window.clearTimeout(closetimer);
		closetimer = null;
	}
}

document.onclick = mclose;

// Newsletter navigation
function goFetch() {
	var theMonth = document.getElementById("theMonth").value;
	var theYear = document.getElementById("theYear").value;
	var shortYear = theYear.substring(2);
	var filename = "newsletters/" + theYear + "/";

	if (theYear == 1996 && theMonth < 3) {
		alert("There is no newsletter for that date");
		return;
	}
	var thisYear = Number((new Date()).getFullYear());
	var thisMonth = Number((new Date()).getMonth()) + 1;
	if (theYear == thisYear && theMonth > thisMonth) {
		alert("That newsletter hasn't been published yet");
		return;
	}

	if ((theYear < 2001) || (theYear == 2001 && theMonth < 9))
		filename += theMonth + shortYear + "/index.html";
	else
		filename += "tvsnews" + theMonth + shortYear + ".pdf#zoom=100&pagemode=none";

	window.location.href = filename;
}

function defineNewsletterYears() {
	var thisYear = Number((new Date()).getFullYear());
	var select = document.getElementById("theYear");
	for (var year = 1996; year <= thisYear; year++) {
		var option = document.createElement("option");
		option.text = String(year);
		option.value = String(year);
		if (year == thisYear) option.selected = true;
		select.add(option, null);
	}
}

// PayPal functions
function addItem(name, value, details) {
	total += parseFloat(value);
	item_count++;
	var term = "&item_name_" + item_count + "=" + name;
	term = term + "&amount_" + item_count + "=" + value;
	if (details)
		term = term + "&on0_" + item_count + "=Details&os0_" + item_count + "=" + details;
	url = url + term;
}

function callPayPal() {
	updateItems();
	if (item_count == 0) {
		alert("You didn't order anything");
		return;
	}
	if (otherValue != 0 && explanation == "") {
		alert("Please enter an explanation for the other payment");
		item_count = 0;
		return;
	}

	var usingSandbox = (window.location.search.substring(1) == "sandbox");
	if (usingSandbox) alert("Testing with PayPal's sandbox");
	document.body.onbeforeunload = "";
	window.location.assign(url);
}

function updateItems() {
	var e;
	item_count = 0;
	total = 0;

	var usingSandbox = (window.location.search.substring(1) == "sandbox");
	if (usingSandbox)
		url = "https://www.sandbox.paypal.com/cgi-bin/webscr?business=treasurer-facilitator@trivalleystargazers.org";
	else
		url = "https://www.paypal.com/cgi-bin/webscr?business=treasurer@trivalleystargazers.org";
	url += "&cmd=_cart&currency_code=USD&upload=1";

	e = document.getElementById("membershipType");
	addItem(e.options[e.selectedIndex].text + " Membership", e.value);

	e = document.getElementById("H2OKey");
	if (e.checked) addItem("H2O Key Deposit", e.value);

	e = document.getElementById("H2OAccess");
	if (e.checked) addItem("H2O Yearly Access Fee", e.value);

	e = document.getElementById("donation");
	if (e.value == "") e.value = "0";
	var r = /^\$?[0-9]+\.?[0-9]?[0-9]?$/;
	if (r.test(e.value)) {
		e.value = e.value.replace(/\$/g, '');
		addItem("Donation", e.value);
		e.value = "$" + e.value;
	} else {
		alert("Please enter a valid amount for the donation. " + e.value + " isn't valid.");
		e.value = "$0";
		item_count = 0;
		return;
	}

	e = document.getElementById("other");
	explanation = document.getElementById("explanation").value;
	explanation = explanation.slice(0, 50);
	otherValue = 0;
	if (e.value == "") e.value = "0";
	if (r.test(e.value)) {
		e.value = e.value.replace(/\$/g, '');
		otherValue = e.value;
		e.value = "$" + e.value;
	} else {
		alert("Please enter a valid amount for the Other expense. " + e.value + " isn't valid.");
		e.value = "$0";
		item_count = 0;
		return;
	}

	addItem("Other", otherValue, explanation);
}

function updateTotal() {
	updateItems();
	var e = document.getElementById("total");
	e.value = "$" + total;
}

// Banner animation - Updated for new CSS-based banners
function blendBanners() {
	blendPercent += 100 / numChanges;
	if (blendPercent > 100) {
		clearInterval(blendID);
		return;
	}
	changeOpacity("bannerTop", blendPercent);
}

function changeOpacity(id, opacity) {
	var object = document.getElementById(id);
	if (object) {
		object.style.opacity = (opacity / 100);
	}
}

function startBannerSwapping() {
	var bannerTop = document.getElementById('bannerTop');
	var bannerBottom = document.getElementById('bannerBottom');

	if (!bannerTop || !bannerBottom) return;

	// Helper to set banner background image
	function setBannerImage(element, index) {
		var imagePath = 'images/banners/' + bannerNames[index];
		element.style.backgroundImage = 'url("' + imagePath + '")';
		element.title = bannerTitles[index];
	}

	function swapBanners() {
		// Copy current top to bottom (for crossfade effect)
		bannerBottom.style.backgroundImage = bannerTop.style.backgroundImage;
		bannerBottom.title = bannerTop.title;

		// Move to next banner
		bannerIndex = (bannerIndex + 1) % bannerNames.length;

		// Set top banner opacity to 0 before changing image
		bannerTop.style.opacity = 0;

		// Set new image on top banner
		setBannerImage(bannerTop, bannerIndex);

		// Fade in using CSS transition (defined in tvs.css)
		// Small delay to ensure the image starts loading before fade
		setTimeout(function() {
			bannerTop.style.opacity = 1;
		}, 50);
	}

	// Initialize with first two banners
	setBannerImage(bannerTop, 0);
	setBannerImage(bannerBottom, 1);
	bannerTop.style.opacity = 1;

	// Start the rotation
	setInterval(swapBanners, swapTime);
}

// Progressive enhancement for contact links
// Converts spans with data-user and data-domain into mailto links
function enhanceContactLinks() {
	document.querySelectorAll('.contact-link').forEach(function(el) {
		var user = el.getAttribute('data-user');
		var domain = el.getAttribute('data-domain');
		if (user && domain) {
			var email = user + '@' + domain;
			var link = document.createElement('a');
			link.href = 'mailto:' + email;
			link.title = 'mailto:' + email;
			link.textContent = el.textContent;
			el.innerHTML = '';
			el.appendChild(link);
		}
	});
}

// Observing awards display
function award(cert_no, name, date) {
	document.write("<tr><td>" + cert_no + "</td><td>" + name + "</td><td>" + date + "</td></tr>");
}

// Speakers page presentation display
function presentation(month, day, presenter, title, link) {
	if (link) {
		title = '<a href="' + link + '" title="See more about this talk">' + title + '</a>';
	}
	if (presenter) {
		if (title != null) {
			title = '; "' + title + '"';
		} else {
			title = "";
		}
	} else {
		presenter = "";
	}
	document.write('<tr><td>' + month + '</td><td>' + day + '</td><td>' + presenter + title + '</td></tr>');
}

// Membership form setup
function setupForm() {
	document.application.action = "cgi-bin/apply.pl";
	document.getElementById("preset").value = "Preset";
}

// Links page topic display
function showLinksDetails(newTopic) {
	var newDetails = document.getElementById("D_" + newTopic.id);
	if (currentTopic == null)
		currentTopic = document.getElementById("overview");
	var currentDetails = document.getElementById("D_" + currentTopic.id);

	currentTopic.style.fontWeight = "normal";
	currentDetails.style.display = "none";

	newTopic.style.fontWeight = "bold";
	newDetails.style.display = "block";

	currentTopic = newTopic;
}

// Logo hover effect
function highlightLogo(mouseover) {
	var logo = document.getElementById("logo");
	if (logo) {
		if (mouseover)
			logo.src = "images/logo2.png";
		else
			logo.src = "images/logo1.png";
	}
}

// URL query parameter utility
function getQueryParam(name) {
	name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
	var regex = new RegExp("[\\?&]" + name + "=([^&#]*)");
	var results = regex.exec(location.search);
	return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

// Smooth scroll to anchor links (optional enhancement)
function enableSmoothScroll() {
	document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
		anchor.addEventListener('click', function(e) {
			var targetId = this.getAttribute('href');
			if (targetId === '#') return;

			var targetElement = document.querySelector(targetId);
			if (targetElement) {
				e.preventDefault();
				targetElement.scrollIntoView({
					behavior: 'smooth',
					block: 'start'
				});
			}
		});
	});
}

// ============================================
// PHOTO GALLERY & LIGHTBOX
// ============================================

// Gallery state
var currentGallery = null;
var currentIndex = 0;
var galleryImages = [];

// Create lightbox HTML structure
function createLightbox() {
	if (document.querySelector('.lightbox')) return;

	var lightbox = document.createElement('div');
	lightbox.className = 'lightbox';
	lightbox.innerHTML = [
		'<button class="lightbox-close" aria-label="Close lightbox">&times;</button>',
		'<button class="lightbox-nav lightbox-prev" aria-label="Previous image">&#10094;</button>',
		'<img class="lightbox-image" src="" alt="">',
		'<button class="lightbox-nav lightbox-next" aria-label="Next image">&#10095;</button>',
		'<div class="lightbox-caption"></div>'
	].join('');

	document.body.appendChild(lightbox);

	// Event listeners
	lightbox.querySelector('.lightbox-close').addEventListener('click', closeLightbox);
	lightbox.querySelector('.lightbox-prev').addEventListener('click', function() {
		navigateLightbox(-1);
	});
	lightbox.querySelector('.lightbox-next').addEventListener('click', function() {
		navigateLightbox(1);
	});

	// Close on background click
	lightbox.addEventListener('click', function(e) {
		if (e.target === lightbox) {
			closeLightbox();
		}
	});
}

// Initialize all galleries on the page
function initGalleries() {
	createLightbox();

	document.querySelectorAll('.photo-gallery').forEach(function(gallery) {
		gallery.querySelectorAll('.gallery-item').forEach(function(item, index) {
			item.setAttribute('tabindex', '0');
			item.setAttribute('role', 'button');
			item.setAttribute('aria-label', 'View ' + (item.getAttribute('data-title') || 'image') + ' in lightbox');

			item.addEventListener('click', function() {
				openLightbox(gallery, index);
			});

			item.addEventListener('keydown', function(e) {
				if (e.key === 'Enter' || e.key === ' ') {
					e.preventDefault();
					openLightbox(gallery, index);
				}
			});
		});
	});
}

// Open lightbox with image from gallery
function openLightbox(gallery, index) {
	currentGallery = gallery;
	currentIndex = index;
	galleryImages = Array.from(gallery.querySelectorAll('.gallery-item'));

	var lightbox = document.querySelector('.lightbox');
	lightbox.setAttribute('data-count', galleryImages.length);

	showImage();
	lightbox.classList.add('active');
	document.body.style.overflow = 'hidden';

	// Focus the lightbox for keyboard navigation
	lightbox.focus();
}

// Close lightbox
function closeLightbox() {
	var lightbox = document.querySelector('.lightbox');
	if (lightbox) {
		lightbox.classList.remove('active');
		document.body.style.overflow = '';
	}
}

// Navigate to prev/next image
function navigateLightbox(direction) {
	currentIndex += direction;
	if (currentIndex < 0) currentIndex = galleryImages.length - 1;
	if (currentIndex >= galleryImages.length) currentIndex = 0;
	showImage();
}

// Display the current image in the lightbox
function showImage() {
	var item = galleryImages[currentIndex];
	var lightbox = document.querySelector('.lightbox');
	var img = lightbox.querySelector('.lightbox-image');
	var caption = lightbox.querySelector('.lightbox-caption');

	// Get full-size image source (data-full attribute or the img src)
	var fullSrc = item.getAttribute('data-full');
	if (!fullSrc) {
		var imgElement = item.querySelector('img');
		fullSrc = imgElement ? imgElement.src : '';
	}

	var title = item.getAttribute('data-title') || '';

	img.src = fullSrc;
	img.alt = title;
	caption.textContent = title;
}

// Keyboard navigation for lightbox
document.addEventListener('keydown', function(e) {
	var lightbox = document.querySelector('.lightbox');
	if (!lightbox || !lightbox.classList.contains('active')) return;

	if (e.key === 'Escape') {
		closeLightbox();
	} else if (e.key === 'ArrowLeft') {
		navigateLightbox(-1);
	} else if (e.key === 'ArrowRight') {
		navigateLightbox(1);
	}
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
	startBannerSwapping();
	enhanceContactLinks();
	enableSmoothScroll();
	initGalleries();
});
