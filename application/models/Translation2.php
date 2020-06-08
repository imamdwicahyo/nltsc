<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Translation2 extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        //Codeigniter : Write Less Do More
    }

    function removeAdditionalToken($scanning)
    {
        $result = [];
        foreach ($scanning as $key => $value) {
            if ($value['class'] != "AdditionalToken") {
                array_push($result, $value);
            }
        }
        return $result;
    }

    function changeToken($cleanToken)
    {
        //get tabel grammar NL
        $grammarPascal = $this->getGrammarNl();

        //membuat Desain Grammar NL untuk melakukan pengecekan;
        $grammarParent = [];
        $grammarChild = [];
        foreach ($grammarPascal as $key => $listGrammar) {
            $expld = explode('|', $listGrammar->child);
            foreach ($expld as $word) {
                array_push($grammarParent, $listGrammar->parent);
                array_push($grammarChild, $word);
            }
        }

        foreach ($cleanToken as $key => $value) {
            $token = $value['token'];
            $class = $value['class'];

            if ($class == "Keyword" or $class == "ArithmeticOperator") {
                //mendapatkan parent dari token
                $keyParent = array_search($token, $grammarChild);
                $parent = $grammarParent[$keyParent];

                if ($parent == 'PROGRAM_KEYWORD') {
                    $cleanToken[$key]['token'] = 'program';
                } elseif ($parent == 'VAR_KEYWORD') {
                    $cleanToken[$key]['token'] = 'var';
                } elseif ($parent == 'KEYWORD_DATA_TYPE') {
                    $cleanToken[$key]['token'] = ':';
                } elseif ($parent == 'INPUT_OPR') {
                    $cleanToken[$key]['token'] = ':=';
                } elseif ($parent == 'MULTIPLICATION_OPR') {
                    $cleanToken[$key]['token'] = '*';
                } elseif ($parent == 'ADDITION_OPR') {
                    $cleanToken[$key]['token'] = '+';
                } elseif ($parent == 'REDUCTION_OPR') {
                    $cleanToken[$key]['token'] = '-';
                } elseif ($parent == 'DIVISION_OPR') {
                    $cleanToken[$key]['token'] = '/';
                } elseif ($parent == 'MOD_OPR') {
                    $cleanToken[$key]['token'] = 'mod';
                } elseif ($parent == 'DIV_OPR') {
                    $cleanToken[$key]['token'] = 'div';
                } elseif ($parent == 'BIG_OPR') {
                    $cleanToken[$key]['token'] = '>';
                } elseif ($parent == 'SMALL_OPR') {
                    $cleanToken[$key]['token'] = '<';
                } elseif ($parent == 'BIG_SAME_OPR') {
                    $cleanToken[$key]['token'] = '>=';
                } elseif ($parent == 'SMALL_SAME_OPR') {
                    $cleanToken[$key]['token'] = '<=';
                } elseif ($parent == 'EQUALS_OPR') {
                    $cleanToken[$key]['token'] = '=';
                } elseif ($parent == 'NOT_EQUALS_OPR') {
                    $cleanToken[$key]['token'] = '<>';
                } elseif ($parent == 'KEYWORD_WHILE') {
                    $cleanToken[$key]['token'] = 'while';
                } elseif ($parent == 'KEYWORD_FOR') {
                    $cleanToken[$key]['token'] = 'for';
                } elseif ($parent == 'KEYWORD_REPEAT') {
                    $cleanToken[$key]['token'] = 'repeat';
                } elseif ($parent == 'KEYWORD_REPEAT2') {
                    $cleanToken[$key]['token'] = 'until';
                } elseif ($parent == 'LOOP_OPR') {
                    $cleanToken[$key]['token'] = 'do';
                } elseif ($parent == 'BETWEEN') {
                    $cleanToken[$key]['token'] = 'to';
                } elseif ($parent == 'BETWEEN2') {
                    $cleanToken[$key]['token'] = 'downto';
                } elseif ($parent == 'KEYWORD_INPUT') {
                    $cleanToken[$key]['token'] = 'readln';
                } elseif ($parent == 'KEYWORD_OUTPUT') {
                    $cleanToken[$key]['token'] = 'writeln';
                } elseif ($parent == 'INT') {
                    $cleanToken[$key]['token'] = 'integer';
                } elseif ($parent == 'FRACTION') {
                    $cleanToken[$key]['token'] = 'real';
                } elseif ($parent == 'KEYWORD_SQRT') {
                    $cleanToken[$key]['token'] = 'sqrt';
                }
            } elseif ($class == '.') {
                $cleanToken[$key]['token'] = ';';
            } elseif ($class == 'Number') {
                $new_number = str_replace(",", ".", $cleanToken[$key]['token']);
                $cleanToken[$key]['token'] = $new_number;
            }
        }

        return $cleanToken;
    }

    function shortToken($changeToken)
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
                if ((($changeToken[$key - 1]['class'] != 'VariableIdent') && ($changeToken[$key - 1]['class'] != 'Number') && ($changeToken[$key - 1]['class'] != 'String'))) {
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

    function codeInsertion($shortToken)
    {
        //insisialisasi
        $diterima   = 0; //variabel status diterima/tidak
        $endStack   = 0; //pointer untuk menyimpan stack terakhir
        $endList    = 0; //
        $end_result = 0;
        $stack      = [];
        $listStack  = [];
        $result     = [];
        $result_parsing = [];
        $keyToken   = 0;
        $maxLoop    = 0;
        $pesan      = TRUE;
        // $maxLoop = 1741;

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

        // mendapatkan token dan class untuk grammar NL
        $program_ident = array();
        $variable_dent = array();
        $number = array();
        $string = array();
        foreach ($shortToken as $key => $value) {
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

        $tokenCheck = array('begin', 'end.', 'end', 'readln;', "'", ",", ";", '(', ')');
		// $tokenCheck = array('begin', 'end.', 'end', 'readln;', "'", ";", '(', ')');

        //Menghilangkan data yang duplikat
        $program_ident = array_unique($program_ident);
        $variable_dent = array_unique($variable_dent);
        $string = array_unique($string);
        $number = array_unique($number);

        //mendapatkan list grammar parent
        $grammarPascal = $this->getGrammarPascal();
        $listParent = $grammarPascal['listParent'];
        $listChild  = $grammarPascal['listChild'];

        $impld_prog_ident = implode("|", $program_ident);
        $impld_var_ident = implode("|", $variable_dent);
        $impld_ident = $impld_prog_ident . '|' . $impld_var_ident;
        array_push($listParent, 'IDENTIFIER');
        array_push($listChild, $impld_ident);
        // array_push($listParent, 'IDENT_VAR');
        // array_push($listChild, implode("|", $variable_dent));
        array_push($listParent, 'NUMBER');
        array_push($listChild, implode("|", $number));
        array_push($listParent, 'STRING');
        array_push($listChild, implode("|", $string));
        $grammarNL = array(
            'listParent' => $listParent,
            'listChild' => $listChild
        );

        //memasukan stack pertama
        $key = array_search("START", $listParent);
        list($endStack, $stack) = $this->inputCheck($endList, $stack, $listChild[$key]);
        $parsing = array(implode(' ', array_reverse($stack, true)));
        $listStack = $this->addData($listStack, $stack, TRUE, $keyToken, "", $parsing, "");

        $countLoop = 0;
        $word = "";
        $tempResult = [];
        while ($diterima == 0 and $countLoop < $maxLoop and count($listStack) > 0 and count($tokenKata) > 0) {
            //mendapatkan key stack terakhir
            $endList = count($listStack) - 1;
            $word = "";

            // jika status ceknya true maka lakukan pengecekan
            // jika tidak maka kembali ke track sebelumya yang belum di cek
            if ($listStack[$endList]['status'] == TRUE) {
                $stack = $listStack[$endList]['child']; //mendapatkan stack terakhir
                $endStack = sizeof($stack) - 1; //mendapatkan key stack
                $string = $listStack[$endList]['string'];

                //mengecek apakah stack terakhir punya child atau tidak (output bolean)
                $haveChild = $this->haveChild(end($stack), $grammarNL);

                if ($haveChild == TRUE) {
                    //jika stack terakhir memiliki anak lakukan :
                    $child = $this->getChild(end($stack), $grammarNL);
                    $child = explode("|", $child);


                    if (sizeof($child) == 1) {
                        //jika cuma ada 1 anak maka masukan ke stack
                        list($endStack, $listStack[$endList]['child']) = $this->inputCheck($endStack, $listStack[$endList]['child'], $child[0]);
                        $parsing = $listStack[$endList]['parsing'];
                        array_push($parsing, $string . " " . implode(' ', array_reverse($listStack[$endList]['child'])));
                        $listStack[$endList]['action'] = "Added to stack";
                        $listStack[$endList]['parsing'] = $parsing;
                    } else {
                        //jika lebih dari 1 child
                        //maka setiap child dibuatkan stack lalu masukan ke listStack

                        $listStack[$endList]['status'] = false; //ubah status check false karena sudah di cek
                        $countChild = sizeof($child); //mendapatkan banyaknya child
                        $listStack[$endList]['action'] = "Created new list stack ($countChild)";
                        $parsing = $listStack[$endList]['parsing'];
                        $string = $listStack[$endList]['string'];
                        //lakukan perulangan sebanyak banyaknya child lalu buatkan stack lalu masukan kedalam listStack
                        foreach ($child as $key => $value) {
                            $endStack = sizeof($stack) - 1;
                            list($tempEnd, $tempStack) = $this->inputCheck($endStack, $listStack[$endList]['child'], $value);
                            $tempParsing = $parsing;
                            array_push($tempParsing, $string . " " . implode(' ', array_reverse($tempStack)));

                            $listStack = $this->addData($listStack, $tempStack, TRUE, $keyToken, $tokenKata[$keyToken], $tempParsing, $string);
                        }
                    }
                } else {
                    //jika tidak memiliki child lakukan pengecekan
                    if ($tokenKata[$keyToken] == preg_replace('/[#]/', ' ', end($stack))) {
                        // jika yang dicek sama

                        if ($keyToken == (count($tokenKata) - 1) and (count($stack) == 1)) {
                            //jika token dan stack habis maka string diterima
                            $diterima = 1;
                        } elseif ($keyToken == (count($tokenKata) - 1) and (count($stack) > 1)) {
                            //jika token habis dan stack masih ada
                            //maka hapus stack terakhir lalu cek stack sebelumya
                            $resultToken[$end_result] = end($stack);
                            $listStack[$endKeyList]['endToken'] = $end_result + 1;
                            $end_result++;

                            array_pop($listStack);
                            $endList = count($listStack) - 1;
                            $listStack[$endList]['action'] = "Previous list has been delete";
                            $keyToken = $listStack[$endList]['key'];
                        } else {
                            //juka token masih ada dan stack masih ada
                            //maka lakukan ke pengecekan ke stack
                            $word = $tokenKata[$keyToken]; //ini tanda untuk memunculkan pesan

                            $resultToken[$end_result] = end($stack);
                            $listStack[$endKeyList]['endToken'] = $end_result + 1;
                            $end_result++;

                            $keyToken++;
                            array_pop($listStack[$endList]['child']);
                            $listStack[$endList]['action'] = "Checking success, deleting last stack";
                            $listStack[$endList]['check'] = $tokenKata[$keyToken];
                            $listStack[$endList]['key'] = $listStack[$endList]['key'] + 1;
                            $listStack[$endList]['string'] = $listStack[$endList]['string'] . " " . $word;
                            $endStack = $endStack - 1;
                        }
                    } elseif (in_array(end($stack), $tokenCheck)) {
                        $word = end($stack); //ini tanda untuk memunculkan pesan

                        $resultToken[$end_result] = end($stack);
                        $listStack[$endKeyList]['endToken'] = $end_result + 1;
                        $end_result++;

                        array_pop($listStack[$endList]['child']);
                        $listStack[$endList]['action'] = "Checking success, deleting last stack";
                        $listStack[$endList]['check'] = $tokenKata[$keyToken];
                        $listStack[$endList]['key'] = $listStack[$endList]['key'] + 1;
                        $endKeyStack = $endList - 1;
                    } else {
                        //jika yang dicek tidak sama
                        //maka hapus stack terakhir lalu cek stack sebelumnya yang sebelumnya
                        array_pop($listStack);
                        $endList = count($listStack) - 1;
                        $listStack[$endList]['action'] = "Previous list has been delete";
                        $keyToken = $listStack[$endList]['key'];
                        $end_result = $listStack[$endList]['endToken'];
                    }
                }
            } else {
                //jika status ceknya false
                if ((count($listStack) == 1)) {
                    array_pop($listStack);
                } else {
                    array_pop($listStack);
                    $endList = count($listStack) - 1;
                    $keyToken = $listStack[$endList]['key'];
                }
            }


            if (count($stack) == 0) {
                $diterima = 1;
            }

            if ($diterima != 0 && isset($listStack[$endList]) and count($listStack[$endList]['parsing']) >= count($result_parsing)) {
                $result_parsing = $listStack[$endList]['parsing'];
            }

            if ($pesan = TRUE) {
                if ($word != "") {
                    $stringStack = "";
                    $numStack = count($stack) - 1;
                    for ($i = $numStack; $i >= 0; $i--) {
                        $stringStack = $stringStack . $stack[$i] . " ";
                    }
                    foreach ($stack as $key => $value) {
                    }
                    if (($keyToken - 1) > 0) {
                        $word = $tempResult[$keyToken - 2]['word'] . " " . $word;
                    }

                    $temp = array(
                        'word' => $word,
                        'stack' => $stringStack,
                    );

                    $tempResult[$keyToken - 1] = $temp;

                    if (count($tempResult) >= count($result)) {
                        $result = $tempResult;
                    }
                }
            }

            $countLoop++;
        } //end while


        $message = "Ditolak";
        if ($diterima == 1) {
            $message = "Diterima";
        }

        $data = array(
            'numLoop' => $countLoop,
            'maxLoop' => $maxLoop,
            'message' => $message,
            'diterima' => $diterima,
        );


        if ($diterima == 0 AND count($result) > 0) {
            $key = count($result) - 1;
            $data['error_message'] = "Terdapat error setelah kata \"" . $result[$key]['word'] . "\" yaitu pada kata \"" . $tokenKata[$key + 1] . "\"";
        }
        $data['penurunan'] = $result_parsing;
        $data['result']  = $result;
        $data['list'] = $listStack;


        return $data;
    }

    function tidyingToken2($code_insert)
    {
        $jtab = 0;
        $tab = array();
        // &nbsp;
        foreach ($code_insert as $key => $value) {
            if ($value == ';' || $value == 'var' || $value == 'begin' || $value == 'do' || $value == 'repeat' || $value == 'readln;') {
                $code_insert[$key] = $code_insert[$key] . " <br> ";
                // $tab = array();
            }
        }

        $impl_code = implode(" ", $code_insert);
        $expl_code = explode('<br>', $impl_code);
        foreach ($expl_code as $key => $value) {
            $bantu = trim($value);
            if ($bantu == 'var') {
                $expl_code[$key + 1] = " &nbsp; &nbsp; " . $expl_code[$key + 1];
            } elseif ($bantu == 'begin') {
                $expl_code[$key] = implode(" ", $tab) . $expl_code[$key];
                $jtab = $jtab + 2;
                for ($i = 0; $i < $jtab; $i++) {
                    array_push($tab, '&nbsp;');
                }
            } elseif ($bantu == 'end ;') {
                for ($i = 0; $i < 4; $i++) {
                    unset($tab[$i]);
                }
                $expl_code[$key] = implode(" ", $tab) . $expl_code[$key];
            } elseif ($bantu == 'end.') {
                for ($i = 0; $i < 8; $i++) {
                    unset($tab[$i]);
                }
                $expl_code[$key] = implode(" ", $tab) . $expl_code[$key];
            } else {
                $expl_code[$key] = implode(" ", $tab) . $expl_code[$key];
            }
        }

        $sc = implode(' <br> ', $expl_code);
        $sc = str_replace("( ' ", "('", $sc);
        $sc = str_replace(" ' )", "')", $sc);
        return $sc;
    }

    function getGrammarNl()
    {
        $this->db->select('*');
        return $this->db->get("grammar_nl")->result();
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

    function addData($listStack, $tChild, $status, $key, $check, $end_result, $action = NULL)
    {
        $temp = array(
            'child' => $tChild,
            'action' => $action,
            'status' => $status,
            'key' => $key,
            'endToken' => $end_result,
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
