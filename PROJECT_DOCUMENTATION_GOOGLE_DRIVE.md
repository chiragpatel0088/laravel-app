# Jaxxon Concrete Pumps — Technical Project Documentation

## Table of Contents
- Project Overview
- Tech Stack
- Installation and Setup Guide
- System Architecture
- Feature Breakdown
- Workflow and Process Flows
- API Documentation
- Database Schema
- Key Modules and Responsibilities
- Error Handling and Edge Cases
- Assumptions and Limitations
- Recommendations and Improvements

## Project Overview
This project is a PHP-based operations platform for managing concrete pumping workflows. It supports office/admin users and field teams (operators and linesmen) from quote creation through job completion and invoicing.

### Primary Goals
- Manage quote and job lifecycles end-to-end.
- Coordinate assignment and completion workflows for operators/linesmen.
- Handle site inspections, including photo uploads.
- Maintain operational master data (customers, trucks, suppliers, etc.).
- Generate reports and send workflow notifications/emails.

### Domain
Concrete pumping dispatch and operations management.

## Tech Stack

### Backend
- PHP (server-rendered architecture)
- MySQL via PDO
- DataTables Editor (server-side CRUD controllers)
- PHPMailer (SMTP email)
- PhpSpreadsheet
- FPDF (PDF reports)

### Frontend
- PHP views with jQuery-based page scripts
- DataTables, DataTables Editor JS
- Select2
- Dashmix template assets
- Additional vendored JS/CSS plugins

### Server/Runtime
- Apache rewrite-based routing (`.htaccess`)
- Local `php.ini`
- Composer for PHP dependency management

## Installation and Setup Guide

### 1) Clone and initialize
The repository README includes basic git bootstrap instructions.

### 2) Install PHP dependencies
```bash
composer install
```

### 3) Configure database and SMTP
Edit:
- `inc/backend/db_cred_constants.php`

Set:
- DB constants (`DB_SERVER`, `DB_USER`, `DB_PASS`, `DB_NAME`)
- SMTP constants used by `inc/backend/mailer.php`

### 4) Configure web server
- Ensure Apache `mod_rewrite` is enabled.
- Ensure `.htaccess` is respected (`AllowOverride`).
- Confirm active PHP runtime version (repo has legacy/new handler directives).

### 5) Ensure writable upload storage
- `site_inspection_photos/`

### Important Note
- No SQL migrations/schema dump were found in this repository. Database bootstrap appears external/manual.

## System Architecture

### High-Level Components
- Route and page layer: top-level `*.php` pages, extensionless URL rewrites.
- Action orchestration: `process.php`.
- Core business and data service: `inc/backend/database.php`.
- CRUD APIs: `inc/backend/editor_controllers/*.php`.
- Lookup/data APIs: `inc/backend/data_retrieval/**/*.php`.
- Persistence: MySQL.
- Integrations: SMTP, Addy, Google Maps links, Teams SDK include.

### Request Architecture (Text Diagram)
```text
Browser UI (PHP pages + JS)
  -> process.php (command/action POSTs)
  -> editor_controllers/*.php (DataTables CRUD)
  -> data_retrieval/*.php (lookups/detail APIs)

process.php + controllers
  -> database.php (business/data logic)
  -> MySQL
  -> mailer.php -> SMTP
```

## Feature Breakdown

### Authentication and Access Control
- Login/account flows via `process.php`.
- Session and role-based access checks in backend/global bootstrap files.

### Jobs
- Create, edit, copy, cancel, reinstate, complete, invoice.
- Assignment flows for operators and linesmen.
- Status progression based on completeness and completion state.

### Quotes
- Create/update quotes.
- Email quote to customer/stakeholders.
- Accept/decline handling.
- Convert accepted quote into a job.

### Site Inspections
- Create and assign site inspections.
- Complete with notes and photo uploads.
- Site inspection state affects job progression.

### Invoicing
- Mark jobs ready for invoicing.
- Save invoice data.
- Mark invoiced and persist invoice identifiers/dates.

### Reporting
- Multiple report screens and PDF outputs (jobs, sales, hours, cubic volume by dimensions such as operator/truck/customer/year).

### Master Data Management
- CRUD for customers, operators, foremen, layers, suppliers, trucks, job types, and concrete types.

## Workflow and Process Flows

### Quote to Job
```text
Create Quote
  -> Edit/Review Quote
    -> Accept?
      -> No: Decline + notify
      -> Yes: Create Job from Quote + create link record
```

### Job Lifecycle
```text
New Job
  -> Required fields completed?
    -> No: Pending/incomplete
    -> Yes: Site inspection complete?
      -> No: Pending site inspection
      -> Yes: Assigned to operator?
        -> No: Ready to assign
        -> Yes: Assigned
          -> Operator completes job
            -> Job complete
              -> Ready for invoicing
                -> Invoiced
```

### Site Inspection
```text
Create Inspection
  -> Assign Operator
    -> Submit completion info + photos
      -> Save inspection completion
        -> Recompute job status + notify
```

## API Documentation

