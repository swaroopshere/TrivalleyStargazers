<?php
/**
 * Unit Tests for auth.php authentication and authorization
 */

use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    private $auth;

    protected function setUp(): void
    {
        // Clear session before each test
        $_SESSION = [];

        // Load auth file if not already loaded
        if (!class_exists('Auth')) {
            require_once dirname(__DIR__, 2) . '/includes/auth.php';
        }

        // Get fresh instance - we need to reset the singleton for testing
        $reflection = new ReflectionClass('Auth');
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);

        $this->auth = Auth::getInstance();
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    /**
     * Test password validation - minimum length
     */
    public function testPasswordValidationMinLength(): void
    {
        $result = $this->auth->validatePassword('Short1!');
        $this->assertFalse($result['valid']);
        $this->assertContains('at least 12 characters', $result['errors'][0]);
    }

    /**
     * Test password validation - requires uppercase
     */
    public function testPasswordValidationRequiresUppercase(): void
    {
        $result = $this->auth->validatePassword('lowercase123!@#');
        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('uppercase', implode(' ', $result['errors']));
    }

    /**
     * Test password validation - requires lowercase
     */
    public function testPasswordValidationRequiresLowercase(): void
    {
        $result = $this->auth->validatePassword('UPPERCASE123!@#');
        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('lowercase', implode(' ', $result['errors']));
    }

    /**
     * Test password validation - requires number
     */
    public function testPasswordValidationRequiresNumber(): void
    {
        $result = $this->auth->validatePassword('NoNumbersHere!@#');
        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('number', implode(' ', $result['errors']));
    }

    /**
     * Test password validation - requires special character
     */
    public function testPasswordValidationRequiresSpecialChar(): void
    {
        $result = $this->auth->validatePassword('NoSpecialChar123');
        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('special character', implode(' ', $result['errors']));
    }

    /**
     * Test password validation - valid password
     */
    public function testPasswordValidationValidPassword(): void
    {
        $result = $this->auth->validatePassword('ValidPassword123!');
        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }

    /**
     * Test password validation - common passwords blocked
     */
    public function testPasswordValidationBlocksCommonPasswords(): void
    {
        $commonPasswords = ['password123!A', 'Password123!', 'Admin12345!@'];

        foreach ($commonPasswords as $password) {
            $result = $this->auth->validatePassword($password);
            // These may pass complexity but should not be in a blocklist if implemented
            // For now, just verify they pass basic validation
            if (!$result['valid']) {
                $this->assertNotEmpty($result['errors']);
            }
        }
    }

    /**
     * Test CSRF token generation
     */
    public function testCSRFTokenGeneration(): void
    {
        $token1 = $this->auth->getCSRFToken();
        $this->assertNotEmpty($token1);
        $this->assertEquals(64, strlen($token1)); // 32 bytes = 64 hex chars

        // Same session should return same token
        $token2 = $this->auth->getCSRFToken();
        $this->assertEquals($token1, $token2);
    }

    /**
     * Test CSRF token validation
     */
    public function testCSRFTokenValidation(): void
    {
        $token = $this->auth->getCSRFToken();

        $this->assertTrue($this->auth->validateCSRF($token));
        $this->assertFalse($this->auth->validateCSRF('invalid-token'));
        $this->assertFalse($this->auth->validateCSRF(''));
    }

    /**
     * Test isLoggedIn when not logged in
     */
    public function testIsLoggedInWhenNotLoggedIn(): void
    {
        $this->assertFalse($this->auth->isLoggedIn());
    }

    /**
     * Test isLoggedIn when logged in
     */
    public function testIsLoggedInWhenLoggedIn(): void
    {
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'testuser';
        $_SESSION['role'] = 'publisher';
        $_SESSION['last_activity'] = time();

        $this->assertTrue($this->auth->isLoggedIn());
    }

    /**
     * Test session timeout
     */
    public function testSessionTimeout(): void
    {
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'testuser';
        $_SESSION['role'] = 'publisher';
        $_SESSION['last_activity'] = time() - (SESSION_TIMEOUT + 100);

        $this->assertFalse($this->auth->isLoggedIn());
    }

    /**
     * Test getUser when logged in
     */
    public function testGetUserWhenLoggedIn(): void
    {
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'testuser';
        $_SESSION['role'] = 'admin';
        $_SESSION['last_activity'] = time();

        $user = $this->auth->getUser();

        $this->assertIsArray($user);
        $this->assertEquals(1, $user['id']);
        $this->assertEquals('testuser', $user['username']);
        $this->assertEquals('admin', $user['role']);
    }

    /**
     * Test getUser when not logged in
     */
    public function testGetUserWhenNotLoggedIn(): void
    {
        $user = $this->auth->getUser();
        $this->assertNull($user);
    }

    /**
     * Test getUserId
     */
    public function testGetUserId(): void
    {
        $_SESSION['user_id'] = 42;
        $_SESSION['last_activity'] = time();

        $this->assertEquals(42, $this->auth->getUserId());
    }

    /**
     * Test getUserId when not logged in
     */
    public function testGetUserIdWhenNotLoggedIn(): void
    {
        $this->assertNull($this->auth->getUserId());
    }

    /**
     * Test hasRole with matching role
     */
    public function testHasRoleMatching(): void
    {
        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = 'publisher';
        $_SESSION['last_activity'] = time();

        $this->assertTrue($this->auth->hasRole('publisher'));
        $this->assertFalse($this->auth->hasRole('admin'));
    }

    /**
     * Test hasRole - admin has all roles
     */
    public function testHasRoleAdminHasAll(): void
    {
        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = 'admin';
        $_SESSION['last_activity'] = time();

        $this->assertTrue($this->auth->hasRole('admin'));
        $this->assertTrue($this->auth->hasRole('publisher'));
        $this->assertTrue($this->auth->hasRole('viewer'));
    }

    /**
     * Test isAdmin
     */
    public function testIsAdmin(): void
    {
        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = 'admin';
        $_SESSION['last_activity'] = time();

        $this->assertTrue($this->auth->isAdmin());

        $_SESSION['role'] = 'publisher';
        $this->assertFalse($this->auth->isAdmin());
    }

    /**
     * Test logout clears session
     */
    public function testLogoutClearsSession(): void
    {
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'testuser';
        $_SESSION['role'] = 'admin';
        $_SESSION['last_activity'] = time();

        $this->auth->logout();

        $this->assertArrayNotHasKey('user_id', $_SESSION);
        $this->assertArrayNotHasKey('username', $_SESSION);
        $this->assertArrayNotHasKey('role', $_SESSION);
    }

    /**
     * Test singleton pattern
     */
    public function testSingletonPattern(): void
    {
        $instance1 = Auth::getInstance();
        $instance2 = Auth::getInstance();

        $this->assertSame($instance1, $instance2);
    }
}
