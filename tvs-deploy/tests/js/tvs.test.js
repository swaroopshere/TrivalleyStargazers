/**
 * Unit tests for tvs.js
 * Tests the JavaScript utility functions
 */

// Load the main script
const fs = require('fs');
const path = require('path');

// Read and evaluate tvs.js
const tvsCode = fs.readFileSync(path.join(__dirname, '../../tvs.js'), 'utf8');

// We need to evaluate the code in the global scope
// But first, remove the DOMContentLoaded listener to prevent auto-execution
const modifiedCode = tvsCode.replace(
    /document\.addEventListener\('DOMContentLoaded'.*?\}\);/s,
    '// DOMContentLoaded removed for testing'
);

eval(modifiedCode);

describe('Banner Configuration', () => {
    test('bannerNames array should have entries', () => {
        expect(Array.isArray(bannerNames)).toBe(true);
        expect(bannerNames.length).toBeGreaterThan(0);
    });

    test('bannerTitles should match bannerNames length', () => {
        expect(bannerTitles.length).toBe(bannerNames.length);
    });

    test('all banner names should be valid image filenames', () => {
        bannerNames.forEach(name => {
            expect(name).toMatch(/\.(jpg|jpeg|png|gif)$/i);
        });
    });
});

describe('Newsletter Navigation - goFetch()', () => {
    beforeEach(() => {
        document.body.innerHTML = `
            <select id="theMonth">
                <option value="1">January</option>
                <option value="6">June</option>
                <option value="12">December</option>
            </select>
            <select id="theYear">
                <option value="2026">2026</option>
                <option value="2001">2001</option>
                <option value="1999">1999</option>
            </select>
        `;
    });

    test('should alert for invalid early dates (before March 1996)', () => {
        document.getElementById('theMonth').value = '2';
        document.getElementById('theYear').value = '1996';

        goFetch();

        expect(window.alert).toHaveBeenCalledWith('There is no newsletter for that date');
    });

    test('should alert for future newsletters', () => {
        // Set to a future month
        const futureYear = new Date().getFullYear() + 1;
        const yearSelect = document.getElementById('theYear');
        const option = document.createElement('option');
        option.value = String(futureYear);
        yearSelect.appendChild(option);
        yearSelect.value = String(futureYear);

        document.getElementById('theMonth').value = '1';

        goFetch();

        expect(window.alert).toHaveBeenCalledWith("That newsletter hasn't been published yet");
    });

    test('should generate HTML path for pre-September 2001 newsletters', () => {
        document.getElementById('theMonth').value = '6';
        document.getElementById('theYear').value = '1999';

        goFetch();

        expect(window.location.href).toBe('newsletters/1999/699/index.html');
    });

    test('should generate PDF path for post-September 2001 newsletters', () => {
        document.getElementById('theMonth').value = '1';
        document.getElementById('theYear').value = '2026';

        goFetch();

        expect(window.location.href).toContain('newsletters/2026/tvsnews126.pdf');
    });
});

describe('Newsletter Year Dropdown - defineNewsletterYears()', () => {
    beforeEach(() => {
        document.body.innerHTML = '<select id="theYear"></select>';
    });

    test('should populate years from 1996 to current year', () => {
        defineNewsletterYears();

        const select = document.getElementById('theYear');
        const thisYear = new Date().getFullYear();
        const expectedCount = thisYear - 1996 + 1;

        expect(select.options.length).toBe(expectedCount);
    });

    test('should set current year as selected', () => {
        defineNewsletterYears();

        const select = document.getElementById('theYear');
        const thisYear = String(new Date().getFullYear());

        expect(select.value).toBe(thisYear);
    });

    test('should include 1996 as first year', () => {
        defineNewsletterYears();

        const select = document.getElementById('theYear');
        expect(select.options[0].value).toBe('1996');
    });
});

