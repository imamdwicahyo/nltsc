<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Analisis extends CI_Model{

  public function __construct()
  {
    parent::__construct();
    //Codeigniter : Write Less Do More
  }

  public function scanning($text){
    $resultScanning = [];

    //menambahkan spasi sebelum dan sesudah tanda baca
    $tandaBacaLama = array('. ', ', ');
    $tandaBacaBaru = array(' . ', ' , ');
    $text = str_replace($tandaBacaLama, $tandaBacaBaru, $text);

    //memecah teks yang dibatasi oleh spasi
    $listKata = explode(" ", $text);

    //mengambil token
    $tokenArithmeticOperator = $this->getTokenByClass('ArithmeticOperator');
    $tokenKeyword = $this->getTokenByClass('Keyword');
    $tokenAdditionalToken = $this->getTokenByClass('AdditionalToken');
    $tokenComparisonOperator = $this->getTokenByClass('ComparisonOperator');

    //mendapatkan token variabel
    $tokenVariables = [];
    foreach ($listKata as $key => $value) {
        if ($value == 'variabel' || $value == 'var') {
            $i = $key;
            while ($listKata[$i]!="" && $listKata[$i] != '.' && $i < 20) {
                if ((in_array($listKata[$i], $tokenArithmeticOperator) == false) &&
                    (in_array($listKata[$i], $tokenKeyword) == false) &&
                    (in_array($listKata[$i], $tokenAdditionalToken) == false) &&
                    ($listKata[$i] != '.') &&
                    ($listKata[$i] != ',') &&
                    ($listKata[$i] != 'data')
                )
                {
                    array_push($tokenVariables, $listKata[$i]);
                }
                $i++;
            }
            break;
        }
    }

    //mengklasifikasikan kata kedalam class class
    $id = 0;
    $max = count($listKata);
    while ($id < $max) {
      if (is_numeric($listKata[$id]) || is_numeric(preg_replace('/[,]/', '', $listKata[$id]))) {
            $temp = array('token' => $listKata[$id], 'class' => 'Number');
            array_push($resultScanning,$temp);
            $id++;
        } elseif (($listKata[$id] == '.') || ($listKata[$id] == ',')) {
            $temp = array('token' => $listKata[$id], 'class' => $listKata[$id]);
            array_push($resultScanning,$temp);
            $id++;
        } elseif ($id < $max-5 AND in_array($listKata[$id].' '.$listKata[$id+1].' '.$listKata[$id+2].' '.$listKata[$id+3].' '.$listKata[$id+4], $tokenArithmeticOperator)) {
            $temp = array('token' => $listKata[$id].' '.$listKata[$id+1].' '.$listKata[$id+2].' '.$listKata[$id+3].' '.$listKata[$id+4],
                          'class' => 'ArithmeticOperator');
            array_push($resultScanning,$temp);
            $id=$id+5;
        } elseif ($id < $max-4 AND in_array($listKata[$id].' '.$listKata[$id+1].' '.$listKata[$id+2].' '.$listKata[$id+3], $tokenArithmeticOperator)) {
            $temp = array('token' => $listKata[$id].' '.$listKata[$id+1].' '.$listKata[$id+2].' '.$listKata[$id+3],
                          'class' => 'ArithmeticOperator');
            array_push($resultScanning,$temp);
            $id=$id+4;
        } elseif ($id < $max-3 AND in_array($listKata[$id].' '.$listKata[$id+1].' '.$listKata[$id+2], $tokenArithmeticOperator)) {
            $temp = array('token' => $listKata[$id].' '.$listKata[$id+1].' '.$listKata[$id+2],
                          'class' => 'ArithmeticOperator');
            array_push($resultScanning,$temp);
            $id=$id+3;
        } elseif ($id < $max-2 AND in_array($listKata[$id].' '.$listKata[$id+1], $tokenArithmeticOperator)) {
            $temp = array('token' => $listKata[$id].' '.$listKata[$id+1],
                          'class' => 'ArithmeticOperator');
            array_push($resultScanning,$temp);
            $id=$id+2;
        } elseif (in_array($listKata[$id], $tokenArithmeticOperator)) {
            $temp = array('token' => $listKata[$id],'class' => 'ArithmeticOperator');
            array_push($resultScanning,$temp);
            $id++;
        } elseif ($id < $max-2 AND in_array($listKata[$id].' '.$listKata[$id+1], $tokenKeyword)) {
            $temp = array('token' => $listKata[$id].' '.$listKata[$id+1],
                          'class' => 'Keyword');
            array_push($resultScanning,$temp);
            $id=$id+2;
        } elseif (in_array($listKata[$id], $tokenKeyword)) {
            $temp = array('token' => $listKata[$id],'class' => 'Keyword');
            array_push($resultScanning,$temp);
            $id++;
        } elseif (in_array($listKata[$id], $tokenAdditionalToken)) {
            $temp = array('token' => $listKata[$id],'class' => 'AdditionalToken');
            array_push($resultScanning,$temp);
            $id++;
        } elseif (in_array($listKata[$id], $tokenVariables)) {
            $temp = array('token' => $listKata[$id],'class' => 'VariableIdent');
            array_push($resultScanning,$temp);
            $id++;
        } elseif ($listKata[$id]=="") {
            $id++;
        } else {
            if ($id > 1 AND ($listKata[$id-1] == 'program' || $listKata[$id-1] == 'aplikasi')) {
                $temp = array('token' => $listKata[$id],'class' => 'ProgramIdent');
                array_push($resultScanning,$temp);
                $id++;
            } else {
                if ($id>0) {
                  $last = end($resultScanning);
                }
                if (isset($last) AND $last['class'] == 'String') {
                    $end = count($resultScanning);
                    $resultScanning[$end-1]['token'] = $resultScanning[$end-1]['token'].
                                                       " ".$listKata[$id];
                    $id++;
                } else {
                    $temp = array('token' => $listKata[$id],'class' => 'String');
                    array_push($resultScanning,$temp);
                    $id++;
                }

            }
        }
    }
    return $resultScanning;
  }

  function getTokenByClass($class){
    $this->db->select('token');
    $this->db->where('class', $class);
    $res = $this->db->get('token_class')->result();
    $array = [];
    foreach ($res as $value) {
      array_push($array,$value->token);
    }
    return $array;
  }



}
