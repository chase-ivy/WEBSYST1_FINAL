<?php
include "config.php";

if(isset($_POST['submit'])){
    header("Location: teacher_dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Medical Form · Gibraltar AMES</title>

<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>

/* SAME DESIGN TOKENS */
:root {
    --brand: #4e0303;
    --brand-dark: #ec3f3f;
    --brand-light: #e8f0f7;
    --border: #d1d5db;
    --text: #000000;
    --muted: #6b7280;
    --surface: #ffffff;
    --canvas: #f5f7fa;
    --radius-md: 10px;
    --radius-xl: 20px;
}

/* RESET */
*{box-sizing:border-box;margin:0;padding:0}
body{
    font-family:'DM Sans',sans-serif;
    background:var(--canvas);
}

/* TOPBAR */
.topbar{
    background:#4e0303;
    padding:12px 32px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}
.topbar span{color:#fff;font-weight:700}
.topbar a{
    color:#fff;
    font-size:12px;
    border:1px solid rgba(255,255,255,.3);
    padding:6px 12px;
    border-radius:6px;
}

/* STEPPER */
.stepper{
    display:flex;
    justify-content:center;
    padding:25px;
}
.step{
    text-align:center;
    flex:1;
}
.step-dot{
    width:30px;height:30px;
    border-radius:50%;
    border:2px solid var(--border);
    display:flex;
    align-items:center;
    justify-content:center;
    margin:auto;
}
.step.active .step-dot{
    background:var(--brand);
    color:#fff;
}

/* CARD */
.wrap{max-width:700px;margin:20px auto;padding:0 20px}
.card{
    background:#fff;
    border-radius:var(--radius-xl);
    border:1px solid var(--border);
    overflow:hidden;
}
.card-head{
    padding:20px;
    background:#f0f5ff;
}
.card-body{padding:25px}
.card-foot{
    padding:15px;
    display:flex;
    justify-content:space-between;
}

/* FORM */
.field{
    display:flex;
    flex-direction:column;
    margin-bottom:15px;
}
.field label{
    font-size:12px;
    color:var(--muted);
    margin-bottom:5px;
}
.field input,
.field select{
    padding:10px;
    border:1px solid var(--border);
    border-radius:6px;
}

/* BUTTONS */
.btn{
    padding:10px 20px;
    border:none;
    border-radius:6px;
    cursor:pointer;
}
.btn-primary{
    background:var(--brand);
    color:#fff;
}

/* COLLAPSE */
.collapse{display:none}
.collapse.show{display:block}


        /* ── PAGE FOOTER ─────────────────────────────────────────── */
footer {
background: #4e0303;
color: rgba(255,255,255,.5);
text-align: center;
padding: 18px;
font-size: 12px;
border-top: 3px solid var(--brand);
margin-top: auto;
        }

footer strong { color: rgba(255,255,255,.8); }

        /* ── RESPONSIVE ──────────────────────────────────────────── */
@media (max-width: 580px) {
.grid-2, .grid-3 { grid-template-columns: 1fr; }
.span-2 { grid-column: span 1; }
.card-body,
.card-head,
.card-foot{ padding: 20px; }
.topbar{ padding: 10px 16px; }
        }

</style>
</head>

<body>

<!-- TOP -->
<div class="topbar">
    <span>Gibraltar Elementary School</span>
</div>

<!-- STEPPER -->
<div class="stepper">
    <div class="step active">
        <div class="step-dot">5</div>
        <div style="font-size:12px;">Medical</div>
    </div>
</div>

<div class="wrap">
<form method="POST">

<div class="card">

<div class="card-head">
    <h2>Medical Information</h2>
    <p>Health-related details of the learner</p>
</div>

<div class="card-body">

<!-- Parent Info -->
<div class="field">
    <label>Parent / Guardian Name</label>
    <input type="text" name="parent_name">
</div>

<div class="field">
    <label>Contact Number</label>
    <input type="text" name="contact">
</div>

<hr><br>

<!-- Q1 -->
<div class="field">
<label>1. Allergies?</label>
<select onchange="toggle('q1', this.value)">
<option hidden>Select</option>
<option>Yes</option>
<option>No</option>
</select>
</div>

<div id="q1" class="collapse">
<input type="text" placeholder="Specify allergies">
</div>

<!-- Q2 -->
<div class="field">
<label>2. Ongoing medical condition?</label>
<select onchange="toggle('q2', this.value)">
<option hidden>Select</option>
<option>Yes</option>
<option>No</option>
</select>
</div>

<div id="q2" class="collapse">
<input type="text" placeholder="Specify condition">
</div>

<!-- Q3 -->
<div class="field">
<label>3. Surgery / hospitalization?</label>
<select onchange="toggle('q3', this.value)">
<option hidden>Select</option>
<option>Yes</option>
<option>No</option>
</select>
</div>

<div id="q3" class="collapse">
<input type="text" placeholder="Details">
</div>

<!-- Q4 -->
<div class="field">
<label>4. Taking medicines?</label>
<select onchange="toggle('q4', this.value)">
<option hidden>Select</option>
<option>Yes</option>
<option>No</option>
</select>
</div>

<div id="q4" class="collapse">
<input type="text" placeholder="List medicines">
</div>

<!-- Q5 -->
<div class="field">
<label>5. Family medical history?</label>
<select onchange="toggle('q5', this.value)">
<option hidden>Select</option>
<option>Yes</option>
<option>No</option>
</select>
</div>

<div id="q5" class="collapse">
<input type="text" placeholder="Specify history">
</div>

<!-- Q6 -->
<div class="field">
<label>6. Exposure to smoke?</label>
<select>
<option hidden>Select</option>
<option>Yes</option>
<option>No</option>
</select>
</div>

<!-- Q7 -->
<div class="field">
<label>7. Other information</label>
<input type="text" placeholder="Optional">
</div>

</div>

<div class="card-foot">
    <a href="enrollment.php" class="btn">← Back</a>
    <button type="submit" name="submit" class="btn btn-primary">Submit</button>
</div>

</div>

</form>
</div>

    <footer>
        &copy; 2025 <strong>Gibraltar Elementary School — AMES</strong>. All rights reserved.
    </footer>

<script>
function toggle(id, val){
    document.getElementById(id).classList.toggle('show', val === 'Yes');
}
</script>

</body>
</html>