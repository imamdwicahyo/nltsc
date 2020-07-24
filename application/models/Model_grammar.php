<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Model_grammar extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        //Codeigniter : Write Less Do More
    }

    public function get_all_grammar()
    {
		$this->db->select('id, parent, child');
		$this->db->from('grammar_nl');
		$grammar_nl = $this->db->get()->result();
		
		$this->db->select('id, parent, child');
		$this->db->from('grammar_pascal');
		$grammar_pascal = $this->db->get()->result();

		$result = array(
			'grammar_nl' => $grammar_nl,
			'grammar_pascal' => $grammar_pascal,
		);
		return $result;
    }
}
