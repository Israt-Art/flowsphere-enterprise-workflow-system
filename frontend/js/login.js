// Handles the login form: sends email+password to login.php,
// then redirects based on the role that comes back.

const loginForm = document.getElementById("login-form");
const errorBox = document.getElementById("error-message");

const roleToPage = {
    employee: "employee-dashboard.html",
    manager: "manager-dashboard.html",
    hr: "hr-dashboard.html",
    director: "director-dashboard.html"
};

loginForm.addEventListener("submit", async function (event) {
    event.preventDefault();
    errorBox.style.display = "none";

    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value;

    try {
        const response = await fetch("../backend/api/auth/login.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ email, password })
        });

        const data = await response.json();

        if (!data.success) {
            errorBox.textContent = data.message;
            errorBox.style.display = "block";
            return;
        }

        window.location.href = roleToPage[data.user.role];

    } catch (err) {
        errorBox.textContent = "Could not reach the server. Is PHP/MySQL running?";
        errorBox.style.display = "block";
    }
});