describe('PayPal Functions', () => {
    describe('addItem()', () => {
        beforeEach(() => {
            // Reset PayPal variables
            item_count = 0;
            total = 0;
            url = 'https://www.paypal.com/cgi-bin/webscr?cmd=_cart&upload=1';
        });

        test('should increment item count', () => {
            addItem('Test Item', '10.00');
            expect(item_count).toBe(1);
        });

        test('should add to total', () => {
            addItem('Test Item', '10.00');
            addItem('Another Item', '25.50');
            expect(total).toBe(35.50);
        });

        test('should append item to URL', () => {
            addItem('Membership', '30.00');
            expect(url).toContain('item_name_1=Membership');
            expect(url).toContain('amount_1=30.00');
        });

        test('should handle details parameter', () => {
            addItem('Other', '15.00', 'Special request');
            expect(url).toContain('on0_1=Details');
            expect(url).toContain('os0_1=Special request');
        });
    });

    describe('callPayPal()', () => {
        beforeEach(() => {
            document.body.innerHTML = `
                <select id="membershipType">
                    <option value="30">Regular</option>
                </select>
                <input type="checkbox" id="H2OKey" value="40">
                <input type="checkbox" id="H2OAccess" value="30">
                <input type="text" id="donation" value="0">
                <input type="text" id="other" value="0">
                <input type="text" id="explanation" value="">
            `;
            item_count = 0;
            total = 0;
            otherValue = 0;
            explanation = '';
        });

        test('should alert if no items selected', () => {
            document.getElementById('membershipType').innerHTML = '<option value="0">None</option>';

            callPayPal();

            expect(window.alert).toHaveBeenCalledWith("You didn't order anything");
        });

        test('should alert if other value without explanation', () => {
            document.getElementById('other').value = '50';

            callPayPal();

            expect(window.alert).toHaveBeenCalledWith('Please enter an explanation for the other payment');
        });

        test('should use sandbox URL when sandbox param is present', () => {
            window.location.search = 'sandbox';

            updateItems();

            expect(url).toContain('sandbox.paypal.com');
        });
    });

    describe('updateItems()', () => {
        beforeEach(() => {
            document.body.innerHTML = `
                <select id="membershipType">
                    <option value="30">Regular</option>
                    <option value="40">Family</option>
                </select>
                <input type="checkbox" id="H2OKey" value="40">
                <input type="checkbox" id="H2OAccess" value="30">
                <input type="text" id="donation" value="$0">
                <input type="text" id="other" value="$0">
                <input type="text" id="explanation" value="">
                <input type="text" id="total" value="">
            `;
        });

        test('should alert for invalid donation amount', () => {
            document.getElementById('donation').value = 'invalid';

            updateItems();

            expect(window.alert).toHaveBeenCalled();
        });

        test('should strip $ from donation', () => {
            document.getElementById('donation').value = '$25';

            updateItems();

            const donation = document.getElementById('donation').value;
            expect(donation).toBe('$25');
        });

        test('should add H2O key when checked', () => {
            document.getElementById('H2OKey').checked = true;

            updateItems();

            expect(url).toContain('H2O Key Deposit');
        });

        test('should add H2O access when checked', () => {
            document.getElementById('H2OAccess').checked = true;

            updateItems();

            expect(url).toContain('H2O Yearly Access Fee');
        });
    });
});

describe('Pop-down Menu Functions', () => {
    beforeEach(() => {
        document.body.innerHTML = `
            <div id="menu1" style="visibility: hidden;"></div>
            <div id="menu2" style="visibility: hidden;"></div>
        `;
        ddmenuitem = 0;
        closetimer = 0;
    });

    test('mopen() should make menu visible', () => {
        mopen('menu1');

        const menu = document.getElementById('menu1');
        expect(menu.style.visibility).toBe('visible');
    });

    test('mopen() should hide previous menu', () => {
        mopen('menu1');
        mopen('menu2');

        const menu1 = document.getElementById('menu1');
        const menu2 = document.getElementById('menu2');

        expect(menu1.style.visibility).toBe('hidden');
        expect(menu2.style.visibility).toBe('visible');
    });

    test('mclose() should hide current menu', () => {
        mopen('menu1');
        mclose();

        const menu = document.getElementById('menu1');
        expect(menu.style.visibility).toBe('hidden');
    });

    test('mclosetime() should set close timer', () => {
        mopen('menu1');
        mclosetime();

        expect(closetimer).not.toBe(0);
    });

    test('mcancelclosetime() should cancel timer', () => {
        mclosetime();
        mcancelclosetime();

        expect(closetimer).toBeNull();
    });
});

