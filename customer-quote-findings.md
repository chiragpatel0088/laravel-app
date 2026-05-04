# Customer Quote Findings Report

## 1) Issue / Finding
Unescaped values are rendered inside HTML attributes, which can lead to Cross-Site Scripting (XSS).

- **Area:** Security (Application Security / Output Encoding)
- **Affected Files:** `customer_quote.php`
- **Severity:** Critical
- **Screenshot:** To be attached (example: browser devtools showing injected attribute payload)
- **Recommendation:**
  - Escape every dynamic value rendered in HTML attributes using `htmlspecialchars($value, ENT_QUOTES, 'UTF-8')`.
  - Apply this to JSON payload hidden inputs and the quote id hidden input.
  - Keep escaping consistent for all attribute contexts, not only selected fields.

---

## 2) Issue / Finding
Customer quote accept/decline actions are missing CSRF protection, allowing forged requests from third-party sites.

- **Area:** Security (Request Integrity / CSRF)
- **Affected Files:** `customer_quote.php`, `inc/modals/customer_decline_quote.php`, `process.php`
- **Severity:** High
- **Screenshot:** To be attached (example: forged POST request proof-of-concept)
- **Recommendation:**
  - Generate CSRF token on page load and store it in session.
  - Add token as hidden input in both accept and decline forms.
  - Validate token server-side in `procCustomerAcceptQuote()` and `procCustomerDeclineQuote()` before processing.
  - Reject requests with invalid/missing token and log the event.

---

## 3) Issue / Finding
Customer accept handler has weak server-side validation (decoded ID not strictly validated, no state checks before update).

- **Area:** Security + Logic (Authorization/Validation Flow)
- **Affected Files:** `process.php`
- **Severity:** High
- **Screenshot:** To be attached (example: invalid/malformed id request still reaching handler)
- **Recommendation:**
  - Validate decoded quote id is numeric and greater than zero.
  - Confirm quote exists before attempting update.
  - Enforce state check to prevent re-accepting/reprocessing already responded quotes.
  - Return controlled error response/redirect on validation failure.

---

## 4) Issue / Finding
Code assumes quote lookup always returns a row (`[0]` indexing), which can trigger undefined offset or runtime errors when data is missing.

- **Area:** Stability + Logic (Defensive Data Handling)
- **Affected Files:** `customer_quote.php`, `process.php`
- **Severity:** Medium
- **Screenshot:** To be attached (example: PHP warning/notice for undefined offset)
- **Recommendation:**
  - Check query result is non-empty before indexing.
  - Handle missing quote with a controlled 404/invalid-link response.
  - Avoid chained lookups that depend on unverified previous result objects.

---

## 5) Issue / Finding
`addslashes()` is used for decline reason processing, which is brittle and not a safe replacement for parameterized queries.

- **Area:** Security + Data Integrity (Input Handling)
- **Affected Files:** `process.php`
- **Severity:** Medium
- **Screenshot:** To be attached (example: special-character input edge case)
- **Recommendation:**
  - Replace manual escaping with parameterized database operations.
  - Whitelist fixed reason values (e.g., Price, Job Cancelled, etc.).
  - For "Other", apply `trim`, length limit, and allowed-character validation before save.

---

## 6) Issue / Finding
Direct `die()` calls expose non-standard internal/error messages to end users.

- **Area:** Security + UX + Maintainability (Error Handling)
- **Affected Files:** `customer_quote.php`
- **Severity:** Low
- **Screenshot:** To be attached (example: raw error text shown in browser)
- **Recommendation:**
  - Replace `die()` with consistent error handling flow (redirect or dedicated error page).
  - Return appropriate HTTP status codes (`400`, `403`, or `404` as applicable).
  - Log internal details server-side and show user-safe generic messages in UI.

---

## Severity Legend
- **Critical:** Immediate exploitation risk with high impact.
- **High:** Significant security or business impact; prioritize in current sprint.
- **Medium:** Important weakness with moderate impact/risk.
- **Low:** Best-practice gap; fix as part of hardening and cleanup.
