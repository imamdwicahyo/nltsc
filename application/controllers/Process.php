<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Process extends CI_Controller{

  public function __construct()
  {
    parent::__construct();
    $this->load->model(array('Prepocessing'));
    //Codeigniter : Write Less Do More
  }

  function index()
  {
    //inisialisasi
    $prepocessing = $this->Prepocessing;

    //input text
    $input = "Buat aplikasi uji1. buat variabel a dan i dengan tipe data
              bilangan bulat. untuk i sama dengan 1 sampai 10 maka baca a.?";

    //menggunakan fungsi casefolding untuk mendapatkan teks dengan huruf kecil
    $casefolding = $prepocessing->casefolding($input);

    //menggunakan fungsi filtering untuk menghapus karakter yg tdk diperlukan
    $filtering = $prepocessing->filtering($casefolding,"/[^A-Za-z0-9\ \_\.\,\+\-]/");

  }

}
