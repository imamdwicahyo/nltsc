<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Scanning extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        //Codeigniter : Write Less Do More
    }

    public function process($text)
    {
        $resultScanning = [];

        //menambahkan spasi sebelum dan sesudah tanda baca
        $tandaBacaLama = array('. ', ', ');
        $tandaBacaBaru = array(' . ', ' , ');
        $text = str_replace($tandaBacaLama, $tandaBacaBaru, $text);
        $tandaBacaLama = array('.', ',');
        $tandaBacaBaru = array(' . ', ' , ');
        $text = str_replace($tandaBacaLama, $tandaBacaBaru, $text);

        //memecah teks yang dibatasi oleh spasi
        $listKata = explode(" ", $text);

        //menggabungkan nilai yang diapit oleh koma (bilangan desimal)
        foreach ($listKata as $id => $value) {
            if ($value == ',' && is_numeric($listKata[$id - 1]) && is_numeric($listKata[$id + 1])) {
                $listKata[$id] = $listKata[$id - 1] . $listKata[$id] . $listKata[$id + 1];
                $listKata[$id - 1] = "";
                $listKata[$id + 1] = "";
            }
        }

        //mengambil token
        $tokenArithmeticOperator = $this->getTokenByClass('ArithmeticOperator');
        $tokenKeyword = $this->getTokenByClass('Keyword');
        $tokenAdditionalToken = $this->getTokenByClass('AdditionalToken');
        // $tokenComparisonOperator = $this->getTokenByClass('ComparisonOperator'); // ini gak dipake
        $tokenKeywordIf = $this->getTokenByClass('KeywordIf'); //dari hadiyan
        $tokenKeywordElse = $this->getTokenByClass('KeywordElse'); //dari hadiyan
        $tokenLogicOperator = $this->getTokenByClass('LogicOperator'); //dari hadiyan

        //mendapatkan token variabel
        $tokenVariables = [];
        foreach ($listKata as $id => $value) {
            if ($value == 'variabel' || $value == 'var') {
                $i = $id;
                while ($listKata[$i] != '.' && $i < 20) {
                    if ((in_array($listKata[$i], $tokenArithmeticOperator) == false) &&
                        (in_array($listKata[$i], $tokenKeyword) == false) &&
                        (in_array($listKata[$i], $tokenAdditionalToken) == false) &&
                        ($listKata[$i] != '.') &&
                        ($listKata[$i] != ',') &&
                        ($listKata[$i] != 'data') &&
                        ($listKata[$i] != "")
                    ) {
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
                array_push($resultScanning, $temp);
                $id++;
            } elseif (($listKata[$id] == '.') || ($listKata[$id] == ',')) {
                $temp = array('token' => $listKata[$id], 'class' => $listKata[$id]);
                array_push($resultScanning, $temp);
                $id++;
            } elseif ($id < $max - 5 and in_array($listKata[$id] . ' ' . $listKata[$id + 1] . ' ' . $listKata[$id + 2] . ' ' . $listKata[$id + 3] . ' ' . $listKata[$id + 4], $tokenArithmeticOperator)) {
                $temp = array(
                    'token' => $listKata[$id] . ' ' . $listKata[$id + 1] . ' ' . $listKata[$id + 2] . ' ' . $listKata[$id + 3] . ' ' . $listKata[$id + 4],
                    'class' => 'ArithmeticOperator'
                );
                array_push($resultScanning, $temp);
                $id = $id + 5;
            } elseif ($id < $max - 4 and in_array($listKata[$id] . ' ' . $listKata[$id + 1] . ' ' . $listKata[$id + 2] . ' ' . $listKata[$id + 3], $tokenArithmeticOperator)) {
                $temp = array(
                    'token' => $listKata[$id] . ' ' . $listKata[$id + 1] . ' ' . $listKata[$id + 2] . ' ' . $listKata[$id + 3],
                    'class' => 'ArithmeticOperator'
                );
                array_push($resultScanning, $temp);
                $id = $id + 4;
            } elseif ($id < $max - 3 and in_array($listKata[$id] . ' ' . $listKata[$id + 1] . ' ' . $listKata[$id + 2], $tokenArithmeticOperator)) {
                $temp = array(
                    'token' => $listKata[$id] . ' ' . $listKata[$id + 1] . ' ' . $listKata[$id + 2],
                    'class' => 'ArithmeticOperator'
                );
                array_push($resultScanning, $temp);
                $id = $id + 3;
            } elseif ($id < $max - 2 and in_array($listKata[$id] . ' ' . $listKata[$id + 1], $tokenArithmeticOperator)) {
                $temp = array(
                    'token' => $listKata[$id] . ' ' . $listKata[$id + 1],
                    'class' => 'ArithmeticOperator'
                );
                array_push($resultScanning, $temp);
                $id = $id + 2;
            } elseif (in_array($listKata[$id], $tokenArithmeticOperator)) {
                $temp = array('token' => $listKata[$id], 'class' => 'ArithmeticOperator');
                array_push($resultScanning, $temp);
                $id++;
            } elseif ($id < $max - 2 and in_array($listKata[$id] . ' ' . $listKata[$id + 1], $tokenKeyword)) {
                $temp = array(
                    'token' => $listKata[$id] . ' ' . $listKata[$id + 1],
                    'class' => 'Keyword'
                );
                array_push($resultScanning, $temp);
                $id = $id + 2;
            } elseif (in_array($listKata[$id], $tokenKeyword)) {
                $temp = array('token' => $listKata[$id], 'class' => 'Keyword');
                array_push($resultScanning, $temp);
                $id++;
            } elseif (in_array($listKata[$id], $tokenAdditionalToken)) {
                $temp = array('token' => $listKata[$id], 'class' => 'AdditionalToken');
                array_push($resultScanning, $temp);
                $id++;
            } elseif (in_array($listKata[$id], $tokenVariables)) {
                $temp = array('token' => $listKata[$id], 'class' => 'VariableIdent');
                array_push($resultScanning, $temp);
                $id++;
            } elseif ($listKata[$id] == "") {
                $id++;
            } elseif ($id < $max - 2 and in_array($listKata[$id] . ' ' . $listKata[$id + 1], $tokenKeywordIf)) { //dari hadiyan
                $temp = array(
                    'token' => $listKata[$id] . ' ' . $listKata[$id + 1],
                    'class' => 'KeywordIf'
                );
                array_push($resultScanning, $temp);
                $id = $id + 2;
            } elseif (in_array($listKata[$id], $tokenKeywordIf)) { // dari hadiyan
                $temp = array('token' => $listKata[$id], 'class' => 'KeywordIf'); 
                array_push($resultScanning, $temp);
                $id++;
            } elseif ($id < $max - 2 and in_array($listKata[$id] . ' ' . $listKata[$id + 1], $tokenKeywordElse)) { //dari hadiyan
                $temp = array(
                    'token' => $listKata[$id] . ' ' . $listKata[$id + 1],
                    'class' => 'KeywordElse'
                );
                array_push($resultScanning, $temp);
                $id = $id + 2;
            } elseif (in_array($listKata[$id], $tokenKeywordElse)) { // dari hadiyan
                $temp = array('token' => $listKata[$id], 'class' => 'KeywordElse'); 
                array_push($resultScanning, $temp);
                $id++;
            } elseif (in_array($listKata[$id], $tokenLogicOperator)) { // dari hadiyan
                $temp = array('token' => $listKata[$id], 'class' => 'LogicOperator'); 
                array_push($resultScanning, $temp);
                $id++;
            } else {
                if ($id > 1 and ($listKata[$id - 1] == 'program' || $listKata[$id - 1] == 'aplikasi')) {
                    $temp = array('token' => $listKata[$id], 'class' => 'ProgramIdent');
                    array_push($resultScanning, $temp);
                    $id++;
                } else {
                    if ($id > 0) {
                        $last = end($resultScanning);
                    }
                    if (isset($last) and $last['class'] == 'String') {
                        $end = count($resultScanning);
                        $resultScanning[$end - 1]['token'] = $resultScanning[$end - 1]['token'] .
                            " " . $listKata[$id];
                        $id++;
                    } else {
                        $temp = array('token' => $listKata[$id], 'class' => 'String');
                        array_push($resultScanning, $temp);
                        $id++;
                    }
                }
            }
        }

        return $resultScanning;
    }

    function getTokenByClass($class)
    {
        $this->db->select('token');
        $this->db->where('class', $class);
        $res = $this->db->get('token_class')->result();
        $array = [];
        foreach ($res as $value) {
            array_push($array, $value->token);
        }
        return $array;
    }
}
