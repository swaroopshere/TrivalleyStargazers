# Design System Documentation

This document describes the CSS architecture, design tokens, and component library used in the TVS Beta website.

## Table of Contents

- [Design Tokens](#design-tokens)
- [Typography](#typography)
- [Spacing System](#spacing-system)
- [Layout](#layout)
- [Components](#components)
- [Responsive Design](#responsive-design)
- [Naming Conventions](#naming-conventions)
- [Adding New Components](#adding-new-components)

---

## Design Tokens

Design tokens are CSS custom properties defined in `:root` that provide consistent values throughout the site.

### Color Palette

```css
:root {
  /* Primary Colors - TVS Brand */
  --color-primary: #0a1628;        /* Deep Space Navy - main brand color */
  --color-primary-light: #1a2d4a;  /* Lighter navy for hover states */
  --color-accent: #d4a84b;         /* Celestial Gold - TVS signature */
  --color-accent-light: #f0d78c;   /* Light gold for highlights */

  /* Neutral Colors */
  --color-bg: #ffffff;             /* White - main background */
  --color-bg-alt: #f5f7fa;         /* Light gray - alternate sections */
  --color-text: #2c3e50;           /* Dark gray - body text */
  --color-text-light: #64748b;     /* Medium gray - secondary text */
  --color-border: #e2e8f0;         /* Light gray - borders */

  /* Interactive Colors */
  --color-link: #2563eb;           /* Blue - links */
  --color-link-hover: #1d4ed8;     /* Darker blue - link hover */

  /* Status Colors */
  --color-success: #22c55e;        /* Green - success messages */
  --color-warning: #f59e0b;        /* Amber - warnings */
  --color-error: #ef4444;          /* Red - errors */
}
```

#### Color Usage Guidelines

| Color | Use For |
|-------|---------|
| `--color-primary` | Header, footer, primary buttons, headings |
| `--color-accent` | Highlights, borders, hover states, call-to-action |
| `--color-bg` | Main page background |
| `--color-bg-alt` | Alternating sections, cards |
| `--color-text` | Body text, paragraphs |
| `--color-text-light` | Secondary text, captions, metadata |
| `--color-link` | Hyperlinks |
| `--color-success` | Success alerts, confirmations |
| `--color-warning` | Warning alerts |
| `--color-error` | Error alerts, validation messages |

### Visual Effects

```css
:root {
  /* Shadows */
  --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
  --shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
  --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1), 0 2px 4px rgba(0, 0, 0, 0.06);
  --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1), 0 4px 6px rgba(0, 0, 0, 0.05);

  /* Transitions */
  --transition-fast: 150ms ease;
  --transition-base: 200ms ease;
  --transition-slow: 300ms ease;

  /* Border Radius */
  --radius-sm: 0.25rem;   /* 4px - small elements */
  --radius: 0.5rem;       /* 8px - default */
  --radius-lg: 0.75rem;   /* 12px - cards */
  --radius-xl: 1rem;      /* 16px - large cards */
}
```

---

## Typography

### Font Stack

```css
:root {
  --font-primary: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI',
                  Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
  --font-heading: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI',
                  Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
}
```

### Font Sizes

```css
:root {
  --text-xs: 0.75rem;    /* 12px - captions, fine print */
  --text-sm: 0.875rem;   /* 14px - small text, labels */
  --text-base: 1rem;     /* 16px - body text */
  --text-lg: 1.125rem;   /* 18px - lead paragraphs */
  --text-xl: 1.25rem;    /* 20px - small headings */
  --text-2xl: 1.5rem;    /* 24px - section headings */
  --text-3xl: 2rem;      /* 32px - page titles */
  --text-4xl: 2.5rem;    /* 40px - hero headings */
}
```

### Font Weights

| Weight | Value | Use For |
|--------|-------|---------|
| Normal | 400 | Body text, paragraphs |
| Medium | 500 | Emphasis, labels |
| Semibold | 600 | Subheadings, buttons |
| Bold | 700 | Headings, important text |

### Typography Scale Example

```css
/* Hero headline */
.hero-title {
  font-size: var(--text-4xl);
  font-weight: 700;
  line-height: 1.1;
}

/* Section heading */
.section-title {
  font-size: var(--text-2xl);
  font-weight: 600;
  color: var(--color-primary);
}

/* Body text */
.body-text {
  font-size: var(--text-base);
  font-weight: 400;
  line-height: 1.6;
  color: var(--color-text);
}

/* Caption/metadata */
.caption {
  font-size: var(--text-sm);
  color: var(--color-text-light);
}
```

---

## Spacing System

The spacing system uses a consistent scale based on 4px increments.

```css
:root {
  --space-1: 0.25rem;    /*  4px */
  --space-2: 0.5rem;     /*  8px */
  --space-3: 0.75rem;    /* 12px */
  --space-4: 1rem;       /* 16px */
  --space-5: 1.25rem;    /* 20px */
  --space-6: 1.5rem;     /* 24px */
  --space-8: 2rem;       /* 32px */
  --space-10: 2.5rem;    /* 40px */
  --space-12: 3rem;      /* 48px */
  --space-16: 4rem;      /* 64px */
}
```

### Spacing Guidelines

| Spacing | Use For |
|---------|---------|
| `--space-1` to `--space-2` | Inline spacing, icon gaps |
| `--space-3` to `--space-4` | Component internal padding |
| `--space-6` to `--space-8` | Section padding, card margins |
| `--space-10` to `--space-16` | Major section separation |

### Usage Example

```css
.card {
  padding: var(--space-6);        /* 24px internal padding */
  margin-bottom: var(--space-4);  /* 16px gap between cards */
}

.card-title {
  margin-bottom: var(--space-2);  /* 8px below title */
}

.section {
  padding: var(--space-12) 0;     /* 48px vertical padding */
}
```

---

## Layout

### Layout Constants

```css
:root {
  --max-width: 1200px;      /* Maximum content width */
  --header-height: 70px;    /* Fixed header height */
  --banner-height: 280px;   /* Hero banner height */
}
```

### Container

The `.container` class provides centered, max-width content with responsive padding.

```css
.container {
  max-width: var(--max-width);
  margin: 0 auto;
  padding: 0 var(--space-4);
}

@media (min-width: 768px) {
  .container {
    padding: 0 var(--space-8);
  }
}
```

### Page Structure

```html
<div class="page-wrapper">
  <header class="site-header">...</header>
  <main class="main-content">
    <section class="hero-section">...</section>
    <section class="section">
      <div class="container">...</div>
    </section>
  </main>
  <footer class="site-footer">...</footer>
</div>
```

```css
.page-wrapper {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.main-content {
  flex: 1;
}
```

### Grid System

Use CSS Grid for layouts:

```css
/* Two-column grid */
.grid-2 {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-6);
}

@media (min-width: 768px) {
  .grid-2 {
    grid-template-columns: repeat(2, 1fr);
  }
}

/* Three-column grid */
.grid-3 {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-6);
}

@media (min-width: 640px) {
  .grid-3 {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 1024px) {
  .grid-3 {
    grid-template-columns: repeat(3, 1fr);
  }
}

/* Stats grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: var(--space-4);
}
```

---

## Components

### Cards

```css
/* Base card */
.card {
  background: var(--color-bg);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow);
  padding: var(--space-6);
  transition: box-shadow var(--transition-base);
}

.card:hover {
  box-shadow: var(--shadow-md);
}

/* Card with gold accent border */
.card-accent {
  border-left: 4px solid var(--color-accent);
}

/* Meeting-specific card */
.meeting-card {
  background: var(--color-bg);
  border-radius: var(--radius-lg);
  padding: var(--space-6);
  border: 1px solid var(--color-border);
}

.meeting-card-header {
  display: flex;
  align-items: center;
  gap: var(--space-3);
  margin-bottom: var(--space-4);
}
```

**HTML Example:**

```html
<div class="card card-accent">
  <h3 class="card-title">Card Title</h3>
  <p class="card-text">Card content goes here.</p>
</div>
```

### Buttons

```css
/* Base button */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: var(--space-2);
  padding: var(--space-3) var(--space-6);
  font-size: var(--text-base);
  font-weight: 500;
  border-radius: var(--radius);
  border: none;
  cursor: pointer;
  transition: all var(--transition-base);
  text-decoration: none;
}

/* Primary button - gold */
.btn-primary {
  background: var(--color-accent);
  color: var(--color-primary);
}

.btn-primary:hover {
  background: var(--color-accent-light);
}

/* Secondary button - navy */
.btn-secondary {
  background: var(--color-primary);
  color: white;
}

.btn-secondary:hover {
  background: var(--color-primary-light);
}

/* Outline button */
.btn-outline {
  background: transparent;
  border: 2px solid var(--color-primary);
  color: var(--color-primary);
}

.btn-outline:hover {
  background: var(--color-primary);
  color: white;
}

/* Danger button */
.btn-danger {
  background: var(--color-error);
  color: white;
}

.btn-danger:hover {
  background: #dc2626;
}

/* Small button */
.btn-sm {
  padding: var(--space-2) var(--space-4);
  font-size: var(--text-sm);
}
```

**HTML Example:**

```html
<button class="btn btn-primary">Primary Action</button>
<a href="/page" class="btn btn-secondary">Secondary Link</a>
<button class="btn btn-outline btn-sm">Small Outline</button>
```

### Forms

```css
/* Form group */
.form-group {
  margin-bottom: var(--space-4);
}

/* Labels */
.form-label {
  display: block;
  font-size: var(--text-sm);
  font-weight: 500;
  color: var(--color-text);
  margin-bottom: var(--space-2);
}

/* Input fields */
.form-input,
.form-select,
.form-textarea {
  width: 100%;
  padding: var(--space-3);
  font-size: var(--text-base);
  border: 1px solid var(--color-border);
  border-radius: var(--radius);
  background: var(--color-bg);
  transition: border-color var(--transition-base),
              box-shadow var(--transition-base);
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
  outline: none;
  border-color: var(--color-accent);
  box-shadow: 0 0 0 3px rgba(212, 168, 75, 0.1);
}

/* Textarea */
.form-textarea {
  min-height: 120px;
  resize: vertical;
}

/* Checkbox/Radio */
.form-check {
  display: flex;
  align-items: center;
  gap: var(--space-2);
}

.form-check-input {
  width: 18px;
  height: 18px;
  accent-color: var(--color-accent);
}

/* Error state */
.form-input.error {
  border-color: var(--color-error);
}

.form-error {
  color: var(--color-error);
  font-size: var(--text-sm);
  margin-top: var(--space-1);
}
```

**HTML Example:**

```html
<div class="form-group">
  <label class="form-label" for="email">Email Address</label>
  <input type="email" id="email" class="form-input" required>
</div>

<div class="form-group">
  <label class="form-label" for="message">Message</label>
  <textarea id="message" class="form-textarea"></textarea>
</div>

<div class="form-check">
  <input type="checkbox" id="agree" class="form-check-input">
  <label for="agree">I agree to the terms</label>
</div>
```

### Alerts

```css
.alert {
  padding: var(--space-4);
  border-radius: var(--radius);
  margin-bottom: var(--space-4);
  font-size: var(--text-sm);
}

.alert-success {
  background: #dcfce7;
  color: #166534;
  border: 1px solid #bbf7d0;
}

.alert-error {
  background: #fef2f2;
  color: #dc2626;
  border: 1px solid #fecaca;
}

.alert-warning {
  background: #fffbeb;
  color: #b45309;
  border: 1px solid #fde68a;
}

.alert-info {
  background: #eff6ff;
  color: #1d4ed8;
  border: 1px solid #bfdbfe;
}
```

**HTML Example:**

```html
<div class="alert alert-success">
  Your changes have been saved successfully.
</div>
```

### Navigation

```css
/* Site header */
.site-header {
  position: sticky;
  top: 0;
  z-index: 100;
  background: var(--color-primary);
  height: var(--header-height);
}

.nav-container {
  max-width: var(--max-width);
  margin: 0 auto;
  padding: 0 var(--space-4);
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 100%;
}

/* Desktop navigation */
.nav-menu {
  display: none;
  list-style: none;
  margin: 0;
  padding: 0;
  gap: var(--space-1);
}

@media (min-width: 1024px) {
  .nav-menu {
    display: flex;
  }
}

.nav-link {
  display: block;
  padding: var(--space-2) var(--space-3);
  color: white;
  text-decoration: none;
  font-size: var(--text-sm);
  border-radius: var(--radius);
  transition: background var(--transition-base);
}

.nav-link:hover,
.nav-link.active {
  background: rgba(255, 255, 255, 0.1);
}

/* Mobile menu toggle */
.mobile-menu-toggle {
  display: flex;
  padding: var(--space-2);
  background: none;
  border: none;
  color: white;
  cursor: pointer;
}

@media (min-width: 1024px) {
  .mobile-menu-toggle {
    display: none;
  }
}
```

### Hero Section

```css
.hero-section {
  position: relative;
  height: var(--banner-height);
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  color: white;
  overflow: hidden;
}

.hero-banner {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-size: cover;
  background-position: center;
}

.hero-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(
    to bottom,
    rgba(10, 22, 40, 0.7),
    rgba(10, 22, 40, 0.9)
  );
}

.hero-content {
  position: relative;
  z-index: 1;
  max-width: 800px;
  padding: var(--space-4);
}

.hero-title {
  font-size: var(--text-3xl);
  font-weight: 700;
  margin-bottom: var(--space-2);
}

@media (min-width: 768px) {
  .hero-title {
    font-size: var(--text-4xl);
  }
}
```

### Sections

```css
/* Default section */
.section {
  padding: var(--space-12) 0;
}

/* Alternate background section */
.section-alt {
  background: var(--color-bg-alt);
}

/* Intro section with gold accent */
.intro {
  border-left: 4px solid var(--color-accent);
  padding-left: var(--space-6);
  margin-bottom: var(--space-8);
}

.intro p {
  font-size: var(--text-lg);
  color: var(--color-text);
  line-height: 1.7;
}
```

### Tables

```css
.table {
  width: 100%;
  border-collapse: collapse;
  font-size: var(--text-sm);
}

.table th,
.table td {
  padding: var(--space-3) var(--space-4);
  text-align: left;
  border-bottom: 1px solid var(--color-border);
}

.table th {
  background: var(--color-bg-alt);
  font-weight: 600;
  color: var(--color-text);
}

.table tbody tr:hover {
  background: var(--color-bg-alt);
}

/* Responsive table wrapper */
.table-responsive {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}
```

---

## Responsive Design

### Breakpoints

The site uses a **mobile-first** approach with three main breakpoints:

| Breakpoint | Width | Target Devices |
|------------|-------|----------------|
| Default | < 640px | Mobile phones |
| sm | 640px | Small tablets, large phones |
| md | 768px | Tablets |
| lg | 1024px | Desktops, laptops |

### Media Query Usage

```css
/* Mobile first - default styles for mobile */
.element {
  padding: var(--space-4);
  font-size: var(--text-base);
}

/* Small tablets */
@media (min-width: 640px) {
  .element {
    padding: var(--space-6);
  }
}

/* Tablets */
@media (min-width: 768px) {
  .element {
    padding: var(--space-8);
    font-size: var(--text-lg);
  }
}

/* Desktop */
@media (min-width: 1024px) {
  .element {
    padding: var(--space-10);
  }
}
```

### Responsive Patterns

**Navigation:**
- Mobile: Hamburger menu with slide-out drawer
- Desktop (1024px+): Horizontal menu bar

**Grids:**
- Mobile: Single column
- Tablet (640px/768px): Two columns
- Desktop (1024px+): Three or more columns

**Typography:**
- Mobile: Slightly smaller headings
- Desktop: Full-size headings

**Containers:**
- Mobile: 16px horizontal padding
- Tablet+: 32px horizontal padding

---

## Naming Conventions

The codebase uses a hybrid naming approach combining **BEM-inspired** naming with **utility classes**.

### BEM-Inspired Components

```css
/* Block */
.card { }

/* Block__Element */
.card-title { }      /* Simplified: hyphen instead of __ */
.card-text { }
.card-footer { }

/* Block--Modifier */
.card-accent { }     /* Simplified: hyphen instead of -- */
.card-large { }
```

### Utility Classes

```css
/* Text utilities */
.text-center { text-align: center; }
.text-left { text-align: left; }
.text-right { text-align: right; }

/* Display utilities */
.hidden { display: none; }
.block { display: block; }
.flex { display: flex; }

/* Spacing utilities (margin) */
.mb-0 { margin-bottom: 0; }
.mb-4 { margin-bottom: var(--space-4); }
.mt-8 { margin-top: var(--space-8); }
```

### Naming Guidelines

1. **Use lowercase with hyphens**: `.my-component`, not `.myComponent` or `.my_component`
2. **Be descriptive**: `.meeting-card` not `.mc`
3. **Use prefixes for context**:
   - `.nav-` for navigation elements
   - `.form-` for form elements
   - `.btn-` for button variants
   - `.alert-` for alert variants
4. **Keep specificity low**: Avoid deep nesting, prefer single class selectors

---

## Adding New Components

### Step 1: Define the Component

Identify the component's purpose and variations needed.

### Step 2: Use Design Tokens

Always use CSS custom properties for colors, spacing, and typography:

```css
/* Good */
.new-component {
  background: var(--color-bg);
  padding: var(--space-4);
  color: var(--color-text);
  font-size: var(--text-base);
  border-radius: var(--radius);
}

/* Avoid */
.new-component {
  background: #ffffff;
  padding: 16px;
  color: #2c3e50;
  font-size: 16px;
  border-radius: 8px;
}
```

### Step 3: Consider Responsive Behavior

```css
.new-component {
  /* Mobile-first base styles */
  padding: var(--space-4);
  flex-direction: column;
}

@media (min-width: 768px) {
  .new-component {
    padding: var(--space-6);
    flex-direction: row;
  }
}
```

### Step 4: Add States

```css
.new-component {
  transition: all var(--transition-base);
}

.new-component:hover {
  box-shadow: var(--shadow-md);
}

.new-component:focus {
  outline: 2px solid var(--color-accent);
  outline-offset: 2px;
}

.new-component.active {
  border-color: var(--color-accent);
}
```

### Step 5: Document in tvs.css

Add the component to the appropriate section in `tvs.css` with a comment:

```css
/* ============================================
   New Component
   ============================================ */

.new-component {
  /* styles */
}
```

### Example: Event Badge Component

```css
/* ============================================
   Event Badge
   ============================================ */

.event-badge {
  display: inline-flex;
  align-items: center;
  gap: var(--space-2);
  padding: var(--space-1) var(--space-3);
  font-size: var(--text-xs);
  font-weight: 500;
  border-radius: var(--radius);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.event-badge-h2o {
  background: #dbeafe;
  color: #1e40af;
}

.event-badge-tesla {
  background: #fef3c7;
  color: #b45309;
}

.event-badge-announcement {
  background: #e0e7ff;
  color: #4338ca;
}
```

---

## Related Documentation

- [Architecture](ARCHITECTURE.md) - System design and structure
- [PHP Reference](PHP-REFERENCE.md) - Backend functions
- [Admin Guide](ADMIN-GUIDE.md) - Admin panel usage
- [Contributing](CONTRIBUTING.md) - Development workflow