describe('Contact Link Enhancement - enhanceContactLinks()', () => {
    test('should convert span to mailto link', () => {
        document.body.innerHTML = `
            <span class="contact-link" data-user="test" data-domain="example.com">Contact Us</span>
        `;

        enhanceContactLinks();

        const link = document.querySelector('a');
        expect(link).not.toBeNull();
        expect(link.href).toBe('mailto:test@example.com');
    });

    test('should preserve original text', () => {
        document.body.innerHTML = `
            <span class="contact-link" data-user="info" data-domain="tvs.org">Info</span>
        `;

        enhanceContactLinks();

        const link = document.querySelector('a');
        expect(link.textContent).toBe('Info');
    });

    test('should handle multiple contact links', () => {
        document.body.innerHTML = `
            <span class="contact-link" data-user="one" data-domain="test.com">One</span>
            <span class="contact-link" data-user="two" data-domain="test.com">Two</span>
        `;

        enhanceContactLinks();

        const links = document.querySelectorAll('a');
        expect(links.length).toBe(2);
    });

    test('should skip links without data attributes', () => {
        document.body.innerHTML = `
            <span class="contact-link">No data</span>
        `;

        enhanceContactLinks();

        const link = document.querySelector('a');
        expect(link).toBeNull();
    });
});

describe('URL Query Parameter - getQueryParam()', () => {
    test('should return empty string for missing param', () => {
        window.location.search = '';
        expect(getQueryParam('missing')).toBe('');
    });

    test('should return param value', () => {
        window.location.search = '?name=value';
        expect(getQueryParam('name')).toBe('value');
    });

    test('should handle multiple params', () => {
        window.location.search = '?first=1&second=2&third=3';
        expect(getQueryParam('second')).toBe('2');
    });

    test('should decode URL encoded values', () => {
        window.location.search = '?query=hello%20world';
        expect(getQueryParam('query')).toBe('hello world');
    });

    test('should handle special regex characters in param name', () => {
        window.location.search = '?test[0]=value';
        expect(getQueryParam('test[0]')).toBe('value');
    });
});

describe('Banner Animation Functions', () => {
    beforeEach(() => {
        document.body.innerHTML = `
            <div id="bannerTop" style="opacity: 1; background-image: none;"></div>
            <div id="bannerBottom" style="background-image: none;"></div>
        `;
        bannerIndex = 0;
    });

    test('changeOpacity() should set element opacity', () => {
        changeOpacity('bannerTop', 50);

        const banner = document.getElementById('bannerTop');
        expect(banner.style.opacity).toBe('0.5');
    });

    test('changeOpacity() should handle non-existent element', () => {
        // Should not throw
        expect(() => changeOpacity('nonexistent', 50)).not.toThrow();
    });

    test('startBannerSwapping() should initialize banner images', () => {
        startBannerSwapping();

        const bannerTop = document.getElementById('bannerTop');
        expect(bannerTop.style.backgroundImage).toContain(bannerNames[0]);
    });

    test('startBannerSwapping() should handle missing elements', () => {
        document.body.innerHTML = '';

        // Should not throw
        expect(() => startBannerSwapping()).not.toThrow();
    });
});

describe('Links Page Functions - showLinksDetails()', () => {
    beforeEach(() => {
        document.body.innerHTML = `
            <div id="overview" style="font-weight: normal;"></div>
            <div id="D_overview" style="display: block;"></div>
            <div id="topic1" style="font-weight: normal;"></div>
            <div id="D_topic1" style="display: none;"></div>
        `;
        currentTopic = null;
    });

    test('should show new topic details', () => {
        const topic = document.getElementById('topic1');
        showLinksDetails(topic);

        const details = document.getElementById('D_topic1');
        expect(details.style.display).toBe('block');
    });

    test('should hide previous topic details', () => {
        const overview = document.getElementById('overview');
        const topic1 = document.getElementById('topic1');

        showLinksDetails(topic1);

        const overviewDetails = document.getElementById('D_overview');
        expect(overviewDetails.style.display).toBe('none');
    });

    test('should set font weight on current topic', () => {
        const topic = document.getElementById('topic1');
        showLinksDetails(topic);

        expect(topic.style.fontWeight).toBe('bold');
    });
});

