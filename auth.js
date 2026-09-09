const authForm = document.getElementById("authForm");
const switchAuth = document.getElementById("switchAuth");
const confirmField = document.querySelector(".confirm-field");
const authTitle = document.getElementById("authTitle");
const authDescription = document.getElementById("authDescription");
const authSubmit = document.getElementById("authSubmit");
const switchPrompt = document.getElementById("switchPrompt");
const formMessage = document.getElementById("formMessage");
const authMode = document.getElementById("authMode");
const registrationFields = document.querySelectorAll(".registration-field");
let createAccountMode = false;

if (authMode) {
  createAccountMode = authMode.value === "register";
}

function updateRegistrationFields() {
  if (!confirmField || !authMode) return;
  confirmField.hidden = !createAccountMode;
  document.getElementById("confirmPassword").required = createAccountMode;
  registrationFields.forEach((field) => {
    field.hidden = !createAccountMode;
    field.querySelector("input").required = createAccountMode;
  });
}

updateRegistrationFields();

if (switchAuth && authForm && confirmField && authTitle && authDescription && authSubmit && switchPrompt && formMessage && authMode) {
switchAuth.addEventListener("click", () => {
  createAccountMode = !createAccountMode;
  authMode.value = createAccountMode ? "register" : "login";
  updateRegistrationFields();
  authTitle.textContent = createAccountMode ? "Create your account." : "Welcome back.";
  authDescription.textContent = createAccountMode
    ? "Save your event inquiry details and stay connected with the LEGATO team."
    : "Log in to manage your event inquiry and booking details.";
  authSubmit.textContent = createAccountMode ? "Create Account" : "Log In";
  switchPrompt.textContent = createAccountMode ? "Already have an account?" : "New to LEGATO?";
  switchAuth.textContent = createAccountMode ? "Log in" : "Create an account";
  formMessage.textContent = "";
});
}

