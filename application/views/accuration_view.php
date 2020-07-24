<!doctype html>
<html lang="en">

<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="assets/css/bootstrap.min.css">

	<title>Akurasi Translasi</title>
</head>

<body>
	<br>
	<div class="container">
		<div class="card border-primary">
			<h5 class="card-header text-center">Pengujian Akurasi</h5>
			<div class="card-body">
				<div class="accordion">
					<div class="card">
						<div class="card-header">
							<h5 class="mb-0">
								<button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapse_0var1state">
									Lihat Hasil Pengujian
								</button>
							</h5>
						</div>
						<div id="collapse_0var1state" class="collapse">
							<!-- <div id="collapse_pasrsing" class="collapse"> -->
							<div class="card-body">
								<div class="row">
									<table class="table">
										<thead>
											<th width='40%'>Masukan</th>
											<th>Harapan</th>
											<th>Hasil</th>
											<th>Status</th>
										</thead>
										<?php foreach ($result as $key => $res) : ?>
											<tr>
												<td><?= $res['kalimat'] ?></td>
												<td><?= $res['harapan'] ?></td>
												<td><?= $res['hasil'] ?></td>
												<td><?= $res['status']."<br>(".round($res['time'],2)." Detik)" ?></td>
											</tr>
										<?php endforeach; ?>
									</table>
									<hr>
								</div>
							</div>
						</div>
					</div>
				</div>
				<br>
			</div>
		</div>
		<br>

	</div>
	<center>
		<h6>Waktu Eksekusi = <?= round($hasil_uji['time'])+1 ." Detik" ?> </h6>
		<h6>Total data uji benar = <?= $hasil_uji['benar'] ?></h6>
		<h6>Totoal data uji salah = <?= $hasil_uji['salah'] ?> </h6>
		<h6>Jumlah seluruh data uji = <?= $hasil_uji['total'] ?></h6>
		<h6>Nilai akurasi keseluruhan = <?= $hasil_uji['akurasi'] . "%" ?> </h6>
	</center>
	<script src="assets/js/jquery-3.3.1.slim.min.js"></script>
	<script src="assets/js/popper.min.js"></script>
	<script src="assets/js/bootstrap.min.js"></script>
</body>

</html>
