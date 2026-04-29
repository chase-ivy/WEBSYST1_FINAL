function ipField() {
  const selected = document.querySelector('input[name="indigenous_group"]:checked');
  const card = document.getElementById("ipDetails");

  if (!selected) return;

  card.style.display = selected.value === "Yes" ? "block" : "none";
}

function fourPsField() {
  const selected = document.querySelector('input[name="4p_benificiary"]:checked');
  const card = document.getElementById("fourPsDetails");

  if (!selected) return;

  card.style.display = selected.value === "Yes" ? "block" : "none";
}

function disabilityField() {
  const selected = document.querySelector('input[name="is_learner_with_disability"]:checked');
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
