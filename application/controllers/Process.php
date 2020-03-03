<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Process extends CI_Controller{

  public function __construct()
  {
    parent::__construct();
    $this->load->model(array('Prepocessing',
                             'Scanning',
                             'Parsing',
                             'Translation'
                           ));
    //Codeigniter : Write Less Do More
  }

  function index()
  {
    //inisialisasi
    $prepocessing = $this->Prepocessing;
    $scanning = $this->Scanning;
    $parsing = $this->Parsing;
    $translation = $this->Translation;

    //input text
    $input = "Buat aplikasi uji1. buat variabel a dan i dengan tipe data
              bilangan bulat. untuk i sama dengan 1,5 sampai 10 maka tampilkan imam dwi cahyo. ";
    // $input = "buat program uji50. buat variabel a dan i dengan tipe data
    //           bilangan bulat. Tampilkan Imam. ";
    // $input = "Buat aplikasi tampil_hasi_bagi. Tampilkan imam.";


    //menggunakan fungsi casefolding untuk mendapatkan teks dengan huruf kecil
    $casefolding = $prepocessing->casefolding($input);

    //menggunakan fungsi filtering untuk menghapus karakter yg tdk diperlukan
    $filtering = $prepocessing->filtering($casefolding,"/[^A-Za-z0-9\ \_\.\,\+\-]/");

    //menggunakan fungsi scanning untuk memecah text kedalah class
    $scanning = $scanning->process($filtering);

    //menggunakan fungsi parser untuk mengecek urutan token
    $parsing = $parsing->process($scanning);

    if ($parsing['diterima']=='1') {
      $cleanToken = $translation->removeAdditionalToken($scanning);

      $changeToken = $translation->changeToken($cleanToken);

      $shortToken = $translation->shortToken($changeToken);

      $codeInsertion = $translation->codeInsertion($shortToken);
    }
  }

}
