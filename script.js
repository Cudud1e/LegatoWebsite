const menuButton = document.getElementById("menuButton");
const navLinks = document.getElementById("navLinks");
const checkboxes = document.querySelectorAll(".service-item input");
const estimatedCost = document.getElementById("estimatedCost");
const inquiryForm = document.querySelector(".contact-form");

if (inquiryForm) {
  inquiryForm.action = "process_inquiry.php";
}

async function syncAuthNavigation() {
  const navActions = document.querySelector(".nav-actions");
  if (!navActions) return;

  try {
    const response = await fetch("auth_status.php", { credentials: "same-origin" });
    const authState = await response.json();
    const authLinks = navActions.querySelectorAll(".nav-login, .auth-nav");
    authLinks.forEach((link) => link.remove());

    const authNav = document.createElement("div");
    authNav.className = "auth-nav";
    authNav.innerHTML = authState.loggedIn
      ? '<a href="profile.php" class="nav-login">My Account</a><a href="logout.php" class="nav-login">Log Out</a>'
      : '<a href="login.php" class="nav-login">Log In</a>';
    const menuButton = navActions.querySelector(".menu-button");
    navActions.insertBefore(authNav, menuButton);
  } catch (error) {
    // Keep the server-rendered navigation if the status request fails.
  }
}

syncAuthNavigation();

document.querySelectorAll(".nav-book, .package-button").forEach((bookingLink) => {
  if (bookingLink.getAttribute("href") === "booking.php" || bookingLink.getAttribute("href")?.startsWith("booking.php?")) {
    return;
  }

  bookingLink.addEventListener("click", (event) => {
    event.preventDefault();
    const packageName = bookingLink.textContent.includes("VIP 1")
      ? "VIP1"
      : bookingLink.textContent.includes("VIP 2")
        ? "VIP2"
        : bookingLink.textContent.includes("VIP 3")
          ? "VIP3"
          : "Custom Build";
    window.location.href = `booking.php?package=${encodeURIComponent(packageName)}`;
  });
});

if (menuButton && navLinks) {
  menuButton.addEventListener("click", () => {
    navLinks.classList.toggle("open");

    menuButton.textContent = navLinks.classList.contains("open") ? "✕" : "☰";
  });

  navLinks.querySelectorAll("a").forEach((link) => {
    link.addEventListener("click", () => {
      navLinks.classList.remove("open");
      menuButton.textContent = "☰";
    });
  });
}

function updateEstimatedCost() {
  let total = 0;

  checkboxes.forEach((checkbox) => {
    if (checkbox.checked) {
      total += Number(checkbox.dataset.price);
    }
  });

  if (estimatedCost) {
    estimatedCost.textContent = `₱${total.toLocaleString("en-PH")}`;
  }
}

checkboxes.forEach((checkbox) => {
  checkbox.addEventListener("change", updateEstimatedCost);
});

updateEstimatedCost();