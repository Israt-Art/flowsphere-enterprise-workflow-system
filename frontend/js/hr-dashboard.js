async function checkAuth() {
    const response = await fetch("../backend/api/auth/me.php");
    const data = await response.json();

    if (!data.success || data.user.role !== "hr") {
        window.location.href = "login.html";
        return;
    }

    document.getElementById("user-name").textContent = data.user.name;
}

async function loadPending() {
    const response = await fetch("../backend/api/workflow/hr_pending.php");
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
                <span class="badge badge-pending_hr">Pending HR</span>
            </div>
            <div class="doc-meta">Submitted by ${doc.submitted_by_name} on ${new Date(doc.created_at).toLocaleDateString()}</div>
            ${doc.description ? `<div class="doc-description">${doc.description}</div>` : ""}
            <div class="stepper">
                <div class="step done"><span class="dot">&#10003;</span> Submitted</div>
                <div class="connector done"></div>
                <div class="step done"><span class="dot">&#10003;</span> Manager</div>
                <div class="connector"></div>
                <div class="step current"><span class="dot">2</span> HR</div>
            </div>
            ${doc.manager_name
                ? `<div class="doc-meta">Approved by <strong>${doc.manager_name}</strong>${doc.manager_comment ? ` - "${doc.manager_comment}"` : ""}</div>`
                : ""}
            ${doc.file_path
                ? `<a href="../backend/api/documents/download.php?id=${doc.id}" target="_blank">Download attachment</a>`
                : `<span style="color:var(--color-text-muted); font-size:13px;">No attachment</span>`}
            <input type="text" class="comment-input" id="comment-${doc.id}" placeholder="Comment (required to reject)">
            <div class="doc-actions">
                <button class="btn-approve" onclick="approveDocument(${doc.id})">Approve</button>
                <button class="btn-reject" onclick="rejectDocument(${doc.id})">Reject</button>
            </div>
        `;
        container.appendChild(card);
    });
}

async function approveDocument(documentId) {
    const comment = document.getElementById(`comment-${documentId}`).value.trim();

    const response = await fetch("../backend/api/workflow/hr_approve.php", {
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

    const response = await fetch("../backend/api/workflow/hr_reject.php", {
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
