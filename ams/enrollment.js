function ipField() {
  const selected = document.querySelector('input[name="ip"]:checked');
  const card = document.getElementById("fieldDetails");

  if (!selected) return;

  if (selected.value === "Yes") {
    card.style.display = "block";
  } else {
    card.style.display = "none";
  }
}
