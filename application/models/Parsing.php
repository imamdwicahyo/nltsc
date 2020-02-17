<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Parsing extends CI_Model{

  public function __construct()
  {
    parent::__construct();
    //Codeigniter : Write Less Do More
  }

  public function Process($input){
    //insisialisasi
    $save = 0;
    $end  = 0;
    $diterima = FALSE;
    $listChecking = [];
    $maxLoop = 5;

    //mendapatkan list grammar parent
    $listParent = $this->getDataGrammarNL("parent");
    $listChild  = $this->getDataGrammarNL("child");
    $grammarNL = array('listParent' => $listParent,
                       'listChild' => $listChild);

    //inisialisasi aturan grammar pertama
    $key  = array_search('START', $listParent);
    list($end,$listChecking) = $this->inputCheck($end,$listChecking,$listChild[$key]);//tampung ke array pengecekan
    $save = $end;//set variabel save = end

    foreach ($input as $key => $word) {
      echo "Check = ".$word['token'];
      $save--;
      $check = $listChecking[$end]; //yang di cek sekarang

      //cek apakah token ini punya child
      $loop = 0;
      while ($this->haveChild($check,$grammarNL) == TRUE AND $loop < $maxLoop) {
        $child = $this->getChild($check,$grammarNL);
        list($end,$listChecking) = $this->inputCheck($end,$listChecking,$child);
        $check = $listChecking[$end];
        $loop++;
      }

      if (in_array( $word['token'],explode("|", $listChecking[$end])) ) {
        echo " -- DITERIMA <br>";
        unset($listChecking[$end]);
      }else {
        echo " -- TIDAK DITERIMA <br>";
      }

      $end--;
      $check = $listChecking[$end];
      echo "check Baru ".$check." [$end]<br>";
    }

    var_dump($listChecking);

  }

  function getDataGrammarNL($field){
    $this->db->select($field);
    $res = $this->db->get('grammar_nl')->result();
    $array = [];
    foreach ($res as $value) {
      array_push($array,$value->$field);
    }
    return $array;
  }

  function inputCheck($start,$array,$string){
    $expld = explode(" ", $string);
    $size = sizeof($expld);
    for ($i=$size-1; $i >= 0; $i--) {
      $array[$start] = $expld[$i];
      $start++;
    }
    $end = sizeof($array)-1;
    return array($end,$array);
  }

  function haveChild($check,$grammarNL){
    $listParent=$grammarNL['listParent'];
    $key  = array_search($check, $listParent);
    if ($key != "") {
      return TRUE;
    }else {
      return FALSE;
    }
  }

  function getChild($check,$grammarNL){
    $listParent = $grammarNL['listParent'];
    $listChild  = $grammarNL['listChild'];
    $key = array_search($check, $listParent);
    return $listChild[$key];
  }

}
