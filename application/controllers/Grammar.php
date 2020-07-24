<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Grammar extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		//Codeigniter : Write Less Do More
		$this->load->model("Model_grammar","grammar");
	}

	function index()
	{
		$all_grammar = $this->grammar->get_all_grammar();

		$this->load->view('view_grammar',$all_grammar);
	}
}
