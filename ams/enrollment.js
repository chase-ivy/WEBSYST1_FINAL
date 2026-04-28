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

function disabilityField() {
  const selected = document.querySelector('input[name="disability"]:checked');
  const card = document.getElementById("disabilityDetails");

  if (!selected) return;

  card.style.display = selected.value === "Yes" ? "block" : "none";
}

function returningField() {
  const selected = document.querySelector('input[name="returning"]:checked');
  const card = document.getElementById("returningDetails");

  if (!selected) return;

  card.style.display = selected.value === "Yes" ? "block" : "none";
}
