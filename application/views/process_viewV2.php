<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="<?php echo base_url() ?>assets/css/bootstrap.min.css">

        <title>NL to Source Code</title>
    </head>
    <body>
        <div class="container">
			<h3 class="text-center"></h3>

			<!-- begin : input text -->
            <div class="card border-primary">
                <h5 class="card-header text-center">Masukkan</h5>
                <div class="card-body">
                    <h5 class="card-title">Teks Masukan</h5>
                    <form class="" action="<?php echo base_url().'process2' ?>" method="post" enctype="multipart/form-data">
                        <div class="form-group">
							<table border="0" width="100%">
                                <tr>
									<td width="90%">
										<textarea rows="8" class="form-control" name="input" placeholder="Algoritma Deskriptif"><?php if(isset($input)){echo "$input";}?></textarea>
										<br>
										<div class="row justify-content-between">
											<div class="col-10">
												<input type="file" class="form-control-file" name="file_algoritma">
											</div>
											<div class="col-2">
												<button type="submit" class="btn btn-primary btn-block" name="proses">Proses</button>
											</div>
										</div>
									</td>
									<td valign="top">
										<div class="col-10">
											<a href="lihat-grammar.php" class="btn btn-outline-secondary">Grammar</a>
											<br><br>
											<a href="lihat-kelas-token.php" class="btn btn-outline-secondary">Kelas Token</a>
											<br><br>
											<a href="<?= base_url('accuration') ?>" class="btn btn-outline-secondary">Uji Akurasi</a>
										</div>
									</td>
                                </tr>
                                </table>
                        </div>
                        <hr>
                    </form>
                </div>
			</div>
			<!-- end : input text -->

			<!-- begin : Hasil Proses NL to Source code -->
            <?php if (isset($input) AND $input != ""): ?>
                    <?php if (isset($input)): ?>
						<hr><hr>
						<!-- begin : text masukan -->
                        <div class="card border-primary">
                            <h5 class="card-header text-center">Teks Masukan</h5>
                            <div class="card-body">
                                <?php echo $input ?>
                            </div>
						</div>
						<!-- end : text masukan -->

						<!-- begin : Proses -->
                        <br>
                        <div class="card border-primary">
                            <h5 class="card-header text-center">Proses</h5>
                            <div class="card-body">
                                <!-- begin : Preprocessing -->
                                <div class="card border-primary">
                                    <h5 class="card-header">Preprocessing</h5>
                                    <div class="card-body">
                                        <!-- begin : CASE FOLDING -->
                                        <div class="accordion">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5 class="mb-0">
                                                        <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapse_casefolding">
                                                            Case Folding
                                                        </button>
                                                    </h5>
                                                </div>
                                                <div id="collapse_casefolding" class="collapse">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-sm-6">
                                                                <div class="card">
                                                                    <div class="card-body">
                                                                        <h5 class="card-title">Sebelum</h5>
                                                                        <?= $input; ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <div class="card">
                                                                    <div class="card-body">
                                                                        <h5 class="card-title">Sesudah</h5>
																		<?php
																			if (isset($casefolding) AND $casefolding != NULL) {
																				echo $casefolding;
																			}
																		?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
										</div>
										<!-- end : CASE FOLDING -->

                                        <!-- begin : FILTERING -->
                                        <div class="accordion">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5 class="mb-0">
                                                        <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapse_filtering">
                                                            Filtering
                                                        </button>
                                                    </h5>
                                                </div>
                                                <div id="collapse_filtering" class="collapse">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-sm-6">
                                                                <div class="card">
                                                                    <div class="card-body">
                                                                        <h5 class="card-title">Sebelum</h5>
                                                                        <?php
																			if (isset($casefolding) AND $casefolding != NULL) {
																				echo $casefolding;
																			}
																		?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <div class="card">
                                                                    <div class="card-body">
                                                                        <h5 class="card-title">Sesudah</h5>
                                                                        <?php
																			if (isset($filtering) AND $filtering != NULL) {
																				echo $filtering;
																			}
																		?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
										</div>
										<!-- end : FILTERING -->
                                    </div>
								</div>
								<!-- end : Preprocessing -->

                                <br>
                                <!-- begin : Analisis -->
                                <div class="card border-primary">
                                    <h5 class="card-header">Analisis</h5>
                                    <div class="card-body">
                                        <!-- SCANNING -->
                                        <div class="accordion">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5 class="mb-0">
                                                        <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapse_scanning">
                                                            Scanning
                                                        </button>
                                                    </h5>
                                                </div>
                                                <div id="collapse_scanning" class="collapse">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-sm-6">
                                                                <div class="card">
                                                                    <div class="card-body">
                                                                        <h5 class="card-title">Sebelum</h5>
                                                                        <?php
																			if (isset($filtering) AND $filtering != NULL) {
																				echo $filtering;
																			}
																		?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <div class="card">
                                                                    <div class="card-body">
																		<h5 class="card-title">Sesudah</h5>
                                                                        <table class="table">
                                                                            <thead>
                                                                                <th>Index</th>
                                                                                <th>Token</th>
                                                                                <th>Kelas</th>
																			</thead>
																			<tbody>
																			<?php if(isset($scanning) AND $scanning != NULL) : ?>
																				<?php $num = 1; ?>
																				<?php foreach($scanning as $key=>$value): ?>
																				<tr>
																					<td><?= $num ?></td>
																					<td><?= $value['token'] ?></td>
																					<td><?= $value['class'] ?></td>
																				</tr>
																				<?php $num++; ?>
																				<?php endforeach; ?>
																			<?php endif; ?>
																			</tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- begin : PARSING -->
                                        <div class="accordion">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5 class="mb-0">
                                                        <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapse_pasrsing">
                                                            Parsing
                                                        </button>
                                                    </h5>
                                                </div>
                                                <div id="collapse_pasrsing" class="collapse">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-sm-12">
                                                                <div class="card">
                                                                    <div class="card-body">
																		<h5 class="card-title">Penurunan String</h5>
																		<?php if($parsing['diterima']==False) : ?>
																			<font color="red">
																				<?= $parsing['message'] ?>
																			</font>
																		<?php else : ?>
																			<?php foreach($parsing['result'] as $key=>$value): ?>
																				<?= "&rarr; ".$value."<br>"; ?>
																				<?php $num++; ?>
																			<?php endforeach; ?>
																		<?php endif; ?>

                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
										</div>
										<!-- end : PARSING -->
                                    </div>
								</div>
								<!-- end : Analisis -->
								<br>

                                <!-- begin : Translasi -->
                                <?php if ($parsing['diterima'] != TRUE): ?>
									<script language='javascript'>alert('Parsing Ditolak!');</script>
									<script language='javascript'>alert(<?php echo $parsing['error_message'] ?>);</script>
                                <?php else: ?>
                                    <div class="card border-primary">
                                        <h5 class="card-header">Translasi</h5>
                                        <div class="card-body">
                                            <!-- Pembangkitan Kode -->
                                            <div class="accordion">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5 class="mb-0">
                                                            <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapse_generation">
                                                                Pembangkitan Kode
                                                            </button>
                                                        </h5>
                                                    </div>
                                                    <div id="collapse_generation" class="collapse">
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <!-- Penghapusan Additional Token -->
                                                                <div class="col-sm-12">
                                                                    <div class="card">
                                                                        <div class="card-header">
                                                                            <h5 class="mb-0">
                                                                                <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapse_delete_additional_token">
                                                                                    Penghapusan Additional Token
                                                                                </button>
                                                                            </h5>
                                                                        </div>
                                                                        <div id="collapse_delete_additional_token" class="collapse">
                                                                            <div class="card-body">
                                                                                <div class="row">
                                                                                    <div class="col-sm-6">
                                                                                        <div class="card">
                                                                                            <div class="card-body">
                                                                                                <h5 class="card-title">Sebelum</h5>
                                                                                                <table class="table">
                                                                                                    <thead>
                                                                                                        <th>Index</th>
                                                                                                        <th>Token</th>
																									</thead>
																									<?php $num = 1; ?>
                                                                                                    <?php foreach ($scanning as $key => $value) { ?>
                                                                                                        <tr>
                                                                                                            <td><?= $num ?></td>
                                                                                                            <td><?= $value['token'] ?></td>
                                                                                                            <td><?= $value['class'] ?></td>
																										</tr>
                                                                                                    <?php } ?>
                                                                                                </table>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-sm-6">
                                                                                        <div class="card">
                                                                                            <div class="card-body">
                                                                                                <h5 class="card-title">Sesudah</h5>
                                                                                                <?php //$clean_token = removeAdditionalToken($scanning) ?>
                                                                                                <table class="table">
                                                                                                    <thead>
                                                                                                        <th>Index</th>
                                                                                                        <th>Token</th>
                                                                                                    </thead>
                                                                                                    <?php $num = 1; ?>
                                                                                                    <?php foreach ($cleanToken as $key => $value) { ?>
                                                                                                        <tr>
                                                                                                            <td><?= $num ?></td>
                                                                                                            <td><?= $value['token'] ?></td>
                                                                                                            <td><?= $value['class'] ?></td>
																										</tr>
																										<?php $num++; ?>
                                                                                                    <?php } ?>
                                                                                                </table>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <!-- Pengubahan Token -->
                                                                <div class="col-sm-12">
                                                                    <div class="card">
                                                                        <div class="card-header">
                                                                            <h5 class="mb-0">
                                                                                <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapse_change_token">
                                                                                    Pengubahan Token
                                                                                </button>
                                                                            </h5>
                                                                        </div>
                                                                        <div id="collapse_change_token" class="collapse">
                                                                            <div class="card-body">
                                                                                <div class="row">
                                                                                    <div class="col-sm-6">
                                                                                        <div class="card">
                                                                                            <div class="card-body">
                                                                                                <h5 class="card-title">Sebelum</h5>
                                                                                                <table class="table">
                                                                                                    <thead>
                                                                                                        <th>Index</th>
                                                                                                        <th>Token</th>
                                                                                                    </thead>
                                                                                                    <?php $num = 1; ?>
                                                                                                    <?php foreach ($cleanToken as $key => $value) { ?>
                                                                                                        <tr>
                                                                                                            <td><?= $num ?></td>
                                                                                                            <td><?= $value['token'] ?></td>
                                                                                                            <td><?= $value['class'] ?></td>
																										</tr>
																										<?php $num++; ?>
                                                                                                    <?php } ?>
                                                                                                </table>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-sm-6">
                                                                                        <div class="card">
                                                                                            <div class="card-body">
                                                                                                <h5 class="card-title">Sesudah</h5>
                                                                                                <?php //$change_token = changeToken($clean_token) ?>
                                                                                                <table class="table">
                                                                                                    <thead>
                                                                                                        <th>Index</th>
                                                                                                        <th>Token</th>
                                                                                                    </thead>
                                                                                                    <?php $num = 1; ?>
                                                                                                    <?php foreach ($changeToken as $key => $value) { ?>
                                                                                                        <tr>
                                                                                                            <td><?= $num ?></td>
                                                                                                            <td><?= $value['token'] ?></td>
                                                                                                            <td><?= $value['class'] ?></td>
																										</tr>
																										<?php $num++; ?>
                                                                                                    <?php } ?>
                                                                                                </table>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <!-- Pemetaan Posisi Token -->
                                                                <div class="col-sm-12">
                                                                    <div class="card">
                                                                        <div class="card-header">
                                                                            <h5 class="mb-0">
                                                                                <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapse_short_token">
                                                                                    Pemetaan Posisi Token
                                                                                </button>
                                                                            </h5>
                                                                        </div>
                                                                        <div id="collapse_short_token" class="collapse">
                                                                            <div class="card-body">
                                                                                <div class="row">
                                                                                    <div class="col-sm-6">
                                                                                        <div class="card">
                                                                                            <div class="card-body">
                                                                                                <h5 class="card-title">Sebelum</h5>
                                                                                                <table class="table">
                                                                                                    <thead>
                                                                                                        <th>Index</th>
                                                                                                        <th>Token</th>
                                                                                                    </thead>
                                                                                                    <?php $num = 1; ?>
                                                                                                    <?php foreach ($changeToken as $key => $value) { ?>
                                                                                                        <tr>
                                                                                                            <td><?= $num ?></td>
                                                                                                            <td><?= $value['token'] ?></td>
                                                                                                            <td><?= $value['class'] ?></td>
																										</tr>
																										<?php $num++; ?>
                                                                                                    <?php } ?>
                                                                                                </table>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-sm-6">
                                                                                        <div class="card">
                                                                                            <div class="card-body">
                                                                                                <h5 class="card-title">Sesudah</h5>
                                                                                                <table class="table">
                                                                                                    <thead>
                                                                                                        <th>Index</th>
                                                                                                        <th>Token</th>
                                                                                                    </thead>
                                                                                                    <?php $num = 1; ?>
                                                                                                    <?php foreach ($shortToken as $key => $value) { ?>
                                                                                                        <tr>
                                                                                                            <td><?= $num ?></td>
                                                                                                            <td><?= $value['token'] ?></td>
                                                                                                            <td><?= $value['class'] ?></td>
																										</tr>
																										<?php $num++; ?>
                                                                                                    <?php } ?>
                                                                                                </table>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <!-- Penyesuaian Sintaksis Dalam Bahasa Pascal -->
                                                                <div class="col-sm-12">
                                                                    <div class="card">
                                                                        <div class="card-header">
                                                                            <h5 class="mb-0">
                                                                                <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapse_code_insert">
                                                                                    Penyesuaian Sintaksis Dalam Bahasa Pascal
                                                                                </button>
                                                                            </h5>
                                                                        </div>
                                                                        <div id="collapse_code_insert" class="collapse">
                                                                            <div class="card-body">
                                                                                <div class="row">
                                                                                    <div class="col-sm-6">
                                                                                        <div class="card">
                                                                                            <div class="card-body">
                                                                                                <h5 class="card-title">Sebelum</h5>
                                                                                                <table class="table">
                                                                                                    <thead>
                                                                                                        <th>Index</th>
                                                                                                        <th>Token</th>
                                                                                                    </thead>
                                                                                                    <?php $num = 1; ?>
                                                                                                    <?php foreach ($shortToken as $key => $value) { ?>
                                                                                                        <tr>
                                                                                                            <td><?= $num ?></td>
                                                                                                            <td><?= $value['token'] ?></td>
                                                                                                            <td><?= $value['class'] ?></td>
																										</tr>
																										<?php $num++; ?>
                                                                                                    <?php } ?>
                                                                                                </table>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-sm-6">
                                                                                        <div class="card">
                                                                                            <div class="card-body">
																								<h5 class="card-title">Sesudah</h5>
																								<?php if($codeInsertion['diterima'] == False) : ?>
																									<font color="red">
																										<!-- Penyesuaian Bahasa Pascal Ditolak, Harap Periksa Kembali Grammar Pascalnya -->
                                                    <?= $codeInsertion['message']  ?>
																									</font>
																									<?php else : ?>
																										<table class="table">
																											<thead>
																												<th>Index</th>
																												<th>Token</th>
																											</thead>
																											<?php $num = 1; ?>
																											<?php foreach ($codeInsertion['result'] as $key => $value) { ?>
																												<tr>
																													<td><?= $num ?></td>
																													<td><?= $value ?></td>
																												</tr>
																												<?php $num++; ?>
																											<?php } ?>
																										</table>
																									<?php endif; ?>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <!-- Perapihan Kode -->
                                                                <div class="col-sm-12">
                                                                    <div class="card">
                                                                        <div class="card-header">
                                                                            <h5 class="mb-0">
                                                                                <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapse_beutify_code">
                                                                                    Perapihan Kode
                                                                                </button>
                                                                            </h5>
                                                                        </div>
                                                                        <div id="collapse_beutify_code" class="collapse">
                                                                            <div class="card-body">
                                                                                <div class="row">
																					<?php if($codeInsertion['diterima'] == True) : ?>
																						<?php echo $tdying; ?>
																					<?php else : ?>
																						<font color="red">
																							Penyesuaian Bahasa Pascal Ditolak, Harap Periksa Kembali Grammar Pascalnya
																						</font>
																					<?php endif; ?>

                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
					 </div>
					 <!-- end : Proses -->
                                            </div>

                                        </div>
                                    </div>
								<?php endif; ?>
								<!-- end : Translasi -->


                                <br>

                            </div>
                        </div>
                        <br>
                        <div class="card border-primary">
                            <h5 class="card-header text-center">Keluaran</h5>
                            <div class="card-body">
                                <h5 class="card-title">Hasil</h5>
                                <?php if ($parsing['diterima'] == True AND $codeInsertion['diterima'] == True): ?>
                                    <?php echo $tdying; ?>
                                    <?php

                                    // // $text = "Hello <br /> Hello again <br> Hello again again <br/> Goodbye <BR>";
                                    // $txt = $source_code;
                                    // $breaks = array("<br />","<br>   ","<br/>","<br> ");
                                    // $txt = str_ireplace($breaks, "\r\n", $txt);
                                    // $tabs = array("&nbsp; &nbsp;  ");
                                    // $txt = str_ireplace($tabs, "\t", $txt);

                                    // $myfile = fopen("translation_result/newfile.pas", "w") or die("Unable to open file!");
                                    // fwrite($myfile, $txt);
                                    // fclose($myfile);

                                    // ?>
                                    <!-- <hr> -->
                                    <!-- <a href="#" onClick="MyWindow=window.open('translation_result/open_devpascal.php','MyWindow',width=600,height=300); return false;">Click Here</a> -->
                                    <!-- <a href="#" onclick="window.open('translation_result/open_devpascal.php');return false">open b.php</a> -->
                                    <a href="translation_result/open_devpascal.php" target="_blank" class="btn btn-outline-success btn-block">Buka Hasil</a>
									<?php else : ?>
										<font color="red">
											Penerjemahan Bahasa Alami ke Source Code Dalam Bahasa Pascal Ditolak, Cek Kembali Grammar NL dan atau Grammar Pascal
										</font>
									<?php endif; ?>


                            </div>
                        </div>
                    <?php endif; ?>
			<?php endif; ?>
			<!-- end : Hasil Proses NL to Source code -->


            <hr>

        </div>

        <hr>

        <!-- <iframe src="lib/turbopascal-master/index.html" name="iframe" width="100%" height="550px"></iframe> -->
        <!-- <a href="http://stackoverflow.com" target="iframe">SO</a> -->




        <!-- Optional JavaScript -->
        <!-- jQuery first, then Popper.js, then Bootstrap JS -->
        <!-- <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script> -->
        <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script> -->
        <script src="<?php  echo base_url() ?>assets/js/jquery-3.3.1.slim.min.js"></script>
        <script src="<?php  echo base_url() ?>assets/js/popper.min.js"></script>
        <script src="<?php  echo base_url() ?>assets/js/bootstrap.min.js"></script>
    </body>
</html>
