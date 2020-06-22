<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Process2 extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->model(array(
			'Prepocessing',
			'ScanningV2',
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
			$mScanning = $this->ScanningV2;
			$parsing = $this->Parsing;
			$translation = $this->Translation;

			// get input text dari user
			$input = $this->input->post('input');

			//menggunakan fungsi casefolding untuk mendapatkan teks dengan huruf kecil
			$casefolding = $prepocessing->casefolding($input);
			$data['casefolding'] = $casefolding;

			//menggunakan fungsi filtering untuk menghapus karakter yg tdk diperlukan
			$filtering = $prepocessing->filtering($casefolding, "/[^A-Za-z0-9\ \_\.\,\+\-]/");
			$data['filtering'] = $filtering;

			//menggunakan fungsi scanning untuk memecah text kedalah class
			$scanning = $mScanning->process($filtering);
			$data['scanning'] = $scanning;
			
			//menggunakan fungsi parser untuk mengecek urutan token
			$parsing = $parsing->process($scanning);
			$data['parsing'] = $parsing;
			var_dump($parsing);
			die;


			if ($parsing['diterima'] == '1') {
				$cleanToken = $translation->removeAdditionalToken($scanning);
				$data['cleanToken'] = $cleanToken;

				$changeToken = $translation->changeToken($cleanToken);
				$data['changeToken'] = $changeToken;

				$shortToken = $translation->shortToken($changeToken);
				$data['shortToken'] = $shortToken;

				$codeInsertion = $translation->codeInsertion($shortToken);
				$data['codeInsertion'] = $codeInsertion;
				// var_dump($codeInsertion);die;

				$tdying = $translation->tidyingToken2($codeInsertion['result']);
				$data['tdying'] = $tdying;
			}

			// echo "$tdying";
		}

		$data['input'] = $input;
		$data['status_parsing'] = TRUE;
		$this->load->view('process_view', $data);
	}
}