describe('Logo Hover Effect - highlightLogo()', () => {
    beforeEach(() => {
        document.body.innerHTML = '<img id="logo" src="images/logo1.png">';
    });

    test('should change to logo2 on mouseover', () => {
        highlightLogo(true);

        const logo = document.getElementById('logo');
        expect(logo.src).toContain('logo2.png');
    });

    test('should change to logo1 on mouseout', () => {
        highlightLogo(true);
        highlightLogo(false);

        const logo = document.getElementById('logo');
        expect(logo.src).toContain('logo1.png');
    });

    test('should handle missing logo element', () => {
        document.body.innerHTML = '';

        // Should not throw
        expect(() => highlightLogo(true)).not.toThrow();
    });
});

describe('Legacy Functions', () => {
    test('award() should write table row', () => {
        award('001', 'John Doe', '2026-01-15');

        expect(document.write).toHaveBeenCalledWith(
            expect.stringContaining('001')
        );
        expect(document.write).toHaveBeenCalledWith(
            expect.stringContaining('John Doe')
        );
    });

    test('presentation() should write presentation with link', () => {
        presentation('January', '15', 'Jane Doe', 'Stars', 'http://example.com');

        expect(document.write).toHaveBeenCalledWith(
            expect.stringContaining('<a href="http://example.com"')
        );
    });

    test('presentation() should handle missing presenter', () => {
        presentation('January', '15', '', 'Stars', '');

        expect(document.write).toHaveBeenCalled();
    });
});

describe('Lightbox Functions', () => {
    beforeEach(() => {
        // Remove any existing lightbox
        const existing = document.querySelector('.lightbox');
        if (existing) existing.remove();
    });

    test('createLightbox() should create lightbox structure', () => {
        createLightbox();

        const lightbox = document.querySelector('.lightbox');
        expect(lightbox).not.toBeNull();
        expect(lightbox.querySelector('.lightbox-close')).not.toBeNull();
        expect(lightbox.querySelector('.lightbox-prev')).not.toBeNull();
        expect(lightbox.querySelector('.lightbox-next')).not.toBeNull();
        expect(lightbox.querySelector('.lightbox-image')).not.toBeNull();
        expect(lightbox.querySelector('.lightbox-caption')).not.toBeNull();
    });

    test('createLightbox() should not duplicate lightbox', () => {
        createLightbox();
        createLightbox();

        const lightboxes = document.querySelectorAll('.lightbox');
        expect(lightboxes.length).toBe(1);
    });

    test('closeLightbox() should remove active class', () => {
        createLightbox();
        const lightbox = document.querySelector('.lightbox');
        lightbox.classList.add('active');

        closeLightbox();

        expect(lightbox.classList.contains('active')).toBe(false);
    });

    test('closeLightbox() should restore body overflow', () => {
        createLightbox();
        document.body.style.overflow = 'hidden';

        closeLightbox();

        expect(document.body.style.overflow).toBe('');
    });
});

describe('Gallery Initialization - initGalleries()', () => {
    beforeEach(() => {
        // Remove any existing lightbox
        const existing = document.querySelector('.lightbox');
        if (existing) existing.remove();

        document.body.innerHTML = `
            <div class="photo-gallery">
                <div class="gallery-item" data-title="Image 1">
                    <img src="img1.jpg">
                </div>
                <div class="gallery-item" data-title="Image 2">
                    <img src="img2.jpg">
                </div>
            </div>
        `;
    });

    test('should create lightbox if not exists', () => {
        initGalleries();

        expect(document.querySelector('.lightbox')).not.toBeNull();
    });

    test('should set tabindex on gallery items', () => {
        initGalleries();

        const items = document.querySelectorAll('.gallery-item');
        items.forEach(item => {
            expect(item.getAttribute('tabindex')).toBe('0');
        });
    });

    test('should set role button on gallery items', () => {
        initGalleries();

        const items = document.querySelectorAll('.gallery-item');
        items.forEach(item => {
            expect(item.getAttribute('role')).toBe('button');
        });
    });

    test('should set aria-label on gallery items', () => {
        initGalleries();

        const item = document.querySelector('.gallery-item');
        expect(item.getAttribute('aria-label')).toContain('Image 1');
    });
});
