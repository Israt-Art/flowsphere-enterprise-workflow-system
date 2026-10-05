async function checkAuth() {
    const response = await fetch("../backend/api/auth/me.php");
    const data = await response.json();

    if (!data.success || data.user.role !== "director") {
        window.location.href = "login.html";
        return;
    }

    document.getElementById("user-name").textContent = data.user.name;
}

// Builds a compact stepper from the REAL history array director_pending.php
// returns (manager + hr actions), plus the current Director step.
function renderStepper(history) {
    const doneRoles = history.map(h => h.role_at_time);

    const steps = [
        { key: "manager", label: "Manager" },
        { key: "hr", label: "HR" }
    ];

    let html = `<div class="stepper"><div class="step done"><span class="dot">&#10003;</span> Submitted</div>`;

    steps.forEach(step => {
        const isDone = doneRoles.includes(step.key);
        html += `<div class="connector ${isDone ? "done" : ""}"></div>`;
        html += `<div class="step ${isDone ? "done" : ""}"><span class="dot">${isDone ? "&#10003;" : "?"}</span> ${step.label}</div>`;
    });

    html += `<div class="connector done"></div><div class="step current"><span class="dot">3</span> Director</div></div>`;
    return html;
}

function renderHistoryList(history) {
    if (!history || history.length === 0) {
        return `<span style="color:var(--color-text-muted); font-size:12px;">No history yet</span>`;
    }

    return `<div class="timeline">` + history.map(h => {
        const date = new Date(h.action_date).toLocaleDateString();
        const comment = h.comment ? ` - "${h.comment}"` : "";
        return `
            <div class="timeline-item">
                <div class="timeline-dot">&#10003;</div>
                <div>
                    <div class="timeline-title">${h.role_at_time} ${h.action} by ${h.actor_name}</div>
                    <div class="timeline-meta">${date}${comment}</div>
                </div>
            </div>
        `;
    }).join("") + `</div>`;
}

async function loadPending() {
    const response = await fetch("../backend/api/workflow/director_pending.php");
    const data = await response.json();

    const container = document.getElementById("pending-body");
    container.innerHTML = "";

    document.getElementById("stat-pending").textContent = data.success ? data.documents.length : 0;

    if (!data.success || data.documents.length === 0) {
        container.innerHTML = `<p class="empty-state">No documents waiting for review.</p>`;
        return;
    }

    data.documents.forEach(doc => {
        const card = document.createElement("div");
        card.className = "document-card";
        card.innerHTML = `
            <div class="doc-header">
                <div class="doc-title">${doc.title}</div>
                <span class="badge badge-pending_director">Pending Director</span>
            </div>
            <div class="doc-meta">Submitted by ${doc.submitted_by_name} on ${new Date(doc.created_at).toLocaleDateString()}</div>
            ${doc.description ? `<div class="doc-description">${doc.description}</div>` : ""}
            ${renderStepper(doc.history)}
            ${doc.file_path
                ? `<a href="../backend/api/documents/download.php?id=${doc.id}" target="_blank">Download attachment</a>`
                : `<span style="color:var(--color-text-muted); font-size:13px;">No attachment</span>`}
            <h4 style="font-size:12px; text-transform:uppercase; color:var(--color-text-muted); margin:12px 0 6px;">Workflow History</h4>
            ${renderHistoryList(doc.history)}
            <input type="text" class="comment-input" id="comment-${doc.id}" placeholder="Comment (required to reject)">
            <div class="doc-actions">
                <button class="btn-approve" onclick="approveDocument(${doc.id})">Final Approve</button>
                <button class="btn-reject" onclick="rejectDocument(${doc.id})">Reject</button>
            </div>
        `;
        container.appendChild(card);
    });
}

async function approveDocument(documentId) {
    const comment = document.getElementById(`comment-${documentId}`).value.trim();

    const response = await fetch("../backend/api/workflow/director_approve.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ document_id: documentId, comment })
    });

    const data = await response.json();
    alert(data.message);
    loadPending();
}

async function rejectDocument(documentId) {
    const comment = document.getElementById(`comment-${documentId}`).value.trim();

    if (comment === "") {
        alert("Please enter a comment explaining the rejection.");
        return;
    }

    const response = await fetch("../backend/api/workflow/director_reject.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ document_id: documentId, comment })
    });

    const data = await response.json();
    alert(data.message);
    loadPending();
}

document.getElementById("logout-btn").addEventListener("click", async function () {
    await fetch("../backend/api/auth/logout.php", { method: "POST" });
    window.location.href = "login.html";
});

document.getElementById("menu-toggle").addEventListener("click", function () {
    document.getElementById("sidebar").classList.toggle("open");
});

checkAuth();
loadPending();
