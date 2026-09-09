# Full Workspace Web Programming Rubric Audit

**Audit date:** 2026-09-09  
**Scope:** Entire workspace, including PHP, CSS, JavaScript, SQL, configuration, includes, assets, and Git metadata.  
**Method:** Read-only source inspection, cross-file workflow tracing, PHP syntax checks, JavaScript syntax checks, and Git metadata inspection. No existing project file was modified.

## A. Overall Score

| Category | Score |
|---|---:|
| Functionality & Requirements | 13/20 |
| PHP Code Quality & Structure | 13/20 |
| Database Integration | 17/20 |
| Form Handling & Validation | 7/10 |
| Session/Authentication | 7/10 |
| Error Handling | 3/5 |
| UI/UX Design | 7/10 |
| Version Control (GitHub) | 2/5 |
| **TOTAL** | **69/100** |

**Estimated Grade: 69/100**

### Verification baseline

- All PHP files passed `php -l` syntax validation.
- `script.js` and `auth.js` passed `node --check`.
- No live database, browser, or production deployment test was performed, so runtime/database behavior cannot be fully verified from source alone.
- The worktree contains many pre-existing modifications and untracked files. The audit evaluates the current workspace state, not only `HEAD`.

## B. Executive Summary

### What the project does well

- It implements a complete event-production workflow: public marketing pages, package/custom builders, inquiry submission, client profiles/history, admin inquiry management, and task tracking.
- The primary `db.php` connection uses PDO exceptions, associative fetches, UTF-8, and native prepared statements.
- Passwords are handled with `password_hash()` and `password_verify()`, and successful logins regenerate the session ID.
- Most user-visible database content is escaped with `htmlspecialchars()`, and inquiry history/profile records are scoped to the logged-in user.
- The design has a coherent dark, gold, ivory visual system and responsive CSS breakpoints.

### Biggest problems and security risks

1. No CSRF tokens protect profile updates, inquiries, admin updates, task mutations, password changes, or admin creation.
2. SQL files seed a known default admin account and fixed password hash in source-controlled files.
3. `admin/dashboard.php` uses a weaker direct session-ID check instead of the role-validating shared guard.
4. `db_connect.php` duplicates the database connection, hard-codes local credentials, and exposes raw connection errors through `die()`.
5. Several pages use `.html` links even though the actual pages are `.php`; they depend on Apache rewrite rules and can fail outside that configuration.
6. The login registration switch is implemented in `auth.js`, but `login.php` does not load `auth.js`, so the account-mode toggle is broken in the normal page flow.
7. The admin search box is rendered but has no filtering implementation.
8. The project has no `.gitignore`, and Git history/current status show many modified files and untracked source files.

### Most likely grade reducers

CSRF absence, known admin credentials, broken/conditional navigation links, incomplete server-side date/length validation, duplicated page templates, and missing repository hygiene are the highest-impact rubric risks.

### Fix first

1. Add CSRF protection to every state-changing form and verify tokens server-side.
2. Remove seeded default credentials and rotate any existing admin password.
3. Consolidate database access on `db.php`; remove or secure unused `db_connect.php`.
4. Correct all `.html` links or guarantee and test rewrite behavior consistently.
5. Fix the login script loading and admin authorization guard.

## C. Complete File-by-File Audit

### `index.php`
**Purpose:** Public homepage, client session display, package overview, custom-service estimate, and navigation/footer.

**Rubric areas affected:** Functionality, PHP structure, UI/UX, navigation, forms.

**Problems found:**

1. Several links still target `custom.html` and `packages.html` even though the workspace contains PHP pages; behavior depends on `.htaccess` rewrites.
2. Header/footer are duplicated rather than consistently included.
3. The logged-in welcome panel exposes nickname/email in the hero, while other pages use different authentication markup.
4. The custom estimate is client-side only and uses a separate price set from `custom.php`; displayed totals can be inconsistent.
5. The page mixes PHP session logic, large HTML, and presentation in one 304-line file.

**Severity:** 🟠 High for broken links/inconsistent flows; 🟡 Medium for structure and pricing consistency.

**Suggested improvement:** Standardize PHP routes, centralize shared layout, and make the server-side pricing source authoritative. Do not trust the browser estimate for billing.

### `about.php`
**Purpose:** About/marketing page with navigation, company information, footer, and auth navigation include.

**Rubric areas affected:** Navigation, UI/UX, PHP structure.

**Problems found:**

1. Uses `index.html`, `about.html`, `packages.html`, `custom.html`, and `login.html` links although the corresponding project pages are PHP.
2. Duplicates the full header/footer structure while also including `includes/auth_nav.php`.
3. Footer/content markup is tightly mixed with page presentation.

**Severity:** 🟠 High for route reliability; 🟡 Medium for maintainability.

**Suggested improvement:** Use one canonical PHP route set and a shared layout component.

