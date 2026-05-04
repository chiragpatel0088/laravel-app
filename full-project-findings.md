# Full Project Security and Quality Findings

Date: 2026-05-01
Scope: Entire repository audit (application security, authorization, validation, session management, file handling, and runtime safety)

---

## Executive Summary

This audit identified multiple high-impact weaknesses across the project, including SQL injection exposure patterns, missing authorization checks, missing CSRF protections, hardcoded credentials, and output encoding gaps that can enable XSS.

The highest-priority work should focus on:
1. Removing SQL string interpolation and enforcing parameterized queries everywhere.
2. Adding authentication and per-resource authorization checks on all data retrieval and mutation endpoints.
3. Implementing CSRF protection on all state-changing requests.
4. Rotating and externalizing hardcoded secrets immediately.

---

## Critical Findings

### 1) SQL Injection Risk Across Core Data Access Layer
- **Severity:** Critical
- **Affected Files:**
  - `inc/backend/database.php`
  - `process.php`
- **Issue:**
  - Dynamic SQL construction patterns are used with user-influenced inputs.
  - Some code paths rely on quoted/interpolated values instead of proper bound parameters.
- **Impact:**
  - Potential data exfiltration, unauthorized modification, or destructive DB operations.
- **Recommendation:**
  - Replace all dynamic SQL interpolation with true parameterized queries (`?` / named placeholders + bind).
  - Enforce strict typing/casting for all IDs before DB layer use.
  - Add query helpers that reject unsafe raw interpolation patterns.
- **Confidence:** High

### 2) Missing Auth/Authz on Data Retrieval Endpoints (Direct Data Exposure)
- **Severity:** Critical
- **Affected Files (examples):**
  - `inc/backend/data_retrieval/getAllNotificationsForUser.php`
  - `inc/backend/data_retrieval/getCustomerDetails.php`
  - `inc/backend/data_retrieval/getOperatorDetails.php`
  - `inc/backend/data_retrieval/getSiteVisitDetails.php`
- **Issue:**
  - Endpoints accept IDs from request input without robust session and ownership/role enforcement.
- **Impact:**
  - Unauthorized access to user, customer, operator, and operational data.
- **Recommendation:**
  - Enforce authenticated session middleware for every endpoint.
  - Derive principal identity from session, not client-provided user IDs.
  - Add per-resource authorization checks (role + ownership).
- **Confidence:** High

### 3) Hardcoded Credentials/Secrets in Codebase
- **Severity:** Critical
- **Affected Files:**
  - `inc/backend/db_cred_constants.php`
- **Issue:**
  - Database and related credentials are stored in plaintext constants in repository code.
- **Impact:**
  - Credential leakage can lead to direct infrastructure compromise.
- **Recommendation:**
  - Move secrets to environment variables or a secret manager.
  - Rotate exposed credentials immediately.
  - Prevent secret files from being committed (git ignore + secret scanning).
- **Confidence:** High

### 4) CSRF Missing on State-Changing Actions
- **Severity:** Critical
- **Affected Files (examples):**
  - `process.php`
  - `job.php`
  - `quote.php`
  - `customer_quote.php`
- **Issue:**
  - State-changing POST actions lack anti-CSRF token validation.
- **Impact:**
  - Attackers can trigger actions as logged-in users through forged requests.
- **Recommendation:**
  - Implement synchronizer-token CSRF protection for all mutating actions.
  - Validate token server-side per request and per session.
  - Add Origin/Referer validation as defense-in-depth.
- **Confidence:** High

---

## High Findings

### 5) Central Request Router Allows Privileged Action Invocation Without Explicit Authorization Gates
- **Severity:** High
- **Affected Files:**
  - `process.php`
- **Issue:**
  - Many `proc*` handlers are selected by POST key presence; role/permission validation is inconsistent or missing.
- **Impact:**
  - Lower-privileged authenticated users may trigger privileged operations.
- **Recommendation:**
  - Add explicit permission checks at the start of every mutating handler.
  - Enforce deny-by-default authorization policy.
- **Confidence:** High

### 6) IDOR Risk on Job Views/Actions
- **Severity:** High
- **Affected Files:**
  - `operator_job.php`
  - `linesman_job.php`
  - `process.php`
- **Issue:**
  - Request-driven identifiers can reference resources not owned/assigned to the current user.
- **Impact:**
  - Unauthorized viewing or modification of other jobs.
- **Recommendation:**
  - Verify assignment/ownership before any read or write.
  - Return 403 for unauthorized resource access attempts.
- **Confidence:** High

### 7) XSS Risk Due to Inconsistent Output Encoding
- **Severity:** High
- **Affected Files (examples):**
  - `job.php`
  - `quote.php`
  - `operator_job.php`
  - `linesman_job.php`
  - `customer_quote.php`
- **Issue:**
  - Database-backed values are output into HTML/attribute contexts without consistent escaping.
- **Impact:**
  - Stored/reflected XSS against internal users and customers.
- **Recommendation:**
  - Apply context-aware output encoding everywhere (`htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` for HTML/attrs).
  - For rich content, sanitize with strict allowlists before rendering.
- **Confidence:** High

