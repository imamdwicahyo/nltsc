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
		set_time_limit(300);
		$start = microtime(true); // time start

		$hasil_uji = array();
		$file = array(
			array('judul'=>"Variasi operasi perulangan",'file_uji'=> "Data_uji.txt",'file_harapan'=>"Harapan_uji.txt"),
		);
		// $file = array(
		// 	array('judul' => "For Do", 'file_uji' => "1.data_uji_for_do.txt", 'file_harapan' => "1.harapan_uji_for_do.txt"),
		// 	array('judul' => "While Do", 'file_uji' => "2.data_uji_while_do.txt", 'file_harapan' => "2.harapan_uji_while_do.txt"),
		// );

		$benar = 0;
		$salah = 0;
		$total = 0;
		foreach ($file as $key => $value) {
			// input file data uji
			$file_data_uji = fopen(base_url() . "file/" . $value['file_uji'], "r") or die("Unable to open file!");
			$list_data_uji 	=  fread($file_data_uji, 100000);
			fclose($file_data_uji);

			// input file data harapan
			$harapan_data_uji = fopen(base_url() . "file/" . $value['file_harapan'], "r") or die("Unable to open file!");
			$list_harapan_uji 	=  fread($harapan_data_uji, 100000);
			fclose($harapan_data_uji);

			$list_input = explode("\n", $list_data_uji);
			$list_hope = explode("\n", $list_harapan_uji);

			$result = $this->proses_NLP($list_input, $list_hope, $value['judul']);
			$benar = $benar + $result['hasil_uji']['benar'];
			$salah = $salah + $result['hasil_uji']['salah'];
			$total = $total + $result['hasil_uji']['total'];

			array_push($hasil_uji, $result);
		}

		$time_elapsed_secs = microtime(true) - $start; //time end
		$result_total = array(
			'benar' => $benar,
			'salah' => $salah,
			'total' => $total,
			'akurasi' => round($benar/$total,2),
			'time' => $time_elapsed_secs,
		);

		$data = array(
			'hasil_uji_keseluruhan' => $result_total,
			'hasil_uji_single' => $hasil_uji,
		);

		$this->load->view('accuration_view', $data);
	}

	function proses_NLP($list_input, $list_hope, $nama_uji)
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
			'judul' => $nama_uji,
			'benar' => $benar,
			'salah' => $salah,
			'total' => $total_data_uji,
			'akurasi' => round($benar / $total_data_uji * 100, 2),
			'time' => $time_elapsed_secs,
		);

		$data = array(
			'result' => $result,
			'hasil_uji' => $hasil_uji,
		);
		return $data;
	}
}