### `packages.php`
**Purpose:** Displays VIP package cards and booking/package links.

**Rubric areas affected:** Functionality, navigation, UI/UX.

**Problems found:**

1. Multiple navigation and package links target `.html` files.
2. Package buttons point to `custom.html`, so direct navigation can lose the selected package without JavaScript/rewrite support.
3. Footer/header are duplicated.

**Severity:** 🟠 High for package workflow reliability.

**Suggested improvement:** Use `booking.php?package=VIP1`, `VIP2`, and `VIP3` consistently and test with JavaScript disabled.

### `custom.php`
**Purpose:** Interactive custom-service builder that calculates a selected tier total and links to inquiry submission.

**Rubric areas affected:** Functionality, validation, JavaScript safety, UI/UX.

**Problems found:**

1. Client-side `innerHTML` is used for service summary output around line 95. Current values originate from rendered data, limiting exploitability, but DOM text APIs would be safer.
2. The builder sends JSON and total values through a URL; the server correctly recalculates accepted tier prices, but long URLs and user expectations are not addressed.
3. `href="#"` with `aria-disabled="true"` is still keyboard-activatable and requires JavaScript to prevent navigation.
4. The page has its own header markup and styling model, separate from shared pages.

**Severity:** 🟡 Medium.

**Suggested improvement:** Use `textContent`/DOM nodes for summaries, use a real disabled button state before selection, and share route/layout conventions.

### `contact.php`
**Purpose:** Inquiry form, client/session prefill, event/package selection, and form rendering.

**Rubric areas affected:** Forms, validation, functionality, security.

**Problems found:**

1. Form has no CSRF token.
2. It performs presentation-time prefill and validation setup in the same file as the page markup.
3. Client-side minimum date exists, but server-side `process_inquiry.php` does not reject past dates.
4. Text inputs have no explicit server-side length limits.
5. Form uses duplicated header/footer markup and multiple inconsistent page templates.

**Severity:** 🔴 Critical for CSRF; 🟠 High for server-side validation gaps; 🟡 Medium for structure.

**Suggested improvement:** Add CSRF, enforce date/length/field constraints in the handler, and centralize rendering.

### `booking.php`
**Purpose:** Requires client login before redirecting to the inquiry flow with an optional package.

**Rubric areas affected:** Authentication, navigation, functionality.

**Problems found:**

1. Redirect behavior depends on `booking_guard.php` and request parsing; it should be tested for query preservation and malicious input.
2. It is a small forwarding endpoint with no explicit CSRF relevance because it is GET-only.

**Severity:** 🟢 Low, pending runtime testing.

**Suggested improvement:** Add automated route tests for logged-in/logged-out and malformed package cases.

### `booking_guard.php`
**Purpose:** Builds login redirect URLs and protects booking routes.

**Rubric areas affected:** Authentication, navigation.

**Problems found:**

1. It allowlists destination basenames, which is good, but preserves query strings without field-level validation.
2. It has no reusable session/cookie security configuration.

**Severity:** 🟡 Medium.

**Suggested improvement:** Validate expected query keys and centralize secure session setup.

### `login.php`
**Purpose:** Client registration/login and unified admin/client authentication.

**Rubric areas affected:** Authentication, database, forms, functionality.

**Problems found:**

1. No CSRF token protects registration/login POSTs.
2. Registration has minimal validation and no length limits, email verification, rate limiting, or account abuse controls.
3. The registration toggle logic is in `auth.js`, but this file does not visibly load `auth.js`; the toggle can fail in the normal page flow.
4. Admin and client login logic are both embedded in a large page controller.
5. Session cookie flags (`Secure`, `HttpOnly`, `SameSite`) are not explicitly configured.

**Severity:** 🔴 Critical for missing CSRF; 🟠 High for auth hardening and broken registration UX.

**Suggested improvement:** Add CSRF/rate limiting, load the correct script, centralize authentication services, and configure secure cookies.

### `logout.php`
**Purpose:** Clears the client session and redirects home.

**Rubric areas affected:** Authentication/session.

**Problems found:**

1. Logout is a GET endpoint, so another site can trigger logout requests; this is lower impact than state-changing account actions but still undesirable.
2. Session cookie deletion uses existing parameters without explicitly establishing secure cookie policy.

**Severity:** 🟡 Medium.

**Suggested improvement:** Use a POST logout with CSRF protection where practical and centralize session teardown.

### `profile.php`
**Purpose:** Protected client profile, profile update form, inquiry history, and itemized receipts.

**Rubric areas affected:** Authentication, forms, database, PHP structure, UI/UX.

**Problems found:**

1. No CSRF token protects profile updates.
2. Profile update accepts trimmed values but has no length/format constraints for phone, name, or location.
3. It is a large mixed controller/template with inline SQL, mutation logic, rendering, and duplicated header/footer.
4. The page has no explicit `account-settings` anchor despite navigation links pointing to `profile.php#account-settings` elsewhere.

**Severity:** 🔴 Critical for CSRF; 🟠 High for validation/structure.

**Suggested improvement:** Add CSRF and field constraints, move profile/inquiry services into reusable functions, and add the intended anchor or correct the link.

### `process_inquiry.php`
**Purpose:** Validates and inserts public/client event inquiries.

**Rubric areas affected:** Database, forms, validation, error handling, functionality.

**Problems found:**

1. No CSRF protection.
2. Past dates are accepted: the code validates `Y-m-d` format but not `>= today`, despite the HTML `min` attribute.
3. No explicit maximum lengths for free-text values before database insertion.
4. Reference numbers use a four-digit random suffix and retry loop; collision handling exists but scalability is limited.
5. Placeholder notification functions do nothing, so expected email/receipt functionality is not implemented.

**Severity:** 🔴 Critical for CSRF; 🟠 High for date/length validation and missing notifications.

**Suggested improvement:** Add server-side business-rule validation, CSRF, length limits, and implement or clearly remove placeholder notification behavior.

### `inquiry_thank_you.php`
**Purpose:** Displays an inquiry confirmation based on a reference number and ownership/session checks.

**Rubric areas affected:** Functionality, authorization, error handling.

**Problems found:**

1. It correctly restricts authenticated lookups by `user_id` and anonymous lookups by the last session reference, which is a strength.
2. Invalid/missing references render a 404-style page, but the page still contains generic placeholder data and a full shared layout; this may be confusing to users.
3. It depends on shared header/footer includes while some pages are standalone.

**Severity:** 🟡 Medium.

**Suggested improvement:** Use a dedicated not-found response/view and standardize layout dependencies.

### `auth_status.php`
**Purpose:** Returns client login state and first name as JSON for JavaScript navigation.

**Rubric areas affected:** Authentication, frontend behavior, error handling.

**Problems found:**

1. No `Cache-Control: no-store` header for session-dependent JSON.
2. It exposes limited identity data; this is not highly sensitive, but it should be deliberately scoped.
3. No explicit handling for JSON encoding failure beyond the exception.

**Severity:** 🟡 Medium.

**Suggested improvement:** Add no-store headers and keep the response contract minimal.

### `terms.php`
**Purpose:** Legacy standalone terms page.

**Rubric areas affected:** Content consistency, navigation, maintainability.

**Problems found:**

1. It duplicates policy content separately from `business_info.php`, creating a risk of legal text drift.
2. It depends on shared header/footer includes while `business_info.php` is standalone.

**Severity:** 🟡 Medium.

**Suggested improvement:** Choose one canonical policy source and link to it consistently.

### `privacy.php`
**Purpose:** Legacy standalone privacy page.

**Rubric areas affected:** Content consistency, maintainability.

**Problems found:**

1. Duplicates privacy content separately from `business_info.php`.
2. Depends on shared includes, unlike the standalone policy page.

**Severity:** 🟡 Medium.

**Suggested improvement:** Consolidate the legal content or generate both views from one source.

### `business_info.php`
**Purpose:** Standalone combined policies and information page with session-aware navigation, tabs, legal content, and footer.

**Rubric areas affected:** UI/UX, accessibility, PHP structure, content consistency.

**Problems found:**

1. It duplicates the global header/footer and embeds a large CSS/JavaScript block inside the page.
2. It duplicates legal content already present in `terms.php` and `privacy.php`.
3. It loads `script.js` and its own tab script, increasing page-level behavior coupling.
4. The tab interface is reasonably structured with `aria-controls`/`aria-selected`, but keyboard/focus state management is minimal.

**Severity:** 🟡 Medium.

**Suggested improvement:** Keep one canonical legal content source, move page CSS/JS to reusable assets, and use a consistent layout strategy.

### `about.php`, `packages.php`, `custom.php`, `contact.php`, `login.php`, `profile.php`
**Shared template finding:** These pages duplicate header/footer markup rather than consistently using reusable layout includes. This creates route, auth-navigation, logo, footer, and accessibility drift. It is a maintainability and UI consistency issue rather than automatically a functional bug.

### `db.php`
**Purpose:** Primary PDO database factory.

**Rubric areas affected:** Database, security, error handling.

**Strengths:** Environment-variable support, UTF-8 DSN, exceptions, associative fetch mode, and native prepared statements are appropriate.

**Problems found:** Empty default root credentials are unsafe outside local development if deployment variables are missing; connection failures throw exceptions that callers must handle.

**Severity:** 🟠 High for deployment misconfiguration risk; 🟢 Low for local development.

**Suggested improvement:** Require production credentials, fail safely, and document required environment variables.

