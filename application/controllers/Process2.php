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
			'ParsingV2',
			'RemoveAdditionalToken',
			'ChangeToken',
			'ShortToken',
			'CodeInsertion2',
			'TidyingToken',
			'Translation'
		));
		//Codeigniter : Write Less Do More
	}

	function index()
	{
		set_time_limit(300);

		$input = "";
		$data = [];
		if (isset($_POST['input']) AND $_POST['input'] != "") {
			//inisialisasi
			$prepocessing = $this->Prepocessing;
			$mScanning = $this->ScanningV2;
			$parsing = $this->ParsingV2;
			$removeAdditionalToken = $this->RemoveAdditionalToken;
			$changeToken = $this->ChangeToken;
			$shortToken = $this->ShortToken;
			$codeInsertion = $this->CodeInsertion2;
			$tidyingToken = $this->TidyingToken;
			$translation = $this->Translation;

			// get input text dari user
			$input = $this->input->post('input');

			//menggunakan fungsi casefolding untuk mendapatkan teks dengan huruf kecil
			$casefolding = $prepocessing->casefolding($input);
			$data['casefolding'] = $casefolding;

			//menggunakan fungsi filtering untuk menghapus karakter yg tdk diperlukan
			$filtering = $prepocessing->filtering($casefolding, "/[^A-Za-z0-9\ \_\.\,\+\-\(\)\*\/]/");
			$data['filtering'] = $filtering;

			//menggunakan fungsi scanning untuk memecah text kedalah class
			$scanning = $mScanning->process($filtering);
			$data['scanning'] = $scanning;			

			//menggunakan fungsi parser untuk mengecek urutan token
			$parsing = $parsing->process($scanning);
			$data['parsing'] = $parsing;
			// var_dump($parsing);
			// die;


			if ($parsing['diterima'] == '1') {
				$cleanToken = $removeAdditionalToken->process($parsing['scanning']);
				$data['cleanToken'] = $cleanToken;
			
				$changeToken = $changeToken->process($cleanToken);
				$data['changeToken'] = $changeToken;				

				$shortToken = $shortToken->process($changeToken);
				$data['shortToken'] = $shortToken;
				
				$codeInsertion = $codeInsertion->process($shortToken);
				$data['codeInsertion'] = $codeInsertion;
				// var_dump($codeInsertion);die;

				$tdying = $tidyingToken->process($codeInsertion['result']);
				$data['tdying'] = $tdying;				
			}

		}

		$data['input'] = $input;
		$data['status_parsing'] = TRUE;
		$this->load->view('process_viewV2', $data);
	}
}
