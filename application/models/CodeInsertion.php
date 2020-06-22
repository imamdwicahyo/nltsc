<?php
defined('BASEPATH') or exit('No direct script access allowed');

class CodeInsertion extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        //Codeigniter : Write Less Do More
    }

    function process($shortToken)
    {
        //insisialisasi
        $diterima   = 0; //variabel status diterima/tidak
        $endKeyStack   = 0; //pointer untuk menyimpan stack terakhir
        $endKeyList    = 0; //pointer untuk menyimpan key liststack terakhir
        $endResult   = 0;
        $stack      = [];
        $listStack  = [];
        $result     = [];
        $resultToken = [];
        $keyToken   = 0;
        $maxLoop    = 100000;
        $pesan      = TRUE;

        //mendapatkan clean token dan class
        //yang tidak mengandung AdditionalToken dan koma
        $cleanToken = array();
        $cleanClass = array();
        foreach ($shortToken as $key => $token) {
            if ($token['class'] != 'AdditionalToken' && $token['token'] != ',') {
                array_push($cleanToken, $token['token']);
                array_push($cleanClass, $token['class']);
            }
        }

        $tokenKata = array_values($cleanToken); //mengisi tokenkata dengan cleantoken

        //mendapatkan class class token
        $programIdent   = array();
        $variabelIdent  = array();
        $number         = array();
        $string         = array();
        foreach ($shortToken as $key => $token) {
            if ($token['class'] == 'ProgramIdent') {
                array_push($programIdent, $token['token']);
            } elseif ($token['class'] == 'VariableIdent') {
                array_push($variabelIdent, $token['token']);
            } elseif ($token['class'] == 'String') {
                $str = preg_replace('/[ ]/', '#', $token['token']); //output hasil Filtering
                array_push($string, $str);
            } elseif ($token['class'] == 'Number') {
                array_push($number, $token['token']);
            }
        }

        $tokenCheck = array('begin', 'end.', 'end', 'readln;', "'", ",", ";", '(', ')');
        // $tokenCheck = array('begin', 'end.', 'end', 'readln;', "'", ";", '(', ')');

        //menghilangkan value array yang duplikat
        $programIdent = array_unique($programIdent);
        $variabelIdent = array_unique($variabelIdent);
        $string = array_unique($string);
        $number = array_unique($number);

        // ambil grammar dari database
        $grammarPascal = $this->getGrammarPascal();
        $grammarParent = $grammarPascal['listParent'];
        $grammarChild  = $grammarPascal['listChild'];

        //menambahkan kamus dari input data (dari short token)
        $impld_prog_ident = implode("|", $programIdent);
        $impld_var_ident = implode("|", $variabelIdent);
        $impld_ident = $impld_prog_ident . '|' . $impld_var_ident;
        array_push($grammarParent, 'IDENTIFIER');
        array_push($grammarChild, $impld_ident);
        array_push($grammarParent, 'NUMBER');
        array_push($grammarChild, implode("|", $number));
        array_push($grammarParent, 'STRING');
        array_push($grammarChild, implode("|", $string));
        $grammarPascal = array(
            'listParent' => $grammarParent,
            'listChild' => $grammarChild
        );

        //memasukan stack pertama
        $key = array_search("START", $grammarParent);
        list($endKeyStack, $stack) = $this->inputCheck($endKeyList, $stack, $grammarChild[$key]);
        $listStack = $this->addData($listStack, $stack, TRUE, $keyToken, "", $endResult);

        $countLoop = 0;
        $word = "";
        $tempResult = [];
        while ($diterima == 0 and $countLoop < $maxLoop and count($listStack) > 0 and count($tokenKata) > 0) {
            $endKeyList = count($listStack) - 1; //mendapatkan key List stack terakhir
            $word = ""; //inisialisasi

            // jika status ceknya true maka lakukan pengecekan
            // jika tidak maka kembali ke track sebelumya yang belum di cek
            if ($listStack[$endKeyList]['status'] == TRUE) {
                $stack = $listStack[$endKeyList]['child']; //mendapatkan stack terakhir
                $endKeyStack = sizeof($stack) - 1; //mendapatkan key stack terakhir

                //pengecekan apakah stack terakhir punya child atau tidak (output bolean)
                $haveChild = $this->haveChild(end($stack), $grammarPascal);
                if ($haveChild == TRUE) { //jika stack terakhir memiliki anak lakukan

                    $child = $this->getChild(end($stack), $grammarPascal);
                    $child = explode("|", $child);

                    if (sizeof($child) == 1) {
                        //jika cuma ada 1 anak maka child masukan ke stack
                        list($endKeyStack, $listStack[$endKeyList]['child']) = $this->inputCheck($endKeyStack, $listStack[$endKeyList]['child'], $child[0]);
                        $listStack[$endKeyList]['action'] = "Added child from '" . end($stack) . "'";
                    } else {
                        //jika lebih dari 1 child
                        //maka setiap child dibuatkan stack lalu masukan ke listStack
                        $listStack[$endKeyList]['status'] = false; //ubah status check false karena sudah di cek

                        $countChild = sizeof($child); //mendapatkan banyaknya child
                        $listStack[$endKeyList]['action'] = "Created new list stack ($countChild)";

                        //lakukan perulangan sebanyak banyaknya child lalu buatkan stack lalu masukan kedalam listStack
                        foreach (array_reverse($child, true) as $key => $value) {
                            $endKeyStack = sizeof($stack) - 1;
                            $action = "new Stack from parent = " . end($stack);

                            list($tempEnd, $tempStack) = $this->inputCheck($endKeyStack, $listStack[$endKeyList]['child'], $value);
                            $listStack = $this->addData($listStack, $tempStack, TRUE, $keyToken, $tokenKata[$keyToken]."\n = ".implode(" ",$resultToken), $endResult, $action);
                        }
                    }
                } else { //jika stack terakhir tidak memiliki child lakukan pengecekan

                    if ($tokenKata[$keyToken] == preg_replace('/[#]/', ' ', end($stack))) {
                        // jika yang dicek sama

                        if ($keyToken == (count($tokenKata) - 1) and (count($stack) == 1)) {
                            //jika token dan stack habis maka string diterima
                            $resultToken[$endResult] = end($stack);
                            $diterima = 1;
                        } elseif ($keyToken == (count($tokenKata) - 1) AND (count($stack) == 3) AND $stack[0]='end.' AND $stack[1] = "readln;" AND $stack[2] = "$tokenKata[$keyToken]") {
                            $resultToken[$endResult] = $stack[2];
                            $resultToken[$endResult+1] = $stack[1];
                            $resultToken[$endResult+2] = $stack[0];
                            $diterima = 1;                            
                        } elseif ($keyToken == (count($tokenKata) - 1) and (count($stack) > 1)) {
                            //jika token habis dan stack masih ada
                            //maka hapus stack terakhir lalu cek stack sebelumya
                            $word = $tokenKata[$keyToken]; //ini tanda untuk memunculkan pesan

                            $resultToken[$endResult] = end($stack);
                            $listStack[$endKeyList]['endToken'] = $endResult + 1;
                            $endResult++;

                            // $keyToken++;
                            array_pop($listStack[$endKeyList]['child']);
                            $listStack[$endKeyList]['action'] = "Checking success, deleting token '" . end($stack) . "'";
                            $listStack[$endKeyList]['check'] = NULL;
                            $listStack[$endKeyList]['key'] = $listStack[$endKeyList]['key'] + 1;
                            $endKeyStack = $endKeyStack - 1;
                        } else {
                            //juka token masih ada dan stack masih ada
                            //maka lakukan ke pengecekan ke stack
                            $word = $tokenKata[$keyToken]; //ini tanda untuk memunculkan pesan

                            $resultToken[$endResult] = end($stack);
                            $listStack[$endKeyList]['endToken'] = $endResult + 1;
                            $endResult++;


                            $keyToken++;
                            array_pop($listStack[$endKeyList]['child']);
                            $listStack[$endKeyList]['action'] = "Checking success, deleting token '" . end($stack) . "'";
                            $listStack[$endKeyList]['check'] = $tokenKata[$keyToken]."\n = ".implode(" ",$resultToken);
                            $listStack[$endKeyList]['key'] = $listStack[$endKeyList]['key'] + 1;
                            $endKeyStack = $endKeyStack - 1;
                        }
                    } elseif (in_array(end($stack), $tokenCheck)) {
                        $word = end($stack); //ini tanda untuk memunculkan pesan

                        $resultToken[$endResult] = end($stack);
                        $listStack[$endKeyList]['endToken'] = $endResult + 1;
                        $endResult++;

                        array_pop($listStack[$endKeyList]['child']);
                        $listStack[$endKeyList]['action'] = "Checking success, deleting last stack";
                        $listStack[$endKeyList]['check'] = $tokenKata[$keyToken]."\n = ".implode(" ",$resultToken);
                        $listStack[$endKeyList]['key'] = $listStack[$endKeyList]['key'] + 1;
                        $endKeyStack = $endKeyStack - 1;
                    } else {
                        //jika yang dicek tidak sama
                        //maka hapus stack terakhir lalu cek stack sebelumnya yang sebelumnya
                        array_pop($listStack);
                        $endKeyList = count($listStack) - 1;
                        $listStack[$endKeyList]['action'] = "Previous list has been delete";
                        $keyToken = $listStack[$endKeyList]['key'];
                        $endResult = $listStack[$endKeyList]['endToken'];
                    }
                }
            } else {
                //jika status ceknya false
                if ((count($listStack) == 1)) {
                    array_pop($listStack);
                } else {
                    array_pop($listStack);
                    $endKeyList = count($listStack) - 1;
                    $keyToken = $listStack[$endKeyList]['key'];
                    $endResult = $listStack[$endKeyList]['endToken'];
                }
            }


            if (count($stack) == 0 and count($tokenKata) == $keyToken + 2) {
                $diterima = 1;
            }

            // if ($pesan = TRUE) {
            //   if ($word!="") {
            //     $stringStack = "";
            //     $numStack = count($stack)-1;
            //     for ($i=$numStack; $i >= 0 ; $i--) {
            //       $stringStack = $stringStack.$stack[$i]." ";
            //     }
            //     foreach ($stack as $key => $value) {
            //
            //     }
            //     if (($keyToken-1)>0) {
            //       $word = $tempResult[$keyToken-2]['word']." ".$word;
            //     }
            //
            //     $temp = array(
            //       'word' => $word,
            //       'stack' => $stringStack,
            //     );
            //
            //     $tempResult[$keyToken-1] = $temp;
            //
            //     if (count($tempResult) > count($result)) {
            //       $result = $tempResult;
            //     }
            //   }
            // }

            $countLoop++;
        } //end while
        $date = array('diterima' => $diterima, 'Loop' => $countLoop);
        // var_dump($date);
        // var_dump($countLoop);
        $data = array(
            'num_loop' => $countLoop,
            // 'token' => $tokenKata,
            'countTOken' => count($tokenKata),
            'endTOken' => $keyToken,
            'diterima' => $diterima,
            'result' => $resultToken,
            'list' => array_reverse($listStack, true),
        );
        return $data;
    }

    function getGrammarPascal()
    {
        $result = $this->db->get('grammar_pascal')->result();
        $listParent = [];
        $grammarChild  = [];
        foreach ($result as $key => $value) {
            array_push($listParent, $value->parent);
            array_push($grammarChild, $value->child);
        }
        $data = array(
            'listParent' => $listParent,
            'listChild' => $grammarChild
        );
        return $data;
    }

    function addData($listStack, $tChild, $status, $key, $check, $endResult, $action = NULL)
    {
        $temp = array(
            'child' => $tChild,
            'action' => $action,
            'status' => $status,
            'key' => $key,
            'endToken' => $endResult,
            'check' => "==>> " . $check,
        );
        array_push($listStack, $temp);
        return $listStack;
    }

    function inputCheck($start, $array, $string)
    {
        $expld = explode(" ", $string);
        $size = sizeof($expld);
        for ($i = $size - 1; $i >= 0; $i--) {
            $array[$start] = $expld[$i];
            $start++;
        }
        $endKeyStack = sizeof($array) - 1;
        return array($endKeyStack, $array);
    }

    function haveChild($check, $grammarPascal)
    {
        $listParent = $grammarPascal['listParent'];
        $key  = array_search($check, $listParent);
        if ($key != "") {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function getChild($check, $grammarPascal)
    {
        $listParent = $grammarPascal['listParent'];
        $grammarChild  = $grammarPascal['listChild'];
        $key = array_search($check, $listParent);
        return $grammarChild[$key];
    }
}
