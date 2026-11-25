# FlowSpherePro — Manual Testing Checklist

Run through these in order using the 4 demo accounts (see README.md
for credentials). Check each box as you confirm it works.

## Authentication

- [ ] Employee can log in with correct credentials
- [ ] Wrong password shows an error, no page reload, stays on login page
- [ ] Each role redirects to its own dashboard after login
      (employee → employee-dashboard.html, manager → manager-dashboard.html, etc.)
- [ ] Visiting a dashboard HTML file directly without logging in
      redirects back to login.html (via the me.php check)
- [ ] Logout button clears the session and returns to login.html
- [ ] After logout, the browser Back button does not show dashboard data

## Employee — Document Submission

- [ ] Submitting with a title only (no file) succeeds
- [ ] Submitting with a valid attachment (pdf/doc/docx/jpg/png) succeeds
- [ ] Submitting a disallowed file type (e.g. .exe) is rejected with a clear message
- [ ] Submitting a file over 5MB is rejected with a clear message
- [ ] Submitting with an empty title is blocked
- [ ] After submitting, the new document appears in "My Documents" with
      a "Pending Manager" badge
- [ ] Clicking "View History" on a document shows its detail + timeline

## Manager Review

- [ ] Manager sees the newly submitted document in their pending queue
- [ ] Manager can download the attachment
- [ ] Approving with no comment succeeds; document leaves the queue
- [ ] Rejecting with an empty comment is blocked (client-side)
- [ ] Rejecting with a comment succeeds; document status becomes "Rejected"
- [ ] Refreshing the manager dashboard does not show already-processed documents

## HR Review

- [ ] A manager-approved document appears in HR's pending queue
- [ ] HR's view shows the manager's name and comment (or "(no comment)")
- [ ] HR approve moves the document to "Pending Director"
- [ ] HR reject (with comment) sets status to "Rejected"

## Director Review

- [ ] An HR-approved document appears in the Director's pending queue
- [ ] Director's view shows the FULL history (manager's action AND HR's action)
- [ ] Director's Final Approve sets status to "Approved"
- [ ] Director reject (with comment) sets status to "Rejected"

## Rejection at Every Stage

- [ ] Reject at Manager stage → employee sees "Rejected" immediately
- [ ] Reject at HR stage → employee sees "Rejected" immediately
- [ ] Reject at Director stage → employee sees "Rejected" immediately
- [ ] A rejected document never reappears in any reviewer's pending queue

## File Download Permission

- [ ] Employee can download their own attachment
- [ ] Employee CANNOT download another employee's attachment
      (try changing the `id` in the URL manually)
- [ ] Manager/HR/Director can download any document's attachment while
      it's relevant to review

## Workflow History

- [ ] Employee's "View History" modal shows every action taken so far, in order
- [ ] Timestamps are in the correct order (oldest first)
- [ ] Comments show up correctly, or "(no comment)" when one wasn't given
- [ ] A document with no actions yet shows "No action has been taken yet."

## Double-Submission Safety

- [ ] Approving the same document twice in a row (e.g. two browser tabs)
      results in the second attempt getting a "no longer waiting" message,
      not a duplicate workflow_history row
