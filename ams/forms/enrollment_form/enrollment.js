// DONT CHANGE ANYTHING
function ipField() {
  const selected = document.querySelector('input[name="ip"]:checked');
  const card = document.getElementById("ipDetails");

  if (!selected) return;

  card.style.display = selected.value === "1" ? "block" : "none";
}

function fourPsField() {
  const selected = document.querySelector('input[name="fourps"]:checked');
  const card = document.getElementById("fourPsDetails");

  if (!selected) return;

  card.style.display = selected.value === "1" ? "block" : "none";
}

function disabilityField() {
  const selected = document.querySelector('input[name="disability"]:checked');
  const card = document.getElementById("disabilityDetails");

  if (!selected) return;

  card.style.display = selected.value === "1" ? "block" : "none";
}

function returningField() {
  const selected = document.querySelector('input[name="returning"]:checked');
  const card = document.getElementById("returningDetails");

  if (!selected) return;

  card.style.display = selected.value === "1" ? "block" : "none";
}
document.getElementById("birthDate").addEventListener("change", function() {
    const birthDate = new Date(this.value);
    const today = new Date();

    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();

    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }

    document.getElementById("age").value = age;
});