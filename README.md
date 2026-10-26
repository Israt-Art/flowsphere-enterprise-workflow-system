# 🚀 FlowSpherePro

## Enterprise Workflow Management System

FlowSpherePro is a document submission and approval workflow management system built with **PHP, MySQL, HTML5, CSS3, and Vanilla JavaScript**.

The system manages the complete document approval process from **Employee → Manager → HR → Director**, while maintaining a complete workflow history of every approval and rejection action.

> 🎯 **Goal:** Provide a simple, role-based document workflow system with authentication, document management, approval processing, and audit history.

---

## ✨ Key Features

- 🔐 Login and logout
- 👥 Role-based access control
- 📄 Document submission with optional file attachments
- 📋 Document status tracking
- 🔄 Multi-level approval workflow
- ✅ Document approval and rejection
- 💬 Mandatory comments for rejection
- 📝 Workflow history tracking
- 📥 Secure document downloads
- 🔒 Bcrypt password hashing
- 🛡️ Prepared SQL statements
- 📦 File upload validation and script execution protection
- 🔄 Session ID regeneration after login
- ⚡ Conditional workflow updates to prevent duplicate processing
---

# 🔄 Workflow

A document passes through three levels of review:

```text
┌─────────────────────┐
│      EMPLOYEE       │
│   Submit Document   │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│      MANAGER        │
│       REVIEW        │
└───────┬───────┬─────┘
        │       │
    Approve    Reject
        │       │
        ▼       ▼
┌─────────────┐ ┌─────────────┐
│     HR      │ │  REJECTED   │
│   REVIEW    │ └─────────────┘
└──────┬──────┘
       │
    Approve
       │
       ▼
┌─────────────────────┐
│      DIRECTOR       │
│       REVIEW        │
└───────┬───────┬─────┘
        │       │
    Approve    Reject
        │       │
        ▼       ▼
┌─────────────┐ ┌─────────────┐
│  COMPLETED  │ │  REJECTED   │
└─────────────┘ └─────────────┘

```

## 📋 Workflow Rules

1. **Submission:** Employee submits a document with status `pending_manager`.
2. **Manager Review:** Approve → `pending_hr` | Reject → `rejected`.
3. **HR Review:** Approve → `pending_director` | Reject → `rejected`.
4. **Director Review:** Approve → `approved` | Reject → `rejected`.
5. **History:** Every decision is recorded in `workflow_history`.


## 👥 User Roles & Permissions

### 👨‍💼 Employee
- Login / Logout
- Submit documents with title, description & optional attachment
- View submitted documents and track status
- View complete workflow history

### 👔 Manager
- View and review pending documents
- Download attachments
- Approve or reject (comment required for rejection)

### 🧑‍💼 HR
- Review documents approved by Manager
- Download attachments
- View Manager's approval details
- Approve or reject (comment required for rejection)

### 👨‍💼 Director
- Review documents pending final approval
- Download attachments
- View complete workflow history
- Final approve or reject (comment required for rejection)





## 🛠️ Technology Stack

| Layer | Technology |
|---|---|
| 🎨 Frontend | HTML5, CSS3, Vanilla JavaScript |
| ⚙️ Backend | PHP |
| 🗄️ Database | MySQL |
| 🔗 API Communication | Fetch API |
| 🔐 Authentication | PHP Native Sessions |
| 🖥️ Web Server | Apache |
| 🧪 Local Environment | XAMPP |

---

## 🏗️ Development Approach

- No frontend framework
- No backend framework
- No build tools
- No package manager

The application uses simple and readable technologies so that the complete request, authentication, database, and workflow logic can be understood directly from the source code.


---

