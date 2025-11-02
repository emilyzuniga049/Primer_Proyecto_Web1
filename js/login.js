document.addEventListener('DOMContentLoaded', function() {
  const params = new URLSearchParams(window.location.search);
  const msg = params.get("error");
  const box = document.getElementById("login-message");

  if (msg && box) {
    box.textContent = msg;
    box.style.color = "red";
    box.style.marginTop = "10px";
    box.style.textAlign = "center";
  }
});