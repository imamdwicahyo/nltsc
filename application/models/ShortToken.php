<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ShortToken extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        //Codeigniter : Write Less Do More
    }

    function process($changeToken)
    {
        $operator = array('+', '-', '*', '/', 'mod', 'div', '>', '>=', '<', '<=', '=', '<>', 'to', 'downto');
        $varList = array();

        //mendapatkan list variable
        foreach ($changeToken as $key => $value) {
            if ($value['class'] == 'VariableIdent') {
                if (in_array($value['token'], $varList) == false) {
                    array_push($varList, $value['token']);
                }
            }
        }

        //jika ada urutan operator yang salah
        //misalkan bagi 5 dengan 2 ==> harusnya 5 bagi 2
        //maka tukar urutannya menjadi 5 bagi 2
        $token_container = '';
        $class_container = '';
        foreach ($changeToken as $key => $value) {
            if (in_array($value['token'], $operator)) {
                if ((($changeToken[$key - 1]['class'] != 'VariableIdent') && ($changeToken[$key - 1]['class'] != 'Number') && ($changeToken[$key - 1]['class'] != 'String') && ($changeToken[$key - 1]['class'] != ')'))) {
                    // membetulkan posisi eksprsi
                    $token_container = $changeToken[$key + 1]['token'];
                    $class_container = $changeToken[$key + 1]['class'];
                    $changeToken[$key + 1]['token'] = $changeToken[$key]['token'];
                    $changeToken[$key + 1]['class'] = $changeToken[$key]['class'];
                    $changeToken[$key]['token'] = $token_container;
                    $changeToken[$key]['class'] = $class_container;
                }
            }
        }
        $array_id = array();
        $array_penampung_token = array();
        $array_penampung_kelas = array();
        $max = count($changeToken);
        $cursor = 0;
        $a = 0;
        // proses agar posisi " var := number + number "
        while (($cursor + 1) != $max && $a < 50) {
            if (($changeToken[$cursor]['class'] == 'VariableIdent') || ($changeToken[$cursor]['class'] == 'Number') || ($changeToken[$cursor]['class'] == 'String')) {
                //jika class yang di cek adalah VariableIdent/Number/String maka lakukan perintah berikut
                if ((in_array($changeToken[$cursor + 1]['token'], $operator))) {
                    //jika token selanjutnya adalah operator
                    array_push($array_id, $cursor);
                    array_push($array_id, $cursor + 1);
                    $cursor = $cursor + 2;
                } elseif ((($changeToken[$cursor]['class'] == 'Number') && ($changeToken[$cursor + 1]['token'] == ':=')) || (($changeToken[$cursor]['class'] == 'String') && ($changeToken[$cursor + 1]['token'] == ':='))) {
                    //jika class yang dicek = Number dan token selanjutnya adalah ':=', atau
                    //jika class yang dicek = String dan token selanjutnya adalah ':='
                    array_push($array_id, $cursor);
                    $cursor++;  // posisi y masuk
                    foreach ($array_id as $key => $id) {
                        // menyimpan id token
                        array_push($array_penampung_token, $changeToken[$id]['token']);
                        array_push($array_penampung_kelas, $changeToken[$id]['class']);
                    }
                    // echo "<b>".$array_penampung_kelas[0]."</b>";
                    // meletakan variabel penampung didepan
                    $changeToken[reset($array_id)]['token'] = $changeToken[$cursor + 1]['token']; // fungsi reset merupakan mengambil value dengan index1
                    $changeToken[reset($array_id)]['class'] = $changeToken[$cursor + 1]['class'];
                    //meletakan operator :=
                    $changeToken[reset($array_id) + 1]['token'] = $changeToken[$cursor]['token'];
                    $changeToken[reset($array_id) + 1]['class'] = $changeToken[$cursor]['class'];
                    for ($i = 0; $i < count($array_id); $i++) {
                        // memasukkan expresi
                        $x = reset($array_id) + $i + 2;
                        $changeToken[$x]['token'] = $array_penampung_token[$i];
                        $changeToken[$x]['class'] = $array_penampung_kelas[$i];
                    }
                    $cursor++;
                    $array_id = array();
                    $array_penampung_token = array();
                    $array_penampung_kelas = array();
                } elseif ($changeToken[$cursor + 1]['token'] == '.' || $changeToken[$cursor + 1]['token'] == ',') {
                    //jika token selanjutnya adalah . atau token selanjutnya adalah,
                    $array_id = array();
                    $array_penampung_token = array();
                    $array_penampung_kelas = array();
                    $cursor++;
                } else {
                    $cursor++;
                }
            } else {
                $cursor++;
            }

            $a++;
        }
        return $changeToken;
    }
}