## 📁 Project Structure
```

FlowSpherePro/
│
├── frontend/
│   ├── login.html
│   ├── employee-dashboard.html
│   ├── manager-dashboard.html
│   ├── hr-dashboard.html
│   ├── director-dashboard.html
│   │
│   ├── css/
│   │   └── style.css
│   │
│   └── js/
│       ├── login.js
│       ├── employee-dashboard.js
│       ├── manager-dashboard.js
│       ├── hr-dashboard.js
│       └── director-dashboard.js
│
├── backend/
│   ├── api/
│   │   ├── auth/
│   │   │   ├── login.php
│   │   │   ├── logout.php
│   │   │   └── me.php
│   │   │
│   │   ├── documents/
│   │   │   ├── create.php
│   │   │   ├── list.php
│   │   │   ├── get.php
│   │   │   └── download.php
│   │   │
│   │   └── workflow/
│   │       ├── manager_pending.php
│   │       ├── manager_approve.php
│   │       ├── manager_reject.php
│   │       ├── hr_pending.php
│   │       ├── hr_approve.php
│   │       ├── hr_reject.php
│   │       ├── director_pending.php
│   │       ├── director_approve.php
│   │       └── director_reject.php
│   │
│   ├── config/
│   │   └── database.php
│   │
│   ├── includes/
│   │   └── auth.php
│   │
│   └── uploads/
│       ├── .htaccess
│       └── uploaded files
│
├── database/
│   └── schema.sql
│
├── README.md

├── TESTING.md
├── INTERVIEW_PREP.md
└── .gitignore

```



## 🗄️ Database Design

FlowSpherePro uses three main database tables:

### 1. `users`
- Stores user information and account credentials.
- Supports four roles: `employee`, `manager`, `hr`, and `director`.
- Passwords are secured using **bcrypt hashing**.

### 2. `documents`
- Stores submitted document details, including title, description, file path, and creation time.
- Tracks the submitting employee and current workflow status.
- `current_status` serves as the source of truth for the document's current stage.

### 3. `workflow_history`
- Records every approval and rejection, including action, role, comment, user, and date.
- Uses an **append-only approach** to preserve previous workflow actions and maintain a complete history.



---



# 🔐 Authentication & Authorization

FlowSpherePro uses PHP native sessions for authentication and role-based access control.

### Login Process
```text
Email + Password
       ↓
Database Lookup
       ↓
password_verify()
       ↓
Session ID Regeneration
       ↓
User Data Stored in $_SESSION
       ↓
Dashboard Access
```

- `password_hash()` — Secure password hashing using bcrypt.
- `password_verify()` — Password verification during login.
- `session_regenerate_id()` — Helps prevent session fixation.

---

# 🛡️ Security

### ✅ Implemented Measures

- **Password Security:** Password hashing and verification.
- **SQL Injection Protection:** Prepared statements using `mysqli`.
- **Role-Based Authorization:** `require_login()` and `require_role(['employee'])`.
- **File Upload Protection:** Extension allow-list, file size limits, and server-side validation.
- **Generated Filenames:** Unique filenames for uploaded files.
- **Upload Directory Protection:** `backend/uploads/.htaccess` restricts server-side script execution.


---

## ⚡ Workflow Concurrency Protection

Each workflow action uses conditional database updates to prevent duplicate processing.

For example, manager approval only succeeds when the document status is `pending_manager`.

```sql
UPDATE documents
SET current_status = 'pending_hr'
WHERE id = ?
AND current_status = 'pending_manager';
```

This prevents duplicate processing from multiple users, browser tabs, or repeated requests. The same approach is used for HR and Director approvals.


---



# 📄 Document Management

Employees can submit:

- Title
- Description
- Optional attachment

The document is stored in the database with:

```sql
current_status = 'pending_manager'

```

Managers, HR, and Directors can download attachments when reviewing documents.

Employees can only access documents they submitted themselves.


---

## 🔄 Workflow History

Every workflow action is recorded to maintain a complete history of the document approval process.

**Example Workflow:**

```text
Employee
   │
   └── Submitted Document
          │
          ▼
Manager
   │
   └── Approved
          │
          ▼
HR
   │
   └── Approved
          │
          ▼
Director
   │
   └── Approved
          │
          ▼
       Completed

```

