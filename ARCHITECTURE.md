
# FlowSpherePro — System Architecture

## 1. Overview

FlowSpherePro is a role-based document submission and approval workflow management system built using PHP, MySQL, HTML5, CSS3, and Vanilla JavaScript.

The system follows a simple three-layer architecture:

- **Frontend:** Handles user interaction and presentation.
- **Backend:** Processes requests, validates permissions, and executes business logic.
- **Database:** Stores users, documents, and workflow history.

The application uses PHP native sessions for authentication and the Fetch API for communication between the frontend and backend.

---

## 2. System Architecture

The application follows this communication flow:

```text
┌──────────────────────────────┐
│           FRONTEND           │
│                              │
│   HTML + CSS + JavaScript    │
│                              │
│   Login & Role Dashboards    │
└──────────────┬───────────────┘
               │
               │ Fetch API
               ▼
┌──────────────────────────────┐
│           BACKEND            │
│                              │
│          PHP APIs            │
│                              │
│  Authentication              │
│  Authorization               │
│  Document Management         │
│  Approval Workflow           │
│  File Upload & Download      │
└──────────────┬───────────────┘
               │
               │ MySQLi
               ▼
┌──────────────────────────────┐
│           DATABASE           │
│                              │
│            MySQL             │
│                              │
│  users                       │
│  documents                   │
│  workflow_history            │
└──────────────────────────────┘
```

### 2.1 Frontend Layer

The frontend is built using HTML5, CSS3, and Vanilla JavaScript.

It provides separate interfaces for four user roles:

- Employee Dashboard
- Manager Dashboard
- HR Dashboard
- Director Dashboard

JavaScript communicates with PHP API endpoints through the Fetch API. API responses are returned in JSON format and used to update the interface.

### 2.2 Backend Layer

The backend is developed using native PHP without a framework.

It handles:

- User authentication and session management
- Role-based authorization
- Document submission and retrieval
- File upload and download
- Approval and rejection operations
- Workflow history recording

Each API endpoint handles a specific operation.

### 2.3 Database Layer

MySQL is used for persistent data storage.

The database maintains user information, document records, current workflow status, and historical approval actions.

---

## 3. Database Architecture

The system uses three primary database tables.

### 3.1 Users

Stores user information and authentication credentials.

| Column | Purpose |
|---|---|
| id | Unique user identifier |
| full_name | User's name |
| email | Login email |
| password_hash | Bcrypt password hash |
| role | User's assigned role |
| created_at | Account creation timestamp |

Supported roles:

- employee
- manager
- hr
- director

### 3.2 Documents

Stores submitted document information and its current workflow status.

| Column | Purpose |
|---|---|
| id | Unique document identifier |
| submitted_by | Employee who submitted the document |
| title | Document title |
| description | Document description |
| file_path | Uploaded attachment path |
| current_status | Current workflow stage |
| created_at | Submission timestamp |
| updated_at | Last update timestamp |

The `current_status` column acts as the source of truth for the document's current position in the approval workflow.

### 3.3 Workflow History

Stores approval and rejection activities.

| Column | Purpose |
|---|---|
| id | Unique history identifier |
| document_id | Related document |
| action_by | User who performed the action |
| role_at_time | User's role when the action occurred |
| action | Approval or rejection action |
| comment | Review comment |
| action_date | Action timestamp |

The workflow history follows an append-only approach. Existing history records are preserved rather than overwritten during normal workflow processing.

---

## 4. Document Approval Lifecycle

A document passes through three approval stages after employee submission.

```text
          EMPLOYEE
              |
              | Submit Document
              ▼
      PENDING_MANAGER
              |
              ▼
          MANAGER
          /      \
     Approve    Reject
        |          |
        ▼          ▼
  PENDING_HR    REJECTED
        |
        ▼
         HR
       /    \
  Approve  Reject
     |       |
     ▼       ▼
PENDING_  REJECTED
DIRECTOR
     |
     ▼
   DIRECTOR
    /     \
Approve  Reject
   |       |
   ▼       ▼
APPROVED REJECTED
```

### 4.1 Workflow Status Transitions

| Current Status | Action | Next Status |
|---|---|---|
| pending_manager | Manager Approve | pending_hr |
| pending_manager | Manager Reject | rejected |
| pending_hr | HR Approve | pending_director |
| pending_hr | HR Reject | rejected |
| pending_director | Director Approve | approved |
| pending_director | Director Reject | rejected |

Every approval or rejection action is recorded in the `workflow_history` table.

---

## 5. Authentication and Authorization

FlowSpherePro uses PHP native sessions to manage authenticated users.

### 5.1 Login Process

```text
       Email + Password
              |
              ▼
       Database Lookup
              |
              ▼
       password_verify()
              |
              ▼
    session_regenerate_id()
              |
              ▼
      Create User Session
              |
              ▼
       Role Identification
              |
              ▼
      Redirect to Dashboard
```

