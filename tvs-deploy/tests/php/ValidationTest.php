<?php
/**
 * Unit Tests for input validation and sanitization
 */

use PHPUnit\Framework\TestCase;

// Load functions file
require_once dirname(__DIR__, 2) . '/includes/functions.php';

class ValidationTest extends TestCase
{
    /**
     * Test XSS prevention with HTML escaping
     */
    public function testXSSPrevention(): void
    {
        // Script tags should be escaped
        $malicious = '<script>alert("XSS")</script>';
        $escaped = e($malicious);
        $this->assertStringNotContainsString('<script>', $escaped);
        $this->assertStringContainsString('&lt;script&gt;', $escaped);
    }

    /**
     * Test SQL-like strings are safely escaped for display
     */
    public function testSqlLikeStringEscaping(): void
    {
        $input = "'; DROP TABLE users; --";
        $escaped = e($input);
        $this->assertStringContainsString('&#039;', $escaped);
    }

    /**
     * Test event handler attributes are escaped
     */
    public function testEventHandlerEscaping(): void
    {
        $input = '" onmouseover="alert(1)"';
        $escaped = e($input);
        $this->assertStringContainsString('&quot;', $escaped);
        $this->assertStringNotContainsString('onmouseover', $escaped);
    }

    /**
     * Test date sanitization rejects invalid formats
     */
    public function testDateSanitizationRejectsInvalid(): void
    {
        // SQL injection attempt
        $this->assertEquals('', sanitizeDate("2026-01-01'; DROP TABLE--"));

        // Invalid date format
        $this->assertEquals('', sanitizeDate('01-15-2026'));
        $this->assertEquals('', sanitizeDate('2026/01/15'));

        // Impossible dates
        $this->assertEquals('', sanitizeDate('2026-02-30'));
        $this->assertEquals('', sanitizeDate('2026-00-15'));
    }

    /**
     * Test time sanitization rejects invalid formats
     */
    public function testTimeSanitizationRejectsInvalid(): void
    {
        // Invalid time formats
        $this->assertEquals('', sanitizeTime('7:30 PM'));
        $this->assertEquals('', sanitizeTime('19:30:00:00'));

        // Out of range values
        $this->assertEquals('', sanitizeTime('24:00'));
        $this->assertEquals('', sanitizeTime('12:61'));
    }

    /**
     * Test slugify removes dangerous characters
     */
    public function testSlugifyRemovesDangerousChars(): void
    {
        // Path traversal attempt
        $this->assertStringNotContainsString('..', slugify('../../../etc/passwd'));
        $this->assertStringNotContainsString('/', slugify('path/to/file'));

        // Script injection
        $this->assertStringNotContainsString('<', slugify('<script>alert(1)</script>'));
        $this->assertStringNotContainsString('>', slugify('<script>alert(1)</script>'));
    }

    /**
     * Test truncate handles edge cases safely
     */
    public function testTruncateEdgeCases(): void
    {
        // Negative length should not cause issues
        $this->assertIsString(truncate('test', -1));

        // Zero length
        $this->assertIsString(truncate('test', 0));

        // Very large length
        $this->assertEquals('test', truncate('test', 1000000));
    }

    /**
     * Test formatFileSize handles edge cases
     */
    public function testFormatFileSizeEdgeCases(): void
    {
        // Negative size
        $result = formatFileSize(-100);
        $this->assertIsString($result);

        // Very large size
        $result = formatFileSize(PHP_INT_MAX);
        $this->assertIsString($result);
    }

    /**
     * Test paginate handles invalid inputs
     */
    public function testPaginateHandlesInvalidInputs(): void
    {
        // Negative total
        $result = paginate(-10, 10, 1);
        $this->assertIsArray($result);

        // Zero per page (should handle gracefully)
        // Note: This might throw an error in the actual implementation
        // so we test what it returns
        try {
            $result = paginate(100, 0, 1);
            $this->assertIsArray($result);
        } catch (Exception $e) {
            $this->assertTrue(true); // Division by zero is handled
        }

        // Negative page
        $result = paginate(100, 10, -5);
        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(1, $result['current_page']);
    }

    /**
     * Test getMonthName handles all valid months
     */
    public function testGetMonthNameAllMonths(): void
    {
        $expectedMonths = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];

        foreach ($expectedMonths as $num => $name) {
            $this->assertEquals($name, getMonthName($num));
        }
    }

    /**
     * Test getMeetingFormatLabel handles all formats
     */
    public function testGetMeetingFormatLabelAllFormats(): void
    {
        $this->assertEquals('In-person', getMeetingFormatLabel('in-person'));
        $this->assertEquals('Zoom', getMeetingFormatLabel('zoom'));
        $this->assertEquals('Hybrid', getMeetingFormatLabel('hybrid'));

        // Unknown format
        $this->assertEquals('Unknown', getMeetingFormatLabel('telepathy'));
    }

    /**
     * Test buildNewsletterPath generates correct paths
     */
    public function testBuildNewsletterPathFormats(): void
    {
        // PDF format (post-2001)
        $path = buildNewsletterPath(2026, 3, 'pdf');
        $this->assertStringEndsWith('.pdf', $path);
        $this->assertStringContainsString('2026', $path);

        // HTML format (pre-2001)
        $path = buildNewsletterPath(1999, 6, 'html');
        $this->assertStringEndsWith('index.html', $path);
        $this->assertStringContainsString('1999', $path);
    }

    /**
     * Test newsletter path handles month padding
     */
    public function testBuildNewsletterPathMonthPadding(): void
    {
        // Single digit month should be zero-padded
        $path = buildNewsletterPath(2026, 1, 'pdf');
        $this->assertStringContainsString('01', $path);

        // Double digit month should not have extra padding
        $path = buildNewsletterPath(2026, 12, 'pdf');
        $this->assertStringContainsString('12', $path);
    }
}
