<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Imam extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		//Codeigniter : Write Less Do More
	}

	function index()
	{
		$this->db->select('parent');
		$this->db->from('grammar_pascal');
		$parent = $this->db->get()->result();
		$list_parent = array();
		foreach ($parent as $key => $value) {
			array_push($list_parent,$value->parent);
		}

		$this->db->select('child');
		$this->db->from('grammar_pascal');
		$child = $this->db->get()->result();

		$result = array();
		foreach ($child as $key => $rule) {
			$rules = $rule->child;
			$string = "";
			$exp_rule = explode("|",$rules);
			foreach ($exp_rule as $key1 => $single_rule) {
				$exp_single_rule = explode(" ",$single_rule);
				foreach ($exp_single_rule as $key2 => $token) {
					if (in_array($token,$list_parent)) {
						$token = '('.$token.')';
					}
					$string = $string.$token." ";
				}
				if (count($exp_rule)-1 != $key1) {
					$string = $string."|";
				}
			}
			echo $string."<br>";
		}

		// var_dump($temp);
		
	}
}
