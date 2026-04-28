<?php
	
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="style.css">
	<title>Gibraltar AMS</title>
</head>
<body>
<header>
  <h2>Gibraltar - AMS</h2>
<!-- Can someone put a pic logo ng school -->
  <img src="logo.png" alt="Logo" class="logo">
</header>

<section>
	<div class="grid">
		

		<div class="card">
			<h2 class="h2">Gibraltar Elementary Management System</h2>
			
			
			<form>
				<div class="card">
					<h4 style="margin: 5px">Log In</h4>
					<nav class="nav-card">
						<a href="select.php" class="select">Change Form Access <</a><br><br>

						<p><strong>Teacher Module</strong></p>
					</nav>

					<span>Username:</span>
					<input type="number" name="lrn"><br>
					<br>
					<span>Password:</span>
					<div class="pass-card">
						<input type="password" name="password" id="pass">
						<input type="checkbox" class="toggle" onclick="pass.type = this.checked ? 'text' : 'password'">
					</div>

					<button class="log">Log In</button>


				</div>
			</form>

		</div>

	</div>
</section>

</body>
</html>