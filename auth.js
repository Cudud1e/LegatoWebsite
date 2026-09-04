const authForm = document.getElementById("authForm");
const switchAuth = document.getElementById("switchAuth");
const confirmField = document.querySelector(".confirm-field");
const authTitle = document.getElementById("authTitle");
const authDescription = document.getElementById("authDescription");
const authSubmit = document.getElementById("authSubmit");
const switchPrompt = document.getElementById("switchPrompt");
const formMessage = document.getElementById("formMessage");
let createAccountMode = false;

switchAuth.addEventListener("click", () => {
  createAccountMode = !createAccountMode;
  confirmField.hidden = !createAccountMode;
  authTitle.textContent = createAccountMode ? "Create your account." : "Welcome back.";
  authDescription.textContent = createAccountMode
    ? "Save your event inquiry details and stay connected with the LEGATO team."
    : "Log in to manage your event inquiry and booking details.";
  authSubmit.textContent = createAccountMode ? "Create Account" : "Log In";
  switchPrompt.textContent = createAccountMode ? "Already have an account?" : "New to LEGATO?";
  switchAuth.textContent = createAccountMode ? "Log in" : "Create an account";
  formMessage.textContent = "";
});

authForm.addEventListener("submit", (event) => {
  event.preventDefault();
  formMessage.textContent = createAccountMode
    ? "Your account details are ready. Our team will be in touch soon."
    : "Thanks for logging in. Your client portal is ready for your next booking.";
});