### `db_connect.php`
**Purpose:** Second, unused MySQLi database connection.

**Problems found:** Hard-coded credentials, duplicate connection architecture, and `die("Connection failed: ...")` exposes raw database errors. It appears unused by current application code.

**Severity:** 🔴 Critical if reachable/used; 🟠 High as deployment/security debt.

**Suggested improvement:** Remove after confirming no consumers, or consolidate into the PDO factory and return user-safe errors.

### `admin/login.php`
**Purpose:** Separate admin login form and authentication.

**Problems found:** No CSRF token or login rate limiting. Authentication is compressed into one-line nested branches, making review/maintenance difficult. It correctly uses prepared SQL and `password_verify()`.

**Severity:** 🔴 Critical for CSRF; 🟡 Medium for structure.

**Suggested improvement:** Add CSRF/rate limiting and expand the controller into readable branches or a shared auth service.

### `admin/logout.php`
**Purpose:** Clears admin session and redirects to client home.

**Strengths:** Calls `session_start()`, `session_unset()`, `session_destroy()`, and redirects with `exit()`.

**Problems found:** Logout is GET-triggerable and does not explicitly expire the session cookie in this file.

**Severity:** 🟡 Medium.

**Suggested improvement:** Use centralized secure session teardown and consider POST + CSRF.

### `admin/dashboard.php`
**Purpose:** Protected admin KPI dashboard, inquiry update forms, and task tracker.

**Problems found:**

1. Uses only `isset($_SESSION['admin_user']['id'])` rather than `includes/admin_guard.php`, so role validity is not checked consistently.
2. All state-changing forms lack CSRF tokens.
3. Search input is present but has no filtering behavior.
4. Static dashboard reads use `query()` safely, but large SQL/UI/controller responsibilities are combined.
5. Long expressions and inline loops reduce readability.

**Severity:** 🔴 Critical for CSRF; 🟠 High for guard inconsistency and missing search.

**Suggested improvement:** Use the shared guard, add CSRF, implement search or remove the control, and split query/controller/view concerns.

### `admin/settings.php`
**Purpose:** Admin password change, super-admin team creation, and team list.

**Problems found:** No CSRF protection on password/team forms. Team creation does not explicitly validate active admin IDs or enforce length limits. Dense one-line logic reduces reviewability. The role check for team creation is present and is a positive authorization control.

**Severity:** 🔴 Critical for CSRF; 🟠 High for admin-account mutation.

**Suggested improvement:** Add CSRF, stronger field constraints, audit logging, and readable service functions.

### `admin/add_task.php`
**Purpose:** Admin task create/toggle/delete endpoint.

**Problems found:** No CSRF protection. Assignment and inquiry/task IDs are type-checked but existence/active-status validation is mostly delegated to database constraints. All actions share one endpoint and redirect path.

**Severity:** 🔴 Critical for CSRF; 🟡 Medium for validation.

**Suggested improvement:** Add CSRF, explicit authorization/ownership checks, and separate action handlers or a validated action map.

### `admin/update_inquiry.php`
**Purpose:** Admin updates inquiry price/status.

**Problems found:** No CSRF. It validates type/range/status but does not verify that the target inquiry exists before reporting success. It relies on the admin session guard and prepared SQL.

**Severity:** 🔴 Critical for CSRF; 🟡 Medium for missing-record handling.

**Suggested improvement:** Check affected rows and return a clear failure message for missing IDs.

### `includes/header.php`
**Purpose:** Shared client header, navigation, session-aware profile dropdown, and document head.

**Problems found:** Shared and standalone pages use different versions, causing navigation drift. The mobile menu button lacks `aria-expanded` and `aria-controls`. Navigation includes an account-settings fragment that is not present in `profile.php`.

**Severity:** 🟡 Medium.

**Suggested improvement:** Make this the canonical header and update all pages to use it consistently; improve menu accessibility.

### `includes/footer.php`
**Purpose:** Shared footer with policy/business links.

**Problems found:** It is not used by every public page; several pages duplicate their own footer. This permits inconsistent business information and policy links.

**Severity:** 🟡 Medium.

**Suggested improvement:** Use one shared footer or one canonical component across all public pages.

### `includes/auth_nav.php`
**Purpose:** Reusable server-rendered client auth links.

**Problems found:** It duplicates auth navigation logic already embedded in several pages and does not provide the newer profile-dropdown behavior used by `includes/header.php`.

**Severity:** 🟡 Medium.

**Suggested improvement:** Choose one auth navigation implementation.

### `includes/admin_guard.php`
**Purpose:** Validates admin session ID and allowed role.

**Strengths:** Centralizes role allowlisting for most protected admin endpoints.

**Problems found:** `admin/dashboard.php` does not use it, so the application has inconsistent authorization enforcement.

**Severity:** 🟠 High.

**Suggested improvement:** Require this guard in every admin-protected page/action.

