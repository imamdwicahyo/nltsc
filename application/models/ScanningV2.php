<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ScanningV2 extends CI_Model
{

    var $result_scanning = array();
    var $result_token = array();
    var $result_class = array();

    public function __construct()
    {
        parent::__construct();
        //Codeigniter : Write Less Do More
    }

    public function process($text)
    {
        $result_scanning = [];

        //menambahkan spasi sebelum dan sesudah tanda baca
        $tandaBacaLama = array('. ', ', ');
        $tandaBacaBaru = array(' . ', ' , ');
        $text = str_replace($tandaBacaLama, $tandaBacaBaru, $text);
        $tandaBacaLama = array('.', ',');
        $tandaBacaBaru = array(' . ', ' , ');
        $text = str_replace($tandaBacaLama, $tandaBacaBaru, $text);

        //memecah teks yang dibatasi oleh spasi
        $list_kata = explode(" ", $text);

        //menggabungkan nilai yang diapit oleh koma (bilangan desimal)
        foreach ($list_kata as $id => $value) {
            if ($value == ',' && is_numeric($list_kata[$id - 1]) && is_numeric($list_kata[$id + 1])) {
                $list_kata[$id] = $list_kata[$id - 1] . $list_kata[$id] . $list_kata[$id + 1];
                $list_kata[$id - 1] = "";
                $list_kata[$id + 1] = "";
            }
        }

        //mengambil data token dan kelas dari database
        $list_token = $this->get_kelas_token('token');
        $list_kelas = $this->get_kelas_token('class');

        //mendapatkan token variabel
        $token_variabel = [];
        foreach ($list_kata as $id => $value) {
            if ($value == 'variabel' || $value == 'var') {
                $i = $id;
                while ($list_kata[$i] != '.' && $i < 20) {
                    if ((in_array($list_kata[$i], $list_token) == false) &&
                        ($list_kata[$i] != '.') &&
                        ($list_kata[$i] != ',') &&
                        ($list_kata[$i] != 'data') &&
                        ($list_kata[$i] != "")
                    ) {
                        array_push($token_variabel, $list_kata[$i]);
                    }
                    $i++;
                }
                break;
            }
        }

        //mengklasifikasikan kata kedalam class class
        $id = 0;
        $max = count($list_kata);
        while ($id < $max) {
            if (is_numeric($list_kata[$id]) || is_numeric(preg_replace('/[,]/', '', $list_kata[$id]))) {
                // jika token yang dicek adalah number
                $temp = array('token' => $list_kata[$id], 'class' => 'Number');
                array_push($result_scanning, $temp);
                $id++;
                // echo "1 = ";
            } elseif (($list_kata[$id] == '.') || ($list_kata[$id] == ',')) {
                // jika token yang dicek adalah delimiter (titik dan koma)
                $temp = array('token' => $list_kata[$id], 'class' => $list_kata[$id]);
                array_push($result_scanning, $temp);
                $id++;
                // echo "2 = ";
            } elseif ($id < $max - 5 and in_array($list_kata[$id] . ' ' . $list_kata[$id + 1] . ' ' . $list_kata[$id + 2] . ' ' . $list_kata[$id + 3] . ' ' . $list_kata[$id + 4], $list_token)) {
                // jika lima kata yang dicek ada di tabel token_class
                $token = $list_kata[$id] . ' ' . $list_kata[$id + 1] . ' ' . $list_kata[$id + 2] . ' ' . $list_kata[$id + 3] . ' ' . $list_kata[$id + 4];
                $key = array_search($token, $list_token);
                $class = $list_kelas[$key];
                $temp = array(
                    'token' => $token,
                    'class' => $class
                );
                array_push($result_scanning, $temp);
                $id = $id + 5;
                // echo "3 = ";
            } elseif ($id < $max - 4 and in_array($list_kata[$id] . ' ' . $list_kata[$id + 1] . ' ' . $list_kata[$id + 2] . ' ' . $list_kata[$id + 3], $list_token)) {
                // jika empat kata yang dicek ada di tabel token_class
                $token = $list_kata[$id] . ' ' . $list_kata[$id + 1] . ' ' . $list_kata[$id + 2] . ' ' . $list_kata[$id + 3];
                $key = array_search($token,$list_token);
                $class = $list_kelas[$key];
                $temp = array(
                    'token' => $token,
                    'class' => $class
                );
                array_push($result_scanning, $temp);
                $id = $id + 4;
                // echo "4 = ";
            } elseif ($id < $max - 3 and in_array($list_kata[$id] . ' ' . $list_kata[$id + 1] . ' ' . $list_kata[$id + 2], $list_token)) {
                // jika tiga kata yang dicek ada di tabel token_class
                $token = $list_kata[$id] . ' ' . $list_kata[$id + 1] . ' ' . $list_kata[$id + 2];
                $key = array_search($token,$list_token);
                $class = $list_kelas[$key];
                $temp = array(
                    'token' => $token,
                    'class' => $class
                );
                array_push($result_scanning, $temp);
                $id = $id + 3;
                // echo "5 = ";
            } elseif ($id < $max - 2 and in_array($list_kata[$id] . ' ' . $list_kata[$id + 1], $list_token)) {
                // jika dua kata yang dicek ada di tabel token_class
                $token = $list_kata[$id] . ' ' . $list_kata[$id + 1];
                $key = array_search($token,$list_token);
                $class = $list_kelas[$key];
                $temp = array(
                    'token' => $token,
                    'class' => $class
                );
                array_push($result_scanning, $temp);
                $id = $id + 2;
                // echo "6 = ";
            } elseif (in_array($list_kata[$id], $list_token)) {
                // jika satu kata yang dicek ada di tabel token_class
                $token = $list_kata[$id];
                $key = array_search($token,$list_token);
                $class = $list_kelas[$key];
                $temp = array(
                    'token' => $token, 
                    'class' => $class);
                array_push($result_scanning, $temp);
                $id++;
                // echo "7 = ";
            } elseif (in_array($list_kata[$id], $token_variabel)) {
                $temp = array(
                    'token' => $list_kata[$id], 
                    'class' => 'VariableIdent');
                array_push($result_scanning, $temp);
                $id++;
                // echo "8 = ";
            } elseif ($list_kata[$id] == "") {
                $id++;
                // echo "9 = ";
            } else {
                if ($id > 1 and ($list_kata[$id - 1] == 'program' || $list_kata[$id - 1] == 'aplikasi')) {
                    $temp = array('token' => $list_kata[$id], 'class' => 'ProgramIdent');
                    array_push($result_scanning, $temp);
                    $id++;
                } else {
                    if ($id > 0) {
                        $last = end($result_scanning);
                    }
                    if (isset($last) and $last['class'] == 'String') {
                        $end = count($result_scanning);
                        $result_scanning[$end - 1]['token'] = $result_scanning[$end - 1]['token'] .
                            " " . $list_kata[$id];
                        $id++;
                    } else {
                        $temp = array('token' => $list_kata[$id], 'class' => 'String');
                        array_push($result_scanning, $temp);
                        $id++;
                    }
                }
                // echo "10 = ";
            }
        }
        // echo "=/ $id /=";
        return $result_scanning;
    }

    //fungsi untuk mengambil data dari tabel token_class
    function get_kelas_token($val)
    {
        $this->db->select('*');
        $this->db->from('token_class');
        $res = $this->db->get()->result();
        $token_class = array();
        if ($val == 'token') {
            foreach ($res as $key => $value) {
                array_push($token_class,$value->token);
            }
        } else {
            foreach ($res as $key => $value) {
                array_push($token_class,$value->class);
            }
        }
        return $token_class;
    }
}