## 1) Central Action Endpoint
- Endpoint: `POST /process` (rewritten to `process.php`)
- Model: one POST action flag per request (e.g., `subnewjob=true`)

### Common action groups
- Auth/account: `sublogin`, `subjoin`, `subforgot`, `subedit`
- Quotes: `subnewquote`, `subupdatequote`, `subsemailquote`, acceptance/decline flags
- Jobs: `subnewjob`, `subupdatejob`, `assignoperator`, `assignLinesmen`, completion/invoicing flags
- Site inspections: `subcompletesiteinspection`, `subupdatesiteinspection`
- Retrieval: `getjobdetails`, `getquotedetails`
- Notifications: `submarknotificationsasread`

### Response behavior
- Redirect responses (many form actions)
- JSON responses (detail and some AJAX paths)
- Plain text/empty responses (some seamless update paths)

## 2) Data Retrieval Endpoints
Examples:
- `inc/backend/data_retrieval/getCustomerDetails.php`
- `inc/backend/data_retrieval/getOperatorDetails.php`
- `inc/backend/data_retrieval/getSiteVisitDetails.php`
- `inc/backend/data_retrieval/getAllNotificationsForUser.php`

Select2 endpoints:
- `inc/backend/data_retrieval/select2/select2_get*.php`
- `inc/backend/data_retrieval/select2/select2_getSingle*.php`

## 3) DataTables Editor CRUD Endpoints
Located in:
- `inc/backend/editor_controllers/`

Includes:
- jobs, quotes, site inspections, history, and master data CRUD controllers

## Database Schema
No migration or SQL dump file was found in-repo; schema is inferred from constants, joins, and query usage.

### Core Tables
- `users`
- `active_users`
- `active_guests`
- `banned_users`
- `configuration`
- `user_levels`
- `jobs`
- `job_statuses`
- `job_types`
- `quotes`
- `job_quote_link`
- `customers`
- `suppliers`
- `trucks`
- `layers`
- `foremen`
- `concrete_types`
- `site_visits`
- `linesman_jobs`
- `invoice_data`
- `job_change_logs`
- `notifications`

### Major Relationships (Inferred)
- `jobs.customer_id -> customers.id`
- `jobs.operator_id -> users.ID`
- `jobs.truck_id -> trucks.id`
- `jobs.supplier_id -> suppliers.id`
- `jobs.layer_id -> layers.id`
- `jobs.foreman_id -> foremen.id`
- `jobs.job_type -> job_types.id`
- `jobs.concrete_type -> concrete_types.id`
- `jobs.status -> job_statuses.id`
- `site_visits.site_visit_job_id -> jobs.id`
- `linesman_jobs.job_id -> jobs.id`
- `invoice_data.job_id -> jobs.id`
- `job_change_logs.job_id -> jobs.id`

## Key Modules and Responsibilities

### `process.php`
- Central workflow command router and orchestration layer.

### `inc/backend/database.php`
- Core data access and business-logic-heavy service class.

### `inc/backend/job_status.php`
- Job status derivation and transition calculation rules.

### `inc/backend/editor_controllers/*.php`
- DataTables Editor-backed server-side CRUD interfaces.

### `inc/backend/data_retrieval/**/*.php`
- Lightweight lookup/detail endpoints for dynamic form UX.

### `inc/backend/mailer.php`
- Email integration and message dispatch via SMTP.

## Error Handling and Edge Cases

### Existing Patterns
- Session-based errors + redirect flow in form-driven actions.
- JSON error payloads on selected AJAX actions.
- Transactions/try-catch in some critical write operations.

### Observed Risks
- Mixed response styles (redirect/text/JSON) complicate API contracts.
- Inconsistent parameterized SQL usage; string interpolation appears in many queries.
- Destructive action exists via GET endpoint (risk-prone pattern).
- Debug-like output in operational paths can affect response correctness/log hygiene.

## Assumptions and Limitations

### Assumptions
- Database schema is pre-provisioned outside repo.
- Role/user-level behavior depends on DB + constants.
- Top-level `.php` files are route screens through rewrite rules.

### Documentation Limitations
- No canonical ERD or DDL in repository.
- API contracts inferred from code, not OpenAPI specs.
- Deployment model inferred from config files; no dedicated deploy pipeline files.

## Recommendations and Improvements

### Security and Reliability
- Move credentials/secrets out of code constants into environment-managed secrets.
- Enforce parameterized SQL everywhere.
- Replace destructive GET operations with authenticated POST/DELETE.
- Standardize structured JSON error/success envelopes for API endpoints.

### Architecture and Maintainability
- Split `database.php` into domain services (jobs, quotes, invoicing, inspections, notifications).
- Introduce migration tooling and versioned DB change scripts.
- Centralize validation and error handling helpers/middleware.

### Developer Experience
- Expand README with complete local setup, database bootstrap, and troubleshooting.
- Add versioned architecture docs and ERD under a `docs/` directory.
- Add automated tests for critical lifecycle paths (quote->job, assignment, completion, invoicing).

