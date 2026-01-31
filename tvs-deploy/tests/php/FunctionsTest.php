<?php
/**
 * Unit Tests for functions.php utility functions
 */

use PHPUnit\Framework\TestCase;

// Load functions file
require_once dirname(__DIR__, 2) . '/includes/functions.php';

class FunctionsTest extends TestCase
{
    /**
     * Test HTML escaping function
     */
    public function testEscapeHtmlSpecialChars(): void
    {
        $this->assertEquals('&lt;script&gt;', e('<script>'));
        $this->assertEquals('&amp;', e('&'));
        $this->assertEquals('&quot;test&quot;', e('"test"'));
        $this->assertEquals("it&#039;s", e("it's"));
    }

    public function testEscapeEmptyString(): void
    {
        $this->assertEquals('', e(''));
    }

    public function testEscapeNullReturnsEmptyString(): void
    {
        $this->assertEquals('', e(null));
    }

    /**
     * Test date formatting
     */
    public function testFormatDateWithValidDate(): void
    {
        $this->assertEquals('January 15, 2026', formatDate('2026-01-15'));
        $this->assertEquals('Jan 15', formatDate('2026-01-15', 'M j'));
        $this->assertEquals('2026', formatDate('2026-01-15', 'Y'));
    }

    public function testFormatDateWithInvalidDate(): void
    {
        $this->assertEquals('', formatDate('invalid-date'));
        $this->assertEquals('', formatDate(''));
        $this->assertEquals('', formatDate(null));
    }

    public function testFormatDateWithDifferentFormats(): void
    {
        $this->assertEquals('Friday, January 16, 2026', formatDate('2026-01-16', 'l, F j, Y'));
        $this->assertEquals('01/16/26', formatDate('2026-01-16', 'm/d/y'));
    }

    /**
     * Test time formatting
     */
    public function testFormatTimeWithValidTime(): void
    {
        $this->assertEquals('7:30 PM', formatTime('19:30'));
        $this->assertEquals('12:00 PM', formatTime('12:00'));
        $this->assertEquals('12:00 AM', formatTime('00:00'));
    }

    public function testFormatTimeWithInvalidTime(): void
    {
        $this->assertEquals('', formatTime('invalid'));
        $this->assertEquals('', formatTime(''));
        $this->assertEquals('', formatTime(null));
    }

    public function testFormatTimeWithCustomFormat(): void
    {
        $this->assertEquals('19:30', formatTime('19:30', 'H:i'));
        $this->assertEquals('7:30:00 pm', formatTime('19:30:00', 'g:i:s a'));
    }

    /**
     * Test file size formatting
     */
    public function testFormatFileSizeBytes(): void
    {
        $this->assertEquals('0 B', formatFileSize(0));
        $this->assertEquals('100 B', formatFileSize(100));
        $this->assertEquals('1023 B', formatFileSize(1023));
    }

    public function testFormatFileSizeKilobytes(): void
    {
        $this->assertEquals('1.00 KB', formatFileSize(1024));
        $this->assertEquals('1.50 KB', formatFileSize(1536));
        $this->assertEquals('999.00 KB', formatFileSize(1022976));
    }

    public function testFormatFileSizeMegabytes(): void
    {
        $this->assertEquals('1.00 MB', formatFileSize(1048576));
        $this->assertEquals('10.00 MB', formatFileSize(10485760));
        $this->assertEquals('5.50 MB', formatFileSize(5767168));
    }

    public function testFormatFileSizeGigabytes(): void
    {
        $this->assertEquals('1.00 GB', formatFileSize(1073741824));
        $this->assertEquals('2.50 GB', formatFileSize(2684354560));
    }

    /**
     * Test string truncation
     */
    public function testTruncateShortString(): void
    {
        $this->assertEquals('Hello', truncate('Hello', 10));
        $this->assertEquals('Test', truncate('Test', 100));
    }

    public function testTruncateLongString(): void
    {
        $this->assertEquals('Hello...', truncate('Hello World', 8));
        $this->assertEquals('This is a...', truncate('This is a long string', 12));
    }

    public function testTruncateWithCustomSuffix(): void
    {
        $this->assertEquals('Hello[more]', truncate('Hello World', 11, '[more]'));
        $this->assertEquals('Test--', truncate('Testing 123', 6, '--'));
    }

    public function testTruncateEmptyString(): void
    {
        $this->assertEquals('', truncate('', 10));
    }

    /**
     * Test slugify function
     */
    public function testSlugifyBasicString(): void
    {
        $this->assertEquals('hello-world', slugify('Hello World'));
        $this->assertEquals('test-string', slugify('Test String'));
    }

    public function testSlugifyWithSpecialChars(): void
    {
        $this->assertEquals('hello-world', slugify('Hello, World!'));
        $this->assertEquals('test-123', slugify('Test @#$ 123'));
        $this->assertEquals('a-b-c', slugify('A & B & C'));
    }

    public function testSlugifyWithMultipleSpaces(): void
    {
        $this->assertEquals('hello-world', slugify('Hello    World'));
        $this->assertEquals('test', slugify('   Test   '));
    }

    public function testSlugifyWithNumbers(): void
    {
        $this->assertEquals('test-123-abc', slugify('Test 123 ABC'));
        $this->assertEquals('2026-meeting', slugify('2026 Meeting'));
    }

    /**
     * Test date sanitization
     */
    public function testSanitizeDateValid(): void
    {
        $this->assertEquals('2026-01-15', sanitizeDate('2026-01-15'));
        $this->assertEquals('2025-12-31', sanitizeDate('2025-12-31'));
    }

