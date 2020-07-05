<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Accuration extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		//Codeigniter : Write Less Do More
		$this->load->model(array(
			'Prepocessing',
			'ScanningV2',
			'ParsingV2',
			'RemoveAdditionalToken',
			'ChangeToken',
			'ShortToken',
			'CodeInsertion2',
			'TidyingToken',
		));
	}

	function index()
	{
		$myfile = fopen(base_url() . "file/while_do.txt", "r") or die("Unable to open file!");
		$file 	=  fread($myfile, 100000);
		fclose($myfile);

		$list_input = explode("\n", $file);

		foreach ($list_input as $key => $input) {
			echo "$input <br>";
			echo "--> ".$this->proses_NLP($input);
			echo "<br><br><hr><br>";
		}
	}

	function proses_NLP($input)
	{
		$data = [];

		//inisialisasi
		$mPrepocessing = $this->Prepocessing;
		$mScanning = $this->ScanningV2;
		$mParsing = $this->ParsingV2;
		$mRemoveAdditionalToken = $this->RemoveAdditionalToken;
		$mChangeToken = $this->ChangeToken;
		$mShortToken = $this->ShortToken;
		$mCodeInsertion = $this->CodeInsertion2;
		$mTidyingToken = $this->TidyingToken;

		//menggunakan fungsi casefolding untuk mendapatkan teks dengan huruf kecil
		$casefolding = $mPrepocessing->casefolding($input);
		$data['casefolding'] = $casefolding;

		//menggunakan fungsi filtering untuk menghapus karakter yg tdk diperlukan
		$filtering = $mPrepocessing->filtering($casefolding, "/[^A-Za-z0-9\ \_\.\,\+\-\(\)\*\/]/");
		$data['filtering'] = $filtering;

		//menggunakan fungsi scanning untuk memecah text kedalah class
		$scanning = $mScanning->process($filtering);
		$data['scanning'] = $scanning;

		//menggunakan fungsi parser untuk mengecek urutan token
		$parsing = $mParsing->process($scanning);
		$data['parsing'] = $parsing;
		// var_dump($parsing);
		// die;


		if ($parsing['diterima'] == '1') {
			$cleanToken = $mRemoveAdditionalToken->process($parsing['scanning']);
			$data['cleanToken'] = $cleanToken;

			$changeToken = $mChangeToken->process($cleanToken);
			$data['changeToken'] = $changeToken;

			$shortToken = $mShortToken->process($changeToken);
			$data['shortToken'] = $shortToken;

			$codeInsertion = $mCodeInsertion->process($shortToken);
			$data['codeInsertion'] = $codeInsertion;
			// var_dump($codeInsertion);

			$tdying = $mTidyingToken->process($codeInsertion['result']);
			$data['tdying'] = $tdying;
		}

		$data['input'] = $input;
		$data['status_parsing'] = TRUE;

		if (isset($codeInsertion['diterima']) and $codeInsertion['diterima'] == 1) {
			return implode(" ",$codeInsertion['result']);
		}else{
			return NULL;
		}
	}
}
