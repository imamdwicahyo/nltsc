<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Process extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->model(array(
			'Prepocessing',
			'Scanning',
			'Parsing',
			'Translation'
		));
		//Codeigniter : Write Less Do More
	}

	function index()
	{

		$input = "";
		$data = [];
		if (isset($_POST['input'])) {
			//inisialisasi
			$prepocessing = $this->Prepocessing;
			$scanning = $this->Scanning;
			$parsing = $this->Parsing;
			$translation = $this->Translation;

			$input = $this->input->post('input');
			//input text
			// $input = "Buat aplikasi Uji. buat variabel a dan i dengan tipe data
            //   bilangan bulat. untuk i sama dengan 1,5 sampai 10 maka tampilkan imam dwi cahyo. ";
			// $input = "buat program uji50. buat variabel a dan i dengan tipe data
			//           bilangan bulat. Tampilkan Imam. ";
			// $input = "Buat aplikasi tampil_hasi_bagi. Tampilkan imam.";
			// $input = "BUAT program uji5. buat variabel i dengan tipe data integer. untuk iterasi 1 sampai 10 pada i maka tampilkan hitungan ke lalu tampilkan i.";


			//menggunakan fungsi casefolding untuk mendapatkan teks dengan huruf kecil
			$casefolding = $prepocessing->casefolding($input);
			$data['casefolding'] = $casefolding;

			//menggunakan fungsi filtering untuk menghapus karakter yg tdk diperlukan
			$filtering = $prepocessing->filtering($casefolding, "/[^A-Za-z0-9\ \_\.\,\+\-]/");
			$data['filtering'] = $filtering;

			//menggunakan fungsi scanning untuk memecah text kedalah class
			$scanning = $scanning->process($filtering);
			$data['scanning'] = $scanning;

			//menggunakan fungsi parser untuk mengecek urutan token
			$parsing = $parsing->process($scanning);
			var_dump($parsing);die;
			$data['parsing'] = $parsing;

			// if ($parsing['diterima'] == '1') {
			// 	$cleanToken = $translation->removeAdditionalToken($scanning);

			// 	$changeToken = $translation->changeToken($cleanToken);

			// 	$shortToken = $translation->shortToken($changeToken);

			// 	$codeInsertion = $translation->codeInsertion($shortToken);
			// 	// var_dump($codeInsertion);die;

			// 	$tdying = $translation->tidyingToken2($codeInsertion);
			// }

			// echo "$tdying";
		}

		$data['input'] = $input;
		$data['status_parsing'] = TRUE;
		$this->load->view('process_view', $data);
	}
}
