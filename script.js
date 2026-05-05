const themeToggleButton = document.getElementById("themeToggle");

const savedTheme = localStorage.getItem("theme");

if (savedTheme === "dark") {
  document.body.classList.add("dark-mode");
  themeToggleButton.textContent = "Light Mode";
}

themeToggleButton.addEventListener("click", () => {
  document.body.classList.toggle("dark-mode");

  const darkModeIsActive = document.body.classList.contains("dark-mode");

  if (darkModeIsActive) {
    localStorage.setItem("theme", "dark");
    themeToggleButton.textContent = "Light Mode";
  } else {
    localStorage.setItem("theme", "light");
    themeToggleButton.textContent = "Dark Mode";
  }
});