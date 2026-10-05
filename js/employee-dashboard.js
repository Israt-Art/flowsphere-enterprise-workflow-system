function formatStatus(status) {
    const labels = {
        pending_manager: "Pending Manager",
        pending_hr: "Pending HR",
        pending_director: "Pending Director",
        approved: "Approved",
        rejected: "Rejected"
    };
    return labels[status] || status;
}

// Builds the small "Submitted -> Manager -> HR -> Director -> Approved"
// progress stepper shown on each document card, based on current_status.
// Rejected documents just show where the chain stopped - the exact
// stage and reason are available via "View History" (get.php).
function buildStepper(status) {
    const stages = ["manager", "hr", "director", "approved"];
    const labels = { manager: "Manager", hr: "HR", director: "Director", approved: "Approved" };

    if (status === "rejected") {
        return `
            <div class="stepper">
                <div class="step done"><span class="dot">&#10003;</span> Submitted</div>
                <div class="connector"></div>
                <div class="step rejected"><span class="dot">&#10007;</span> Rejected</div>
            </div>
        `;
    }

    const currentIndex = stages.indexOf(status.replace("pending_", ""));

    let html = `<div class="stepper"><div class="step done"><span class="dot">&#10003;</span> Submitted</div>`;

    stages.forEach((stage, i) => {
        const isDone = status === "approved" ? true : i < currentIndex;
        const isCurrent = status !== "approved" && i === currentIndex;
        const stateClass = isDone ? "done" : isCurrent ? "current" : "";
        const dot = isDone ? "&#10003;" : (i + 1);

        html += `<div class="connector ${isDone ? "done" : ""}"></div>`;
        html += `<div class="step ${stateClass}"><span class="dot">${dot}</span> ${labels[stage]}</div>`;
    });

    html += `</div>`;
    return html;
}

async function checkAuth() {
    const response = await fetch("../backend/api/auth/me.php");
    const data = await response.json();

    if (!data.success || data.user.role !== "employee") {
        window.location.href = "login.html";
        return;
    }

    document.getElementById("user-name").textContent = data.user.name;
}

function updateStats(documents) {
    const total = documents.length;
    const pending = documents.filter(d => d.current_status.startsWith("pending_")).length;
    const approved = documents.filter(d => d.current_status === "approved").length;
    const rejected = documents.filter(d => d.current_status === "rejected").length;

    document.getElementById("stat-total").textContent = total;
    document.getElementById("stat-pending").textContent = pending;
    document.getElementById("stat-approved").textContent = approved;
    document.getElementById("stat-rejected").textContent = rejected;
}

async function loadDocuments() {
    const response = await fetch("../backend/api/documents/list.php");
    const data = await response.json();

    const container = document.getElementById("documents-body");
    container.innerHTML = "";

    if (!data.success || data.documents.length === 0) {
        container.innerHTML = `<p class="empty-state">No documents submitted yet.</p>`;
        updateStats([]);
        return;
    }

    updateStats(data.documents);

    data.documents.forEach(doc => {
        const card = document.createElement("div");
        card.className = "document-card";
        card.innerHTML = `
            <div class="doc-header">
                <div class="doc-title">${doc.title}</div>
                <span class="badge badge-${doc.current_status}">${formatStatus(doc.current_status)}</span>
            </div>
            <div class="doc-meta">Submitted ${new Date(doc.created_at).toLocaleDateString()}</div>
            ${buildStepper(doc.current_status)}
            <div class="doc-actions">
                <button class="btn-view" onclick="viewHistory(${doc.id})">View History</button>
            </div>
        `;
        container.appendChild(card);
    });
}

async function viewHistory(documentId) {
    const response = await fetch(`../backend/api/documents/get.php?id=${documentId}`);
    const data = await response.json();

    if (!data.success) {
        alert(data.message);
        return;
    }

    const doc = data.document;
    const modalBody = document.getElementById("modal-body");

    const historyHtml = data.history.length === 0
        ? `<p class="empty-state">No action has been taken yet.</p>`
        : `<div class="timeline">` + data.history.map(h => {
            const date = new Date(h.action_date).toLocaleDateString();
            const comment = h.comment ? ` - "${h.comment}"` : "";
            return `
                <div class="timeline-item">
                    <div class="timeline-dot">&#10003;</div>
                    <div>
                        <div class="timeline-title">${h.role_at_time} ${h.action} by ${h.full_name}</div>
                        <div class="timeline-meta">${date}${comment}</div>
                    </div>
                </div>
            `;
        }).join("") + `</div>`;

    modalBody.innerHTML = `
        <h3>${doc.title}</h3>
        <p style="color:var(--color-text-muted); font-size:13px; margin-bottom:10px;">${doc.description || "(no description)"}</p>
        <span class="badge badge-${doc.current_status}">${formatStatus(doc.current_status)}</span>
        ${doc.file_path
            ? `<p style="margin-top:12px;"><a href="../backend/api/documents/download.php?id=${doc.id}" target="_blank">Download attachment</a></p>`
            : ""}
        <h4>Workflow History</h4>
        ${historyHtml}
    `;

    document.getElementById("history-modal").style.display = "flex";
}

document.getElementById("modal-close").addEventListener("click", function () {
    document.getElementById("history-modal").style.display = "none";
});

const submitForm = document.getElementById("submit-form");
const successBox = document.getElementById("success-message");
const errorBox = document.getElementById("error-message");

submitForm.addEventListener("submit", async function (event) {
    event.preventDefault();
    successBox.style.display = "none";
    errorBox.style.display = "none";

    // FormData packages the text fields + file together as multipart/form-data.
    // Don't set a Content-Type header manually - the browser sets the correct boundary.
    const formData = new FormData(submitForm);

    try {
        const response = await fetch("../backend/api/documents/create.php", {
            method: "POST",
            body: formData
        });

        const data = await response.json();

        if (!data.success) {
            errorBox.textContent = data.message;
            errorBox.style.display = "block";
            return;
        }

        successBox.textContent = "Document submitted successfully.";
        successBox.style.display = "block";
        submitForm.reset();
        loadDocuments();

    } catch (err) {
        errorBox.textContent = "Could not reach the server.";
        errorBox.style.display = "block";
    }
});

document.getElementById("logout-btn").addEventListener("click", async function () {
    await fetch("../backend/api/auth/logout.php", { method: "POST" });
    window.location.href = "login.html";
});

// Mobile sidebar toggle - purely visual, no effect on data or auth
document.getElementById("menu-toggle").addEventListener("click", function () {
    document.getElementById("sidebar").classList.toggle("open");
});

checkAuth();
loadDocuments();
