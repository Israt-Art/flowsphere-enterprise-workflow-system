# 🚀 FlowSpherePro

## Enterprise Workflow Management System

FlowSpherePro is a document submission and approval workflow management system built with **PHP, MySQL, HTML5, CSS3, and Vanilla JavaScript**.

The system manages the complete document approval process from **Employee → Manager → HR → Director**, while maintaining a complete workflow history of every approval and rejection action.

> 🎯 **Goal:** Provide a simple, role-based document workflow system with authentication, document management, approval processing, and audit history.

---

## ✨ Key Features

- 🔐 Secure login and logout
- 👥 Role-based access control
- 📄 Document submission
- 📎 Optional file attachment
- 📋 Document status tracking
- 🔄 Multi-level approval workflow
- ✅ Approve / ❌ Reject functionality
- 💬 Required comments for rejection
- 📝 Complete workflow history
- 📥 Secure document downloading
- 🔒 Password hashing using bcrypt
- 🛡️ Prepared SQL statements
- 📦 File upload validation
- 🚫 Upload directory script protection
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
