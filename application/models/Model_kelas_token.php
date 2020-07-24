<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Model_kelas_token extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        //Codeigniter : Write Less Do More
    }

    public function get_all_token()
    {
		$this->db->select('id, class, GROUP_CONCAT(token SEPARATOR ", ") as token');
		$this->db->from('token_class');
		$this->db->group_by('class');
		$result = $this->db->get();
		return $result;
	}
}
