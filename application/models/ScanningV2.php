<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ScanningV2 extends CI_Model
{

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
        $tandaBacaLama = array('.', ',', "'", '"');
        $tandaBacaBaru = array(' . ', ' , ', " ' ", ' " ');
        $text = str_replace($tandaBacaLama, $tandaBacaBaru, $text);
        $tandaBacaLama = array('(', ')');
        $tandaBacaBaru = array(' ( ', ' ) ');
        $text = str_replace($tandaBacaLama, $tandaBacaBaru, $text);
        $tandaBacaLama = array('+', '-', '*', '/');
        $tandaBacaBaru = array(' + ', ' - ', ' * ', ' / ');
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


        //mengambil token konstanta
        $token_konstanta = [];
        foreach ($list_kata as $id => $value) {
            if ($value == 'konstanta' || $value == 'kons') {
                $i = $id;
                while ($list_kata[$i] != '.' && $i < 20) {
                    if ((in_array($list_kata[$i], $list_token) == false) &&
                        ($list_kata[$i] != '.') &&
                        ($list_kata[$i] != ',') &&
                        ($list_kata[$i] != 'sama') &&
                        ($list_kata[$i] != 'bernilai') &&
                        ($list_kata[$i-1] != 'bernilai') &&
                        ($list_kata[$i-2] != 'sama' && $list_kata[$i-1] != 'dengan') &&
                        ($list_kata[$i] != "")
                    ) {
                        array_push($token_konstanta, $list_kata[$i]);
                    }
                    $i++;   
                }
                break;
            }
        }
        //var_dump($token_konstanta);die();

        //var_dump($token_konstanta);die();

        //mengklasifikasikan kata kedalam class class
        $id = 0;
        $max = count($list_kata);
        $maka_if = 0;
        $tampilkan_ke = 0;
        $tampilkan_atau = 0;
        $tampilkan_bilangan = 0;
        $samadengan_if = 0;
        $dan_if = 0;
        $dan_while = 0;
        
        while ($id < $max) {
            if (is_numeric($list_kata[$id]) || is_numeric(preg_replace('/[,]/', '', $list_kata[$id]))) {
                // jika token yang dicek adalah number
                $temp = array('token' => $list_kata[$id], 'class' => 'Number');
                array_push($result_scanning, $temp);
                $id++;
            } elseif (($list_kata[$id] == '.') || ($list_kata[$id] == ',') || ($list_kata[$id] == '(') || ($list_kata[$id] == ')')) {
                // jika token yang dicek adalah delimiter (titik dan koma)
                if (($list_kata[$id] == '.') || ($list_kata[$id] == ',')){
                  $tampilkan_ke = 0;
                  $tampilkan_atau = 0;
                  $tampilkan_bilangan = 0;
                  $dan_if = 0;
                }

                $temp = array('token' => $list_kata[$id], 'class' => $list_kata[$id]);
                array_push($result_scanning, $temp);
                $id++;
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
            } elseif ($id < $max - 4 and in_array($list_kata[$id] . ' ' . $list_kata[$id + 1] . ' ' . $list_kata[$id + 2] . ' ' . $list_kata[$id + 3], $list_token)) {
                // jika empat kata yang dicek ada di tabel token_class
                $token = $list_kata[$id] . ' ' . $list_kata[$id + 1] . ' ' . $list_kata[$id + 2] . ' ' . $list_kata[$id + 3];
                $key = array_search($token, $list_token);
                $class = $list_kelas[$key];
                $temp = array(
                    'token' => $token,
                    'class' => $class
                );
                array_push($result_scanning, $temp);
                $id = $id + 4;
            } elseif ($id < $max - 3 and in_array($list_kata[$id] . ' ' . $list_kata[$id + 1] . ' ' . $list_kata[$id + 2], $list_token)) {
                // jika tiga kata yang dicek ada di tabel token_class
                $token = $list_kata[$id] . ' ' . $list_kata[$id + 1] . ' ' . $list_kata[$id + 2];
                $key = array_search($token, $list_token);
                $class = $list_kelas[$key];
                $temp = array(
                    'token' => $token,
                    'class' => $class
                );
                array_push($result_scanning, $temp);
                $id = $id + 3;
            } elseif ($id < $max - 2 and in_array($list_kata[$id] . ' ' . $list_kata[$id + 1], $list_token)) {
                // jika dua kata yang dicek ada di tabel token_class
                $token = $list_kata[$id] . ' ' . $list_kata[$id + 1];
                $key = array_search($token, $list_token);
                $class = $list_kelas[$key];

                if ($token == "tampilkan kebawah") {
                  $tampilkan_ke = 1;
                  $tampilkan_atau = 1;
                  $tampilkan_bilangan = 1;
                }
                if ($token == "sama dengan" AND $samadengan_if == 1) {
                    $class = "ArithmeticOperator2";
                }

                $temp = array(
                    'token' => $token,
                    'class' => $class
                );
                array_push($result_scanning, $temp);
                $id = $id + 2;
            } elseif ($id > 1 and ($list_kata[$id] == 'kalimat') and in_array($list_kata[$id - 1], array('tampil', 'tampilkan'))) {
                // masukan token ke result scanning
                $token = $list_kata[$id];
                $key = array_search($token, $list_token);
                $class = $list_kelas[$key];
                $temp = array(
                    'token' => $token,
                    'class' => $class
                );
                array_push($result_scanning, $temp);
                // buat token selanjutnya jadi string sampai ketemu token delimiter (koma atau titik)
                $id++;
                if (isset($list_kata[$id])) {
                    $string = $list_kata[$id];
                    $id++;
                    while ($id < $max and ($list_kata[$id] != '.' and $list_kata[$id] != ',')) {
                        $string = $string . " " . $list_kata[$id];
                        $id++;
                    }
                    $temp = array('token' => $string, 'class' => 'String');
                    array_push($result_scanning, $temp);
                }
                // END buat token selanjutnya jadi string sampai ketemu token delimiter (koma atau titik)
            } elseif (in_array($list_kata[$id], $list_token)) {
                // jika satu kata yang dicek ada di tabel token_class
                $token = $list_kata[$id];

                $key = array_search($token, $list_token);
                $class = $list_kelas[$key];

                // untuk menentukan token maka, apakah additional token atau keyword
                if ($token == "jika") {
                    $maka_if = 1;
                }
                if ($token == "maka" and $maka_if == 1) {
                    $class = "AdditionalToken";
                    $maka_if = 0;
                    $samadengan_if = 0;
                    $dan_if = 0;
                }
                // end untuk menentukan token maka, apakah additional token atau keyword

                if ($token == "jika" OR $token == "jikalau" OR $token == "kalau" OR $token == "apabila" OR $token == "bila" ) {
                    $maka_if = 1;
                    $samadengan_if = 1;
                    $dan_if = 1;
                }             

                if ($token == "tampilkan") {
                    $tampilkan_ke = 1;
                    $tampilkan_atau = 1;
                    $tampilkan_bilangan = 1;
                }
                if ($token == "ke" AND $tampilkan_ke == 1) {
                    $class = "String";
                }
                if ($token == "atau" AND $tampilkan_atau == 1) {
                    $class = "String";
                }
                if ($token == "bilangan" AND $tampilkan_bilangan == 1) {
                    $class = "String";
                }
                if ($token == "dan" AND $dan_if == 1) {
                    $class = "LogicOperator";
                }
                if ($token == "lalu") {
                  $tampilkan_ke = 0;
                  $tampilkan_atau = 0;
                  $tampilkan_bilangan = 0;
                }

                $temp = array(
                  'token' => $token,
                  'class' => $class);

                // untuk menentukan apakah kata 'dan' itu additional token atau logic operator
                if (($token == "dan" and in_array($list_kata[$id - 1], $token_variabel) and in_array($list_kata[$id + 1], $token_variabel))
                    || ($token == "dan" and in_array($list_kata[$id - 1], $token_konstanta) and in_array($list_kata[$id + 1], $token_konstanta))
                    || ($token == "dan" and in_array($list_kata[$id - 1], $token_variabel) and in_array($list_kata[$id + 1], $token_konstanta))
                    || ($token == "dan" and in_array($list_kata[$id - 1], $token_konstanta) and in_array($list_kata[$id + 1], $token_variabel))) {
                    $class = "AdditionalToken";
                } 
                $end_result = count($result_scanning) - 1;
                if ($token == "dan" and count($result_scanning > 0) and $result_scanning[$end_result]['class'] == "Keyword") {
                    $class = "AdditionalToken";
                }
                // end untuk menentukan apakah kata 'dan' itu additional token atau logic operator

                $temp = array(
                    'token' => $token,
                    'class' => $class
                );
                array_push($result_scanning, $temp);

                $id++;
            } elseif (in_array($list_kata[$id], $token_variabel)) {
                $temp = array(
                    'token' => $list_kata[$id],
                    'class' => 'VariableIdent'
                );
                array_push($result_scanning, $temp);
                $id++;
            } elseif (in_array($list_kata[$id], $token_konstanta)) {
                $temp = array(
                    'token' => $list_kata[$id],
                    'class' => 'ConstIdent'
                );
                array_push($result_scanning, $temp);
                $id++;
            } elseif ($list_kata[$id] == "") {
                $id++;
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
            }
        }
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
                array_push($token_class, $value->token);
            }
        } else {
            foreach ($res as $key => $value) {
                array_push($token_class, $value->class);
            }
        }
        return $token_class;
    }
}
