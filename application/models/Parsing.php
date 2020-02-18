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
    $diterima = 0;
    $stack = [];
    $last = 0;
    $listStack = [];
    $keyToken = 0;
    $maxLoop = 0;
    $maxLoop = 1738;

    // mendapatkan token dan class untuk grammar NL
    $program_ident = array();
    $variable_dent = array();
    $number = array();
    $string = array();
    foreach ($input as $key => $value) {
        if ($value['class'] == 'ProgramIdent') {
            array_push($program_ident, $value['token']);
        } elseif ($value['class'] == 'VariableIdent') {
            array_push($variable_dent, $value['token']);
        } elseif ($value['class'] == 'String') {
            $str = preg_replace('/[ ]/', '#', $value['token']); //output hasil Filtering
            array_push($string, $str);
        } elseif ($value['class'] == 'Number') {
            array_push($number, $value['token']);
        }
    }

    //Menghilangkan data yang duplikat
    $program_ident = array_unique($program_ident);
    $variable_dent = array_unique($variable_dent);
    $string = array_unique($string);
    $number = array_unique($number);

    //mendapatkan list grammar parent
    $listParent = $this->getDataGrammarNL("parent");
    $listChild  = $this->getDataGrammarNL("child");
    array_push($listParent, 'PROGRAM_IDENT');
    array_push($listChild, implode("|", $program_ident));
    array_push($listParent, 'IDENT_VAR');
    array_push($listChild, implode("|", $variable_dent));
    array_push($listParent, 'NUMBER');
    array_push($listChild, implode("|", $number));
    array_push($listParent, 'STRING');
    array_push($listChild, implode("|", $string));
    $grammarNL = array('listParent' => $listParent,
                       'listChild' => $listChild);

    $key = array_search("START",$listParent);
    list($end,$stack) = $this->inputCheck($last,$stack,$listChild[$key]);
    $listStack = $this->addData($listStack,$stack,TRUE,$keyToken, $input[$keyToken]['token']);

    $countLoop = 0;
    while ($diterima==0 AND $countLoop<=$maxLoop) {
      $last = count($listStack)-1;

      // jika status ceknya true
      if ($listStack[$last]['status']==TRUE) {
        $stack = $listStack[$last]['child'];
        $haveChild = $this->haveChild(end($stack),$grammarNL);
        $end = sizeof($stack)-1;

        if ($keyToken>=(count($input)-1) AND (count($stack) > 0)) {
          array_pop($listStack);
          $last = count($listStack)-1;
          $keyToken = $listStack[$last]['key'];
          $listStack[$last]['action'] = "Checking success, deleting last stack (1)";
        }elseif ($haveChild==TRUE) {
          $child = $this->getChild(end($stack),$grammarNL);
          $child = explode("|",$child);

          if (sizeof($child) == 1) { //jika cuma ada 1
            list($end,$listStack[$last]['child'])=$this->inputCheck($end,$listStack[$last]['child'],$child[0]);
            $listStack[$last]['action'] = "Added to stack";
          }else { // jika child lebih dari 1
            $listStack[$last]['status'] = false;
            $countChild = sizeof($child);
            $listStack[$last]['action'] = "Created new list stack ($countChild)";

            foreach ($child as $key => $value) {
              $end = sizeof($stack)-1;
              list($tempEnd,$tempStack)=$this->inputCheck($end,$listStack[$last]['child'],$value);
              $listStack = $this->addData($listStack,$tempStack,TRUE,$keyToken,$input[$keyToken]['token']);
            }
          }
        }else{// jika yang dicek sama
          if ($input[$keyToken]['token'] == end($stack)) {
            // echo "SAMA ".$input[$keyToken]['token']." - ".end($stack)." <br>";
            $keyToken++;
            array_pop($listStack[$last]['child']);
            $listStack[$last]['action'] = "Checking success, deleting last stack";
            $listStack[$last]['check']=$input[$keyToken]['token'];
            $listStack[$last]['key']=$listStack[$last]['key']+1;
            $end=$end-1;
          }else {//jika yang dicek tidak sama
            array_pop($listStack);
            $last = count($listStack)-1;
            $listStack[$last]['action'] = "Previous list has ben delete";
            $keyToken = $listStack[$last]['key'];
          }
        }
      }else { //jika status ceknya false
        if (count($listStack)==1) {
          break;
        }else {
          array_pop($listStack);
          $last = count($listStack)-1;
          $keyToken = $listStack[$last]['key'];
        }
      }
      if (count($stack)==0) {
        $diterima=1;
      }
      $countLoop++;
    }
    if ($diterima==1) {
      echo "HOORRAAYY";
    }
    var_dump($listStack);
    // echo "$countLoop";
  }

  function addData($listStack,$tChild,$status,$key,$check){
    $temp = array(
      'child' => $tChild,
      'check' => "==>> ".$check,
      'action' => "",
      'status' => $status,
      'key' => $key,
    );
    array_push($listStack,$temp);
    return $listStack;
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
