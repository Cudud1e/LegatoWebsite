const menuButton = document.getElementById("menuButton");
const navLinks = document.getElementById("navLinks");
const checkboxes = document.querySelectorAll(".service-item input");
const estimatedCost = document.getElementById("estimatedCost");

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

function updateEstimatedCost() {
  let total = 0;

  checkboxes.forEach((checkbox) => {
    if (checkbox.checked) {
      total += Number(checkbox.dataset.price);
    }
  });

  estimatedCost.textContent = `₱${total.toLocaleString("en-PH")}`;
}

checkboxes.forEach((checkbox) => {
  checkbox.addEventListener("change", updateEstimatedCost);
});

updateEstimatedCost();