<!doctype html>
<html lang="en">

<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="<?= base_url() ?>assets/css/bootstrap.min.css">

	<title>Hello, world!</title>
</head>

<body>
	<br>
	<div class="container">
		<div class="card border-primary">
			<h5 class="card-header text-center">Grammar Bahasa Indonesia</h5>
			<div class="card-body">
				<?php foreach ($grammar_nl as $key => $value) : ?>
					<?= $value->parent . " ::=";  ?>
					<?= $value->child . "<br>";  ?>
				<?php endforeach ?>
			</div>
		</div>

		<br>

		<div class="card border-primary">
			<h5 class="card-header text-center">Grammar Bahasa Pascal</h5>
			<div class="card-body">
				<?php foreach ($grammar_pascal as $key => $value) : ?>
					<?= $value->parent . " ::=";  ?>
					<?= $value->child . "<br>";  ?>
				<?php endforeach ?>
			</div>
		</div>

		<br>

	</div>
	<script src="<?= base_url() ?>assets/js/jquery-3.3.1.slim.min.js"></script>
	<script src="<?= base_url() ?>assets/js/popper.min.js"></script>
	<script src="<?= base_url() ?>assets/js/bootstrap.min.js"></script>
</body>

</html>
