<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="assets/css/bootstrap.min.css">

        <title>Hello, world!</title>
    </head>
    <body>
        <br>
        <div class="container">
            <div class="card border-primary">
                <h5 class="card-header text-center">Kelas Token</h5>
                <div class="card-body">
                    <table class="table">
						<?php foreach ($all_token as $key => $value): ?>
							<tr>
								<td><?= $value->class ?></td>
								<td><?= $value->token ?></td>
							</tr>
						<?php endforeach ?>
                        <tr>
                            <td>IdentApp</td>
                            <td>[a..z, 0..9, '_']</td>
                        </tr>
                        <tr>
                            <td>IdentVar</td>
                            <td>[a..z, 0..9, '_']</td>
                        </tr>
                        <tr>
                            <td>Number</td>
                            <td>[0..9, ',']</td>
                        </tr>
                        <tr>
                            <td>String</td>
                            <td>[a..z, 0..9, '_']</td>
						</tr>
						<hr>
                    </table>
                    <?php


                    // foreach ($grammar_parent as $key => $parent) {
                    //     echo "$parent ::= ";
                    //     echo $grammar_child[$key]."<br>";
                    // }

                    ?>
                </div>
            </div>



        </div>
        <script src="assets/js/jquery-3.3.1.slim.min.js"></script>
        <script src="assets/js/popper.min.js"></script>
        <script src="assets/js/bootstrap.min.js"></script>
    </body>
</html>