### `style.css`
**Purpose:** Global layout, branding, forms, dashboard, responsive rules, and legacy styles.

**Problems found:**

1. At 1,612 lines, it contains many page-specific and legacy rules.
2. There are duplicate/conflicting layout rules around admin/task/settings areas.
3. Shared/standalone markup variants require selector exceptions and increase CSS drift.
4. Muted gray text on dark backgrounds should be checked against WCAG contrast rather than assumed accessible.

**Severity:** 🟡 Medium.

**Suggested improvement:** Organize by component/page, remove only confirmed dead rules, and run contrast/responsive checks.

### `script.js`
**Purpose:** Auth navigation refresh, mobile menu, package-link behavior, and homepage estimate.

**Problems found:**

1. It uses `innerHTML` for auth dropdown markup and custom summary markup.
2. It derives package identity from visible button text instead of data attributes.
3. Auth refresh may overwrite server-rendered navigation and depends on relative endpoint paths.
4. No explicit error UI exists if the auth status request fails.

**Severity:** 🟡 Medium.

**Suggested improvement:** Build DOM nodes/text safely, use stable data attributes, and keep server/client navigation contracts aligned.

### `auth.js`
**Purpose:** Login/register mode toggle.

**Problems found:** It assumes `switchAuth`, `confirmField`, and other elements exist. On any page where the script is loaded without the login form, null dereferences are possible; on `login.php`, the current markup does not visibly load this script, so the intended toggle may not run.

**Severity:** 🟠 High for login UX.

**Suggested improvement:** Guard event binding with `if (switchAuth && authForm)` and load the script explicitly on the login page.

### `database.sql`
**Purpose:** Base schema, foreign keys, and initial admin seed.

**Problems found:** Seeds `admin@legatoevents.com` with a fixed password hash in source-controlled SQL. Defines `bookings`, but application code uses `inquiries` instead. It also adds an admin foreign key after `admin_users` exists, coupling setup order.

**Severity:** 🔴 Critical for default credentials; 🟡 Medium for schema drift.

**Suggested improvement:** Remove fixed production credentials, use a deployment-time bootstrap process, and reconcile unused/active tables.

### `database_migration_profile.sql`
**Purpose:** Adds profile columns to `users`.

**Problems found:** `ALTER TABLE ADD COLUMN` is not idempotent, so rerunning it fails. It assumes a specific prior schema.

**Severity:** 🟡 Medium.

**Suggested improvement:** Use versioned migrations or guarded/idempotent migration tooling.

### `database_migration_inquiries.sql`
**Purpose:** Adds inquiry columns.

**Problems found:** Non-idempotent column additions and ordering assumptions. Some columns differ from the base schema defaults/nullability.

**Severity:** 🟡 Medium.

**Suggested improvement:** Consolidate schema/migrations and test from a clean database.

### `database_migration_dashboard.sql`
**Purpose:** Creates `booking_services`.

**Problems found:** The table is not used by current application code, suggesting schema drift or incomplete functionality.

**Severity:** 🟡 Medium.

**Suggested improvement:** Either implement the workflow or document/remove the unused schema after confirmation.

### `database_migration_system.sql`
**Purpose:** Adds dashboard fields/tables and seeds admin account.

**Problems found:** It repeats the fixed admin credential seed, relies on `ADD COLUMN IF NOT EXISTS` support, and mixes migration/schema creation with seed data.

**Severity:** 🔴 Critical for known credentials; 🟡 Medium for migration portability.

**Suggested improvement:** Separate schema migrations from secure bootstrap data and document supported MySQL versions.

### `.htaccess`
**Purpose:** Rewrites legacy `.html` URLs to PHP files.

**Problems found:** It only covers a subset of links and requires Apache rewrite support. Many project links still depend on these rules without a visible fallback.

**Severity:** 🟠 High for navigation reliability outside the expected Apache configuration.

**Suggested improvement:** Use canonical PHP links in source and keep rewrite rules only for backward compatibility.

### `RUBRIC_AUDIT.md`
**Purpose:** This audit report.

**Status:** Created by this audit. It is the only file added in this audit.

### `Assest/legato1.png`, `Assest/white 2.png`, `assets/white 1.png`
**Purpose:** Branding image assets.

**Problems found:** `Assest/legato1.png` is modified in the worktree. Binary changes cannot be meaningfully reviewed as source here. Naming/casing is inconsistent (`Assest` and `assets`), which can fail on case-sensitive deployments.

**Severity:** 🟡 Medium.

**Suggested improvement:** Normalize asset directory casing and verify image dimensions/optimization. Confirm the modified binary is intentional.

## D. PHP Structure Audit

### Actual rubric problems