Passwords are hashed using PHP's `password_hash()` function and verified using `password_verify()`.

After successful authentication, the session ID is regenerated to help prevent session fixation.

### 5.2 Role-Based Authorization

The application uses reusable authorization functions from `backend/includes/auth.php`.

- `require_login()` checks whether a user is authenticated.
- `require_role([...])` verifies whether the authenticated user has the required role.

Protected API endpoints perform authorization checks before executing their operations.

Each role can access only the functions permitted for that role.

---

## 6. API Communication Flow

The frontend communicates with backend endpoints using the Fetch API.

### Example: Manager Approval

```text
manager-dashboard.js
        |
        | POST Request
        | document_id
        | comment
        ▼
manager_approve.php
        |
        ▼
Check Manager Session
        |
        ▼
Validate Document Status
        |
        ▼
Conditional Database UPDATE
        |
        ▼
Check affected_rows
        |
        ├── No row updated
        |       |
        |       ▼
        |   Return Conflict
        |
        ▼
Insert Workflow History
        |
        ▼
Return JSON Response
        |
        ▼
Update Manager Dashboard
```

API endpoints return JSON responses to communicate success, validation errors, authorization failures, and workflow conflicts.

---

## 7. Workflow Concurrency Protection

The system uses conditional SQL updates to prevent duplicate workflow processing.

Example:

```sql
UPDATE documents
SET current_status = 'pending_hr'
WHERE id = ?
AND current_status = 'pending_manager';
```

The update succeeds only when the document is still waiting for manager approval.

If another request has already processed the document, the update affects no rows.

The application can then return a conflict response instead of processing the same approval again.

This approach helps protect workflow integrity when multiple requests attempt to process the same document.

---

## 8. Document Management and File Handling

Employees can submit documents with:

- Title
- Description
- Optional file attachment

The backend validates uploaded files before storing them.

The implemented protections include:

- File extension allow-list
- File size restriction
- Server-side validation
- Generated filenames
- Protected upload directory

The application uses `move_uploaded_file()` to store uploaded files.

The `.htaccess` file inside the upload directory restricts server-side script execution.

Employees can access their own submitted documents, while reviewers can access attachments associated with documents they are authorized to review.

---

## 9. Workflow History and Audit Trail

The `workflow_history` table maintains a record of actions performed throughout the approval process.

Each record captures:

- User who performed the action
- Role at the time of the action
- Approval or rejection action
- Review comment
- Date and time

The current document status is maintained separately in the `documents` table.

This separation allows the system to retrieve the current workflow state while preserving the history of previous decisions.

---

## 10. Security Considerations

The application implements several basic security measures.

| Security Measure | Implementation |
|---|---|
| Password Security | Bcrypt hashing |
| SQL Injection Protection | MySQLi prepared statements |
| Authentication | PHP native sessions |
| Session Fixation Protection | Session ID regeneration |
| Authorization | Role-based access checks |
| File Upload Validation | Extension and size validation |
| File Execution Protection | Upload directory `.htaccess` |
| Workflow Integrity | Conditional database updates |

### Current Security Limitations

The following protections are not yet implemented:

- CSRF protection
- Login rate limiting
- Password reset functionality
- Production HTTPS configuration
- Production secure cookie configuration

These are identified as future improvements before production deployment.

---

## 11. Design Decisions

### Why Native PHP?

Native PHP keeps the request handling, authentication, database queries, and workflow logic explicit and understandable.

### Why PHP Sessions?

The application uses a traditional single-server authentication model. Native PHP sessions provide a straightforward way to manage authenticated users.

### Why Separate Workflow History?

The `documents` table stores the current state, while `workflow_history` preserves previous actions. This avoids mixing current status with historical records.

### Why Conditional Database Updates?

Checking the expected current status during an update prevents multiple requests from advancing the same document through the workflow.

### Why Vanilla JavaScript?

The frontend does not require a large framework. Vanilla JavaScript and the Fetch API are sufficient for the application's interface and API communication requirements.

---

## 12. Future Architectural Improvements

Potential improvements include:

- CSRF protection for state-changing requests
- Login rate limiting
- Password reset functionality
- Email notifications
- Advanced document search and filtering
- Administrative user management
- Analytics dashboard
- Improved mobile responsiveness
- Real-time workflow notifications
- Production HTTPS and secure cookie configuration

---

## 13. Summary

FlowSpherePro uses a simple three-layer architecture to manage document submission and multi-level approval.

Its architecture separates presentation, business logic, and data storage while maintaining role-based access control and a structured workflow history.

The project demonstrates practical implementation of authentication, authorization, database operations, file handling, and workflow integrity using native web technologies.
