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
		// input file data uji
		$file_data_uji = fopen(base_url() . "file/1.data_uji_for_do.txt", "r") or die("Unable to open file!");
		$list_data_uji 	=  fread($file_data_uji, 100000);
		fclose($file_data_uji);

		// input file data harapan
		$harapan_data_uji = fopen(base_url() . "file/1.harapan_uji_for_do.txt", "r") or die("Unable to open file!");
		$list_harapan_uji 	=  fread($harapan_data_uji, 100000);
		fclose($harapan_data_uji);

		$list_input = explode("\n", $list_data_uji);
		$list_hope = explode("\n", $list_harapan_uji);

		$result = $this->proses_NLP($list_input, $list_hope);

		$this->load->view('accuration_view', $result);
	}

	function proses_NLP($list_input, $list_hope)
	{
		$start = microtime(true); // time start
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

		$result = array();
		$benar = 0;
		$salah = 0;
		$total_data_uji = 0;
		foreach ($list_input as $key => $input) {
			if ($input != "") {
				$single_start = microtime(true); // time start

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

				$tdying = "";
				if ($parsing['diterima'] == '1') {
					$cleanToken = $mRemoveAdditionalToken->process($parsing['scanning']);
					$data['cleanToken'] = $cleanToken;

					$changeToken = $mChangeToken->process($cleanToken);
					$data['changeToken'] = $changeToken;

					$shortToken = $mShortToken->process($changeToken);
					$data['shortToken'] = $shortToken;

					$codeInsertion = $mCodeInsertion->process($shortToken);
					$data['codeInsertion'] = $codeInsertion;

					$tdying = $mTidyingToken->process($codeInsertion['result']);
					$data['tdying'] = $tdying;
				}
				$hope  = preg_replace('/[\n\r]/', '', $list_hope[$key]);
				$hope2 = $mTidyingToken->process(explode(" ", $hope));

				if ($hope2 == $tdying) {
					$status = "benar";
					$benar++;
				} else {
					$status = "salah";
					$salah++;
				}

				$single_time_elapsed_secs = microtime(true) - $single_start; //time end

				$loop_insertion = 0;
				if (isset($codeInsertion)) {
					$loop_insertion = $codeInsertion['loop'];
				}
				$temp = array(
					'kalimat' => $input . "(" . $parsing['loop'] . ")",
					'harapan' => $hope2,
					'hasil'   => $tdying . "(" . $loop_insertion . ")",
					'status'  => $status,
					'time'	  => $single_time_elapsed_secs,
				);
				array_push($result, $temp);

				$total_data_uji++;
			}
		}

		$time_elapsed_secs = microtime(true) - $start; //time end

		$hasil_uji = array(
			'benar' => $benar,
			'salah' => $salah,
			'total' => $total_data_uji,
			'akurasi' => round($benar / $total_data_uji * 100,2),
			'time' => $time_elapsed_secs,
		);

		$data = array(
			'result' => $result,
			'hasil_uji' => $hasil_uji,
		);
		return $data;
	}
}