- Multiple pages combine session setup, database queries, mutation handlers, validation, and large HTML templates in one file.
- Header/footer/auth markup is duplicated across `index.php`, `about.php`, `packages.php`, `contact.php`, `login.php`, `profile.php`, and policy pages.
- Admin logic is inconsistent: `admin/dashboard.php` has a direct guard while other endpoints use `includes/admin_guard.php`.
- `admin/login.php` and `admin/settings.php` contain dense one-line nested branches that are difficult to review for security.
- Legal content is duplicated across `business_info.php`, `terms.php`, and `privacy.php`.
- The second database connector (`db_connect.php`) creates an unnecessary architecture split.

### Optional improvements

- Extract validation/constants into focused functions or service classes.
- Use a single layout/header/footer strategy after confirming relative-path requirements.
- Add a small routing/link helper to avoid `.html`/`.php` drift.
- Format compressed controller branches for maintainability without changing behavior.

## E. Database Security Audit

| File / location | Operation/query | Prepared statement? | Injection risk | Severity |
|---|---|---:|---|---|
| `db.php:10` | PDO connection factory | N/A | None observed | 🟢 Low |
| `db_connect.php:5-8` | MySQLi connection with fixed credentials | N/A | No SQL injection, but raw error/duplicate connector | 🟠 High |
| `login.php:38` | Insert user | Yes | Low | 🟢 Low |
| `login.php:49` | Select admin by email | Yes | Low | 🟢 Low |
| `login.php:61` | Select user by email | Yes | Low | 🟢 Low |
| `login.php:70` | Select user profile by ID | Yes | Low | 🟢 Low |
| `process_inquiry.php:72` | Check reference number | Yes | Low | 🟢 Low |
| `process_inquiry.php:80` | Insert inquiry | Yes | Low | 🟢 Low |
| `profile.php:16` | Update current user profile | Yes | Low | 🟢 Low |
| `profile.php:18` | Select current account | Yes | Low | 🟢 Low |
| `profile.php:22` | Select current user's inquiries | Yes | Low | 🟢 Low |
| `inquiry_thank_you.php:12,15` | Select inquiry by reference and owner/session | Yes | Low | 🟢 Low |
| `admin/login.php:11` | Select active admin by email | Yes | Low | 🟢 Low |
| `admin/dashboard.php:13-16` | Static KPI/inquiry/admin/task SELECTs | No (`query()`) | None because SQL is static | 🟢 Low |
| `admin/settings.php:15` | Select admin password hash | Yes | Low | 🟢 Low |
| `admin/settings.php:17` | Update admin password | Yes | Low | 🟢 Low |
| `admin/settings.php:22` | Insert admin user | Yes | Low | 🟢 Low |
| `admin/settings.php:25` | Static team SELECT | No (`query()`) | None because SQL is static | 🟢 Low |
| `admin/add_task.php:17` | Insert task | Yes | Low | 🟢 Low |
| `admin/add_task.php:25` | Update task status | Yes | Low | 🟢 Low |
| `admin/add_task.php:31` | Delete task | Yes | Low | 🟢 Low |
| `admin/update_inquiry.php:17` | Update inquiry amount/status | Yes | Low | 🟢 Low |

**Database conclusion:** No application query was found that directly interpolates user input into SQL. The major database risks are deployment credentials, default seeded admin credentials, duplicate connectors, schema drift, and missing affected-row/existence checks rather than SQL injection.

## F. Input/Form Security Audit

| File | Input source | Validation/sanitization observed | Risk |
|---|---|---|---|
| `login.php:5,19-32` | `redirect`, registration/login POST fields | Redirect basename allowlist; email/password checks; trim; no CSRF, rate limiting, length limits | 🟠 High |
| `admin/login.php:7-11` | Email/password POST | Email validation and password verification; no CSRF/rate limiting | 🟠 High |
| `contact.php:13-46` | Inquiry form POST/GET | Prefill/trim/filtering; server handler performs allowlists; no CSRF | 🔴 Critical |
| `process_inquiry.php:10-65` | Inquiry POST | Email, integer, enum/allowlist, date/time shape checks; does not reject past date or limit text lengths; no CSRF | 🔴 Critical |
| `profile.php:10-16` | Profile update POST | Empty-field check; no CSRF, format/length constraints | 🔴 Critical |
| `admin/settings.php:11-22` | Password/team POST | Password minimum, email, role allowlist; no CSRF or length limits | 🔴 Critical |
| `admin/add_task.php:8-33` | Task POST | Integer validation, nonempty title; no CSRF, assignment/date existence validation | 🔴 Critical |
| `admin/update_inquiry.php:7-18` | Amount/status POST | Numeric/range/status allowlist; no CSRF; no target existence check | 🔴 Critical |
| `booking.php:6` | Package GET | Trim and encoded destination construction | 🟢 Low |
| `inquiry_thank_you.php:5` | Reference GET | Trim; prepared ownership query; 404 fallback | 🟢 Low |
| `auth_status.php` | Session state | Server-derived values; no-store header absent | 🟡 Medium |
| `custom.php` JavaScript | Fixed rendered service/tier values | Server later recalculates accepted prices | 🟡 Medium |

