function ipField() {
  const selected = document.querySelector('input[name="ip"]:checked');
  const card = document.getElementById("ipDetails");

  if (!selected) return;

  card.style.display = selected.value === "Yes" ? "block" : "none";
}

function fourPsField() {
  const selected = document.querySelector('input[name="fourps"]:checked');
  const card = document.getElementById("fourPsDetails");

  if (!selected) return;

  card.style.display = selected.value === "Yes" ? "block" : "none";
}
