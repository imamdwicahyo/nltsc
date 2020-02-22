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
    $diterima   = 0; //variabel status diterima/tidak
    $endStack   = 0; //pointer untuk menyimpan stack terakhir
    $endList    = 0; //
    $stack      = [];
    $listStack  = [];
    $result     = [];
    $keyToken   = 0;
    $maxLoop    = 100000;
    $pesan      = TRUE;
    // $maxLoop = 1741;

    //membuat list token yang akan di cek
    $tokenKata = array();
    foreach ($input as $key => $value) {
        if ($value['class'] != 'String') {
            $temp = explode(' ', $value['token']);
            foreach ($temp as $t) {
                array_push($tokenKata, $t);
            }

        }else{
            array_push($tokenKata, $value['token']);
        }
    }

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

    //memasukan stack pertama
    $key = array_search("START",$listParent);
    list($endStack,$stack) = $this->inputCheck($endList,$stack,$listChild[$key]);
    $listStack = $this->addData($listStack,$stack,TRUE,$keyToken,"");

    $countLoop = 0; $word="";
    while ($diterima==0 AND $countLoop<$maxLoop AND count($listStack)>0 AND count($input)>0) {
      //mendapatkan key stack terakhir
      $endList = count($listStack)-1;
      $word="";

      // jika status ceknya true maka lakukan pengecekan
      // jika tidak maka kembali ke track sebelumya yang belum di cek
      if ($listStack[$endList]['status']==TRUE) {
        $stack = $listStack[$endList]['child']; //mendapatkan stack terakhir
        $endStack = sizeof($stack)-1; //mendapatkan key stack

        //mengecek apakah stack terakhir punya child atau tidak (output bolean)
        $haveChild = $this->haveChild(end($stack),$grammarNL);

        // if ($keyToken>=(count($input)-1) AND (count($stack) > 1)) {
        //   //jika token habis tapi stack masih ada
        //   //maka kembali ke track sebelumya yang belum di cek
        //   array_pop($listStack);
        //   $endList = count($listStack)-1;
        //   $keyToken = $listStack[$endList]['key'];
        //   $listStack[$endList]['action'] = "Checking success, deleting last stack (1)";
        if ($haveChild==TRUE) {
          //jika stack terakhir memiliki anak lakukan :
          $child = $this->getChild(end($stack),$grammarNL);
          $child = explode("|",$child);

          if (sizeof($child) == 1) {
            //jika cuma ada 1 anak maka masukan ke stack
            list($endStack,$listStack[$endList]['child'])=$this->inputCheck($endStack,$listStack[$endList]['child'],$child[0]);
            $listStack[$endList]['action'] = "Added to stack";
          }else {
            //jika lebih dari 1 child
            //maka setiap child dibuatkan stack lalu masukan ke listStack

            $listStack[$endList]['status'] = false; //ubah status check false karena sudah di cek
            $countChild = sizeof($child); //mendapatkan banyaknya child
            $listStack[$endList]['action'] = "Created new list stack ($countChild)";

            //lakukan perulangan sebanyak banyaknya child lalu buatkan stack lalu masukan kedalam listStack
            foreach ($child as $key => $value) {
              $endStack = sizeof($stack)-1;
              list($tempEnd,$tempStack)=$this->inputCheck($endStack,$listStack[$endList]['child'],$value);
              $listStack = $this->addData($listStack,$tempStack,TRUE,$keyToken,$input[$keyToken]['token']);
            }
          }
        }else{
          //jika tidak memiliki child lakukan pengecekan
          if ($input[$keyToken]['token'] == end($stack)) {
            // jika yang dicek sama

            if ($keyToken==(count($input)-1) AND (count($stack) == 1)) {
              //jika token dan stack habis maka string diterima
              $diterima=1;
            }elseif ($keyToken==(count($input)-1) AND (count($stack) > 1) ) {
              //jika token habis dan stack masih ada
              //maka hapus stack terakhir lalu cek stack sebelumya
              array_pop($listStack);
              $endList = count($listStack)-1;
              $listStack[$endList]['action'] = "Previous list has been delete";
              $keyToken = $listStack[$endList]['key'];
            }else {
              //juka token masih ada dan stack masih ada
              //maka lakukan ke pengecekan ke stack
              $keyToken++; $word = $input[$keyToken]['token'];
              array_pop($listStack[$endList]['child']);
              $listStack[$endList]['action'] = "Checking success, deleting last stack";
              $listStack[$endList]['check']=$input[$keyToken]['token'];
              $listStack[$endList]['key']=$listStack[$endList]['key']+1;
              $endStack=$endStack-1;
            }
          }else {
            //jika yang dicek tidak sama
            //maka hapus stack terakhir lalu cek stack sebelumnya yang sebelumnya
            array_pop($listStack);
            $endList = count($listStack)-1;
            $listStack[$endList]['action'] = "Previous list has been delete";
            $keyToken = $listStack[$endList]['key'];
          }
        }
      }else {
        //jika status ceknya false
        if ((count($listStack) == 1)) {
          array_pop($listStack);
        }else {
          array_pop($listStack);
          $endList = count($listStack)-1;
          $keyToken = $listStack[$endList]['key'];
        }
      }


      if (count($stack)==0) {
        $diterima=1;
      }

      if ($pesan = TRUE) {
        if ($word!="") {
          $stringStack = "";
          foreach ($stack as $key => $value) {
            $stringStack = $stringStack.$value." ";
          }
          $temp = array(
            'word' => $word,
            'stack' => $stringStack,
          );

          array_push($result,$temp);
        }
      }

      $countLoop++;
    }//end while


    $message = "Ditolak";
    if ($diterima==1) {
      $message = "Diterima";
    }

    $data = array(
      'numLoop' => $countLoop,
      'maxLoop' => $maxLoop,
      'message' => $message,
      'list'  => $result,
      'result' => $listStack,
     );

    var_dump($data);
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
    $endStack = sizeof($array)-1;
    return array($endStack,$array);
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