Output escaping is generally good in page templates through `htmlspecialchars()` and `nl2br(escaped(...))`. The missing CSRF layer is the dominant form-security gap.

## G. Authentication & Session Audit

### Positive controls

- Client and admin passwords use `password_hash()` and `password_verify()`.
- Successful client/admin logins call `session_regenerate_id(true)`.
- Protected client profile checks `$_SESSION['user']['id']`.
- Admin mutation endpoints generally require admin session state.
- `includes/admin_guard.php` allowlists `super_admin`, `coordinator`, and `staff` roles.
- Client logout clears `$_SESSION`, expires the session cookie when cookies are enabled, destroys the session, and redirects home.
- Admin logout calls `session_unset()`, `session_destroy()`, and redirects to `../index.php`.

### Problems

1. `admin/dashboard.php` does not use the shared role-validating guard; it checks only an admin ID.
2. Session cookie security flags are not explicitly configured (`Secure`, `HttpOnly`, `SameSite`).
3. No CSRF protection exists for authentication or all other state-changing forms.
4. No login/register/admin rate limiting or lockout is present.
5. SQL seeds a known default admin hash and email in `database.sql` and `database_migration_system.sql`.
6. Client/admin session switching is implemented, but the overall auth implementation is duplicated across pages.
7. `auth_status.php` exposes session state without cache-control headers.

## H. Error Handling Audit

### Positive behavior

- `db.php` enables PDO exceptions.
- Login, inquiry insertion, admin login/team creation, and inquiry lookup have user-facing fallback messages.
- Inquiry confirmation uses a 404 status for unavailable records.
- Invalid method/action paths redirect instead of rendering raw errors.

### Problems

- `db_connect.php` uses `die()` with raw connection error text and is the clearest production error leak.
- Several database operations outside explicit `try/catch` blocks can surface framework/PHP errors if the database is unavailable, especially profile/admin dashboard/settings reads.
- There is no centralized error logging strategy visible in the project.
- Placeholder notification functions silently do nothing after inquiry creation.
- No production `display_errors`/logging policy is visible.

## I. UI/UX Audit

### Strengths

- Consistent dark/gold visual language and Google font usage.
- Responsive CSS breakpoints exist and viewport metadata is present.
- Forms generally have labels, input types, required attributes, and user-facing messages.
- Admin dashboard has KPI cards, inquiry cards, task tracking, and status styling.
- Policy tabs use active states and ARIA relationships.

### Problems

1. `.html`/`.php` link inconsistency creates broken navigation risk.
2. Main admin search control does not work.
3. Mobile menu button lacks `aria-expanded` and `aria-controls`.
4. Dark muted text may not meet WCAG contrast; this should be measured, not assumed.
5. Duplicated headers/footers create inconsistent auth and navigation behavior.
6. `aria-disabled` on an anchor in `custom.php` does not make it truly disabled.
7. Some forms and controls are dense, and admin actions lack confirmation feedback beyond a redirect message.
8. CSS contains conflicting/duplicated responsive layout rules.
9. Script behavior can overwrite server-rendered nav and uses `innerHTML` for generated UI.

## J. GitHub/Version Control Audit

### Verified

- A Git repository exists.
- Current branch is `master`.
- Recent visible commits:
  - `3b2b617` — `with admin side plus tailwind integ`
  - `512a876` — `php integV3.1`
  - `97e918f` — `2ndVersion with sections`
  - `f61fa3e` — `LegatoV1`
- The repository has 36 tracked files according to `git ls-files`.
- The worktree has many modified files and several untracked PHP/include files.

### Problems

- No `.gitignore` exists.
- Database SQL files contain a seeded admin credential and should be reviewed before publication.
- Local/database configuration files may be tracked or available without an ignore policy.
- The worktree is broadly modified, making it difficult to identify coherent submission changes.
- Commit quality is only partially verifiable from the short log; the messages are brief and do not clearly document feature scope, testing, or security changes.

**Required statement:** Git commit quality could not be fully verified from the available workspace history and metadata alone.

## K. Priority Fix List

### 🔴 MUST FIX BEFORE SUBMISSION

1. Add CSRF tokens to login/registration, inquiry, profile, admin task, inquiry update, password, and team-creation forms.
2. Remove/rotate the seeded default admin credential and use a secure deployment bootstrap process.
3. Replace or remove `db_connect.php`; never expose raw connection errors with `die()`.
4. Make every admin-protected endpoint use the same role-validating guard, including `admin/dashboard.php`.
5. Fix all canonical links from `.html` to actual PHP routes, or test every rewrite path under Apache.
6. Ensure `auth.js` is loaded and null-safe so registration mode works.
7. Enforce future event dates and maximum lengths server-side in `process_inquiry.php`.

