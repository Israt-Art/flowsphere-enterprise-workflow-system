# FlowSpherePro — Enterprise Workflow Management System

A document submission and approval workflow platform built with **plain
PHP, MySQL, HTML/CSS, and vanilla JavaScript** — no frameworks, no
build tools, just files you can open and read top to bottom.

---

## 1. Project Overview

Employees submit documents. Each document is reviewed in order by a
Manager, then HR, then a Director. Any reviewer can reject a document
with a comment, which ends the workflow immediately.

```
Employee submits
      ↓
Manager Review   → approve → forwards to HR
      ↓
HR Review        → approve → forwards to Director
      ↓
Director Review  → approve → Completed
```

At every stage, "reject" instead sends the document straight to a
`rejected` state with a required comment explaining why.

---

## 2. Features

**Employee**
- Log in / log out
- Submit a document (title, description, optional file attachment)
- View their own documents and current status
- View the full workflow history for any document they submitted

**Manager**
- View documents waiting for manager review
- Download the attachment
- Approve (forwards to HR) or reject (with required comment)

**HR**
- View documents waiting for HR review
- See the manager's earlier approval comment for context
- Approve (forwards to Director) or reject (with required comment)

**Director**
- View documents waiting for final review
- See the *complete* workflow history so far
- Give final approval (marks the document `Completed`) or reject

---

## 3. Technology Stack

| Layer     | Technology                          |
|-----------|--------------------------------------|
| Frontend  | HTML5, CSS3, Vanilla JavaScript (Fetch API, async/await) |
| Backend   | PHP (no framework)                  |
| Database  | MySQL (via `mysqli`, prepared statements) |
| Auth      | PHP native sessions (`$_SESSION`)   |
| Server    | Apache (tested with XAMPP)          |

---

## 4. Folder Structure

```
FlowSpherePro/
  frontend/
    login.html
    employee-dashboard.html
    manager-dashboard.html
    hr-dashboard.html
    director-dashboard.html
    css/
      style.css
    js/
      login.js
      employee-dashboard.js
      manager-dashboard.js
      hr-dashboard.js
      director-dashboard.js

  backend/
    api/
      auth/
        login.php        - checks credentials, starts a session
        logout.php        - destroys the session
        me.php            - "who is logged in right now?"
      documents/
        create.php        - employee submits a document (+ file upload)
        list.php          - employee's own document list
        get.php           - one document's detail + full history
        download.php      - serves an uploaded attachment
      workflow/
        manager_pending.php / manager_approve.php / manager_reject.php
        hr_pending.php     / hr_approve.php     / hr_reject.php
        director_pending.php / director_approve.php / director_reject.php
    config/
      database.php        - the single shared DB connection
    includes/
      auth.php            - require_login() / require_role() helpers
    uploads/
      .htaccess           - blocks script execution in this folder
      (uploaded files land here at runtime)

  database/
    schema.sql             - run once to create the database + tables

  README.md
  TESTING.md               - manual test checklist
  INTERVIEW_PREP.md         - architecture + Q&A study guide
```

---

## 5. Database Setup

Three tables (see `database/schema.sql` for full detail + comments):

- **users** — one row per person; `role` is `employee`/`manager`/`hr`/`director`;
  passwords are stored as bcrypt hashes, never plain text.
- **documents** — one row per submission; `current_status` is the single
  source of truth for where it sits in the workflow right now.
- **workflow_history** — an append-only audit log; every approve/reject
  action adds one row here and nothing is ever overwritten.

To set it up:

```bash
mysql -u root -p < database/schema.sql
```

(or paste the file's contents into phpMyAdmin's SQL tab). This also
seeds 4 demo users, one per role.

---

## 6. XAMPP Setup Steps

1. Copy the whole `FlowSpherePro/` folder into your XAMPP `htdocs` directory.
2. Start **Apache** and **MySQL** from the XAMPP control panel.
3. Import `database/schema.sql` (Step 5 above).
4. If your MySQL root user has a password, or you use a different
   username, update `backend/config/database.php` accordingly.
5. Open `http://localhost/FlowSpherePro/frontend/login.html` in your browser.

---

## 7. Default Login Credentials

All demo accounts use the password: **password123**

| Role      | Email                       |
|-----------|------------------------------|
| Employee  | employee@flowsphere.com      |
| Manager   | manager@flowsphere.com       |
| HR        | hr@flowsphere.com            |
| Director  | director@flowsphere.com      |

---

## 8. Workflow Explanation

1. An **Employee** submits a document. It's inserted into `documents`
   with `current_status = 'pending_manager'`.
2. A **Manager** reviewing it can:
   - **Approve** → status becomes `pending_hr`, a `workflow_history`
     row is logged (`role_at_time = 'manager'`, `action = 'approved'`).
   - **Reject** (comment required) → status becomes `rejected`, logged
     the same way with `action = 'rejected'`.
3. **HR** and the **Director** repeat the exact same pattern at their
   own stage, moving the document one step further (`pending_hr` →
   `pending_director` → `approved`) or ending it (`rejected`).
4. Every action anywhere in the chain is preserved in
   `workflow_history`, so the Employee (and the Director, at the final
   stage) can see the complete timeline of who did what and when.

Every approve/reject endpoint uses a **conditional UPDATE**
(`WHERE id = ? AND current_status = 'expected_status'`) so that two
people (or two browser tabs) can never process the same document twice.

---

## 9. Security Notes (what's implemented, and what's intentionally left out)

Implemented:
- Passwords hashed with `password_hash()` / checked with `password_verify()`
- All SQL uses prepared statements (`mysqli` + `bind_param`)
- Session-based auth with `require_login()` / `require_role()` guards on every protected endpoint
- File uploads use an **allow-list** of extensions, a size limit, and a randomly generated filename
- `uploads/.htaccess` blocks any uploaded file from being executed as a script (defense in depth)

Intentionally left out (this is a learning/portfolio project, not a
production system) — good things to mention as "next steps" in an interview:
- No CSRF tokens on state-changing requests
- No rate limiting on login attempts
- No HTTPS/cookie `Secure`/`HttpOnly` flag configuration (left to the web server in production)
- No password reset flow

---

## 10. Screenshots

*(Add screenshots here once you've run the project locally — e.g. the
login page, each dashboard, and the history modal.)*

- `screenshots/login.png`
- `screenshots/employee-dashboard.png`
- `screenshots/manager-dashboard.png`
- `screenshots/hr-dashboard.png`
- `screenshots/director-dashboard.png`

---

See `TESTING.md` for a manual test checklist and `INTERVIEW_PREP.md`
for an architecture walkthrough and 20 practice interview questions.