The workflow history records:

- Who performed the action
- The user's role
- The action performed
- Any comments provided
- The date and time of the action

---


# 🚀 Local Installation

### 1. Install XAMPP

Install XAMPP with Apache, MySQL, and PHP.

### 2. Copy the Project

Copy the project folder into:

```text
C:\xampp\htdocs\
```

Final location:

```text
C:\xampp\htdocs\FlowSpherePro
```

### 3. Start XAMPP

Open the XAMPP Control Panel and start:

- Apache
- MySQL

### 4. Create the Database

Open phpMyAdmin:

```text
http://localhost/phpmyadmin
```

Import the SQL file:

```text
database/schema.sql
```

Alternatively, run:

```bash
mysql -u root -p < database/schema.sql
```

The schema also seeds four demo user accounts.

### 5. Configure Database Connection

Open:

```text
backend/config/database.php
```

Update the database credentials if your local MySQL configuration differs.

### 6. Open the Application

Open the following URL in your browser:

```text
http://localhost/FlowSpherePro/frontend/login.html
```

---

# 🔑 Demo Login Credentials

All demo accounts use the following password:

```text
password123
```

| Role | Email |
|---|---|
| 👨‍💼 Employee | `employee@flowsphere.com` |
| 👔 Manager | `manager@flowsphere.com` |
| 🧑‍💼 HR | `hr@flowsphere.com` |
| 👨‍💼 Director | `director@flowsphere.com` |



---

# 🧪 Testing

Manual testing instructions are available in [`TESTING.md`](TESTING.md).

The testing checklist covers:

### 🔐 Authentication
- Login with valid credentials
- Login with invalid credentials
- Logout
- Session validation

### 👨‍💼 Employee
- Submit document
- Submit document with attachment
- View document list and details
- View workflow history
- Download attachment

### 👔 Manager
- View pending documents
- Approve or reject documents
- Reject without a comment
- Download attachment

### 🧑‍💼 HR
- View pending documents
- Review manager actions
- Approve or reject documents

### 👨‍💼 Director
- View pending documents
- View complete workflow history
- Approve or reject documents

### 🛡️ Authorization
- Verify employees cannot access manager functions
- Verify managers cannot access HR functions
- Verify HR cannot access Director functions
- Verify users cannot access other employees' documents


---

# ⚠️ Current Limitations

FlowSpherePro is a learning and portfolio project, not a production-ready enterprise system.

The following features are not yet implemented:

- CSRF protection
- Login rate limiting
- Password reset functionality
- Email notifications
- Production HTTPS configuration
- Production secure cookie configuration

These features can be considered future improvements.



---

# 🔮 Future Improvements

- CSRF protection
- Login rate limiting
- Password reset functionality
- Email notifications
- Analytics dashboard
- Advanced document search
- Document filtering
- Improved mobile responsiveness
- Real-time workflow notifications
- Production HTTPS configuration
- Administrative user management



---

# 📸 Screenshots

The following screenshots showcase the application's interface and workflow:

```text
screenshots/
├── login.png
├── employee-dashboard.png
├── manager-dashboard.png
├── hr-dashboard.png
├── director-dashboard.png
└── workflow-history.png
```



---

# 📚 Project Documentation

| File | Description |
|---|---|
| 📖 `README.md` | Project overview and setup instructions |
| 🧪 `TESTING.md` | Manual testing checklist |
| 🎤 `INTERVIEW_PREP.md` | Architecture overview and interview preparation |
| 🗄️ `database/schema.sql` | Database schema and demo data |



---

# 🎯 Project Objective

FlowSpherePro demonstrates a role-based document workflow system using a simple web technology stack.

The project covers:

- Authentication and authorization
- Session management
- Database operations
- Prepared SQL statements
- File uploads
- Role-based approval workflow
- Conditional database updates
- Workflow history tracking
- Basic web security

The project avoids frameworks to keep the implementation straightforward and easy to understand.