    public function testSanitizeDateInvalid(): void
    {
        $this->assertEquals('', sanitizeDate('invalid'));
        $this->assertEquals('', sanitizeDate('2026-13-01')); // Invalid month
        $this->assertEquals('', sanitizeDate('2026-01-32')); // Invalid day
        $this->assertEquals('', sanitizeDate(''));
    }

    /**
     * Test time sanitization
     */
    public function testSanitizeTimeValid(): void
    {
        $this->assertEquals('19:30', sanitizeTime('19:30'));
        $this->assertEquals('00:00', sanitizeTime('00:00'));
        $this->assertEquals('23:59', sanitizeTime('23:59'));
    }

    public function testSanitizeTimeWithSeconds(): void
    {
        $this->assertEquals('19:30:00', sanitizeTime('19:30:00'));
    }

    public function testSanitizeTimeInvalid(): void
    {
        $this->assertEquals('', sanitizeTime('invalid'));
        $this->assertEquals('', sanitizeTime('25:00')); // Invalid hour
        $this->assertEquals('', sanitizeTime('12:60')); // Invalid minute
        $this->assertEquals('', sanitizeTime(''));
    }

    /**
     * Test meeting format label
     */
    public function testGetMeetingFormatLabel(): void
    {
        $this->assertEquals('In-person', getMeetingFormatLabel('in-person'));
        $this->assertEquals('Zoom', getMeetingFormatLabel('zoom'));
        $this->assertEquals('Hybrid', getMeetingFormatLabel('hybrid'));
    }

    public function testGetMeetingFormatLabelUnknown(): void
    {
        $this->assertEquals('Unknown', getMeetingFormatLabel('unknown'));
        $this->assertEquals('Unknown', getMeetingFormatLabel(''));
    }

    /**
     * Test month name retrieval
     */
    public function testGetMonthName(): void
    {
        $this->assertEquals('January', getMonthName(1));
        $this->assertEquals('June', getMonthName(6));
        $this->assertEquals('December', getMonthName(12));
    }

    public function testGetMonthNameInvalid(): void
    {
        $this->assertEquals('', getMonthName(0));
        $this->assertEquals('', getMonthName(13));
        $this->assertEquals('', getMonthName(-1));
    }

    /**
     * Test pagination calculation
     */
    public function testPaginateBasic(): void
    {
        $result = paginate(100, 10, 1);

        $this->assertEquals(10, $result['total_pages']);
        $this->assertEquals(1, $result['current_page']);
        $this->assertEquals(0, $result['offset']);
        $this->assertFalse($result['has_prev']);
        $this->assertTrue($result['has_next']);
    }

    public function testPaginateMiddlePage(): void
    {
        $result = paginate(100, 10, 5);

        $this->assertEquals(10, $result['total_pages']);
        $this->assertEquals(5, $result['current_page']);
        $this->assertEquals(40, $result['offset']);
        $this->assertTrue($result['has_prev']);
        $this->assertTrue($result['has_next']);
    }

    public function testPaginateLastPage(): void
    {
        $result = paginate(100, 10, 10);

        $this->assertEquals(10, $result['total_pages']);
        $this->assertEquals(10, $result['current_page']);
        $this->assertEquals(90, $result['offset']);
        $this->assertTrue($result['has_prev']);
        $this->assertFalse($result['has_next']);
    }

    public function testPaginatePageBeyondTotal(): void
    {
        $result = paginate(100, 10, 15);

        $this->assertEquals(10, $result['total_pages']);
        $this->assertEquals(10, $result['current_page']); // Should clamp to max
    }

    public function testPaginatePageZero(): void
    {
        $result = paginate(100, 10, 0);

        $this->assertEquals(1, $result['current_page']); // Should clamp to 1
    }

    public function testPaginateSinglePage(): void
    {
        $result = paginate(5, 10, 1);

        $this->assertEquals(1, $result['total_pages']);
        $this->assertFalse($result['has_prev']);
        $this->assertFalse($result['has_next']);
    }

    public function testPaginateNoItems(): void
    {
        $result = paginate(0, 10, 1);

        $this->assertEquals(0, $result['total_pages']);
        $this->assertEquals(1, $result['current_page']);
    }

    /**
     * Test newsletter path building
     */
    public function testBuildNewsletterPathPdf(): void
    {
        $path = buildNewsletterPath(2026, 1, 'pdf');
        $this->assertEquals('newsletters/2026/tvsnews0126.pdf', $path);

        $path = buildNewsletterPath(2025, 12, 'pdf');
        $this->assertEquals('newsletters/2025/tvsnews1225.pdf', $path);
    }

    public function testBuildNewsletterPathHtml(): void
    {
        $path = buildNewsletterPath(2000, 6, 'html');
        $this->assertEquals('newsletters/2000/0600/index.html', $path);

        $path = buildNewsletterPath(1999, 11, 'html');
        $this->assertEquals('newsletters/1999/1199/index.html', $path);
    }

    /**
     * Test current page detection
     */
    public function testGetCurrentPage(): void
    {
        $_SERVER['SCRIPT_NAME'] = '/admin/meetings.php';
        $this->assertEquals('meetings', getCurrentPage());

        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $this->assertEquals('index', getCurrentPage());
    }

    /**
     * Test isCurrentPage
     */
    public function testIsCurrentPage(): void
    {
        $_SERVER['SCRIPT_NAME'] = '/admin/meetings.php';
        $this->assertTrue(isCurrentPage('meetings'));
        $this->assertFalse(isCurrentPage('events'));
    }
}