### 8) Unsafe File Upload/Delete Paths and Handling
- **Severity:** High
- **Affected Files:**
  - `process.php` (site inspection upload handling)
  - `inc/backend/data_retrieval/select2/select2_deleteCanceledJob.php`
  - `inc/backend/data_retrieval/select2/select2_getPhotoUrl.php`
- **Issue:**
  - Upload naming/validation and deletion path checks are not hardened sufficiently.
- **Impact:**
  - File overwrite/deletion abuse and sensitive path exposure risks.
- **Recommendation:**
  - Generate random server-side file names.
  - Validate MIME type + content signatures, not extension only.
  - Restrict deletion to canonicalized, allowlisted directories.
  - Remove debug output and enforce authz on file operations.
- **Confidence:** Medium-High

---

## Medium Findings

### 9) Weak Password and Session Security Primitives
- **Severity:** Medium
- **Affected Files:**
  - `inc/backend/database.php`
  - `inc/backend/session.php`
  - `process.php`
- **Issue:**
  - Legacy cryptographic/session patterns are used instead of modern password/session APIs.
- **Impact:**
  - Reduced resistance to brute force, fixation, and token prediction risks.
- **Recommendation:**
  - Migrate to `password_hash()` / `password_verify()` (Argon2id or bcrypt).
  - Regenerate session IDs on login and privilege changes.
  - Use CSPRNG token generation for security-sensitive values.
- **Confidence:** High

### 10) Session Cookies Missing Secure Attributes
- **Severity:** Medium
- **Affected Files:**
  - `inc/backend/session.php`
- **Issue:**
  - Cookies are set without robust modern flags.
- **Impact:**
  - Elevated session theft and CSRF-related exposure.
- **Recommendation:**
  - Set `HttpOnly`, `Secure` (HTTPS-only), and `SameSite=Lax` or stricter.
- **Confidence:** High

### 11) Deprecated/Over-Broad Input Sanitization Pattern
- **Severity:** Medium
- **Affected Files:**
  - `process.php`
- **Issue:**
  - Use of deprecated broad sanitization and manual escaping (`addslashes`) introduces false confidence and data handling bugs.
- **Impact:**
  - Validation bypass and data corruption edge cases.
- **Recommendation:**
  - Replace with field-level validation by schema/type and strict server-side constraints.
  - Use proper prepared statements and output encoding instead of generic sanitization.
- **Confidence:** High

### 12) Debug and Internal Error Leakage
- **Severity:** Medium
- **Affected Files (examples):**
  - `process.php`
  - `inc/backend/data_retrieval/getAllNotificationsForUser.php`
  - `inc/backend/data_retrieval/select2/select2_deleteCanceledJob.php`
- **Issue:**
  - Debug strings and internal details are emitted in runtime paths.
- **Impact:**
  - Information disclosure assists attacker recon and exploitation.
- **Recommendation:**
  - Remove debug output from production flow.
  - Log detailed diagnostics server-side only with sensitive-data redaction.
- **Confidence:** High

---

## Low Findings

### 13) Risky Boolean Logic Patterns (Always-True Conditions)
- **Severity:** Low
- **Affected Files:**
  - `inc/backend/session.php`
- **Issue:**
  - Conditional expressions that can evaluate true unexpectedly due to loose logic form.
- **Impact:**
  - Unintended behavior and configuration/security control drift.
- **Recommendation:**
  - Replace with strict comparisons and explicit logic branches.
  - Add tests for control-flow-critical conditionals.
- **Confidence:** High

### 14) Reversible Quote ID Obfuscation Design
- **Severity:** Low
- **Affected Files:**
  - `inc/backend/database.php`
  - `customer_quote.php`
- **Issue:**
  - Static key/IV-based obfuscation for externally shared link identifiers.
- **Impact:**
  - If key material leaks, IDs may become forgeable/reversible.
- **Recommendation:**
  - Replace with signed, expiring tokens tied to quote ID + context.
  - Keep signing secrets outside source code and rotate periodically.
- **Confidence:** Medium

---

## Likely False Positives / Needs Runtime Validation

- Some exploitability depends on deployment controls (WAF, reverse proxy, restrictive routing, hardened DB permissions).
- Certain endpoint exposure impact varies based on network-level access restrictions.
- File deletion/upload abuse severity increases significantly if an attacker can already write malicious paths to DB records.

---

## Remediation Plan (Suggested Order)

### Phase 1 (Immediate: 24-72 hours)
1. Rotate all exposed credentials and externalize secrets.
2. Introduce centralized authn/authz checks for all API/data retrieval and mutation endpoints.
3. Add CSRF protection to all state-changing actions.
4. Remove debug output from production code paths.

### Phase 2 (Short Term: 1-2 weeks)
1. Refactor DB access to strict parameterized queries only.
2. Patch high-risk IDOR/XSS points in job/quote/customer pages.
3. Harden upload/delete handling and add path canonicalization checks.

### Phase 3 (Hardening: 2-4 weeks)
1. Upgrade password/session handling to modern standards.
2. Add cookie security attributes and session lifecycle hardening.
3. Add automated security checks (SAST, secret scanning, lint rules for unsafe SQL/output patterns).

---

## Notes

- This file is intended as a master issue register for whole-project risk triage.
- For implementation tracking, convert each finding into ticket(s) with owner, ETA, and acceptance criteria.
