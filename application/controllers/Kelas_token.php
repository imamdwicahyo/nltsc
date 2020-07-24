<?php
defined('BASEPATH') or exit('No direct script access allowed');

class kelas_token extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		//Codeigniter : Write Less Do More
		$this->load->model("Model_kelas_token","token");
	}

	function index()
	{
		$all_token = $this->token->get_all_token()->result();
		
		$data = array('all_token' => $all_token, );

		$this->load->view('view_token',$data);
	}
}
