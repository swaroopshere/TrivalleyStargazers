/**
 * Jest test setup file
 * Sets up the DOM environment and mocks for testing
 */

// Mock window.location
delete window.location;
window.location = {
    href: 'http://localhost/tvs/',
    search: '',
    assign: jest.fn(),
    replace: jest.fn(),
    reload: jest.fn()
};

// Mock window.setTimeout and setInterval
jest.useFakeTimers();

// Mock alert
window.alert = jest.fn();

// Mock document.write (used by legacy functions)
document.write = jest.fn();

// Helper to reset all mocks between tests
beforeEach(() => {
    jest.clearAllMocks();
    document.body.innerHTML = '';
    window.location.search = '';
    window.location.href = 'http://localhost/tvs/';
});

// Clean up after tests
afterEach(() => {
    jest.clearAllTimers();
});
