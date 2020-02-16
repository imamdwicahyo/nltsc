<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Prepocessing extends CI_Model{

  public function __construct()
  {
    parent::__construct();
    //Codeigniter : Write Less Do More
  }

  public function casefolding($input){
    $casefolding = strtolower($input);
    return $casefolding;
  }

  public function filtering($input,$character){
    $filtering = preg_replace($character, '', $input);
    return $filtering;
  }

}