### 🟠 SHOULD FIX

1. Add session cookie security settings and `Cache-Control: no-store` to auth JSON.
2. Implement admin search/filtering or remove the unused search field.
3. Add missing-record checks and affected-row handling to admin updates.
4. Consolidate duplicated headers, footers, and auth navigation.
5. Add a `.gitignore` for environment files, IDE files, logs, generated artifacts, and local config.
6. Reconcile `bookings`/`booking_services` schema with the active `inquiries` workflow.
7. Add login/inquiry rate limiting and abuse controls.

### 🟡 NICE TO FIX

1. Replace JavaScript `innerHTML` with DOM/text APIs.
2. Add mobile-menu ARIA state attributes.
3. Improve contrast after WCAG measurement.
4. Improve error logging and user-safe database failure handling.
5. Replace four-digit reference numbers with a stronger identifier strategy.

### 🟢 OPTIONAL

1. Organize CSS into component/page sections or separate files.
2. Add automated route/form smoke tests.
3. Improve commit messages and use focused feature/security commits.
4. Normalize asset directory casing and image naming.

## L. Final Rubric Justification

### Functionality & Requirements: 13/20

The core client inquiry/profile and admin dashboard workflows are implemented, and most navigation/form paths exist. Points are lost for `.html` route dependencies, the nonfunctional admin search, the registration toggle script-loading issue, placeholder notifications, and inconsistent page behavior across templates. A clean browser test with Apache, MySQL, and JavaScript enabled is still required.

**What would raise this to 18–20:** Fix canonical routes, registration toggle, admin search, notification behavior, and add workflow tests for logged-in/logged-out paths.

### PHP Code Quality & Structure: 13/20

The project has a reusable PDO factory and some guards/helpers, but many files mix controller logic, SQL, session handling, and full HTML. Header/footer/auth markup is duplicated, legal content is duplicated, and admin logic contains compressed one-line branches. These are concrete maintainability issues, not merely stylistic preferences.

**What would raise this to 18–20:** Consolidate shared layout/auth/database services, split large controllers from views, remove the duplicate connector, and make admin guards consistent.

### Database Integration: 17/20

Most application queries use prepared statements and user-owned data is scoped correctly. Static `query()` calls are not injection risks because their SQL is not user-controlled. Points are lost for duplicate/hard-coded connection code, seeded credentials, schema drift, non-idempotent migrations, and incomplete missing-record checks.

**What would raise this to 18–20:** Remove the duplicate connector, secure bootstrap credentials, reconcile schemas, version migrations, and verify affected rows/existence.

### Form Handling & Validation: 7/10

There is meaningful server-side validation for email, enums, times, integers, packages, services, and status. Output escaping is generally strong. Points are lost for no CSRF, no rate limits, no length constraints, accepting past dates, and relying on HTML `required` for part of the UX.

**What would raise this to 9–10:** Add CSRF, enforce length/date/range constraints centrally, add abuse controls, and test malformed direct POST requests.

### Session/Authentication: 7/10

Password hashing, verification, session regeneration, protected pages, and logout behavior are present. Points are lost for missing cookie flags, missing CSRF, default admin credentials, duplicated auth implementations, and the dashboard’s weaker guard.

**What would raise this to 9–10:** Secure cookie configuration, uniform guards, credential bootstrap rotation, CSRF, rate limiting, and automated authorization tests.

### Error Handling: 3/5

Several flows show friendly errors and use PDO exceptions. Points are lost for raw `die()` output in `db_connect.php`, lack of centralized logging, unhandled reads in some page controllers, and silent placeholder notification functions.

**What would raise this to 4–5:** Remove raw error exposure, add production-safe logging, catch expected database failures consistently, and report notification failures appropriately.

### UI/UX Design: 7/10

The site has a coherent visual system, responsive metadata/breakpoints, labeled forms, admin dashboard structure, and policy tabs. Points are lost for broken route variants, nonfunctional search, inconsistent duplicated layouts, incomplete mobile-menu accessibility, possible contrast failures, and disabled-anchor behavior.

**What would raise this to 9–10:** Normalize shared components/routes, implement missing controls, verify WCAG contrast, improve mobile semantics, and test all major workflows at mobile/desktop sizes.

### Version Control (GitHub): 2/5

A repository, branch, remote history, and multiple commits exist. Points are lost because there is no `.gitignore`, the current worktree has broad uncommitted changes/untracked files, seeded credentials are present in SQL, and commit quality cannot be fully verified from the available history.

**What would raise this to 4–5:** Add a robust `.gitignore`, remove secrets/default credentials, organize coherent commits, review tracked files, and document test evidence in commits or project documentation.
