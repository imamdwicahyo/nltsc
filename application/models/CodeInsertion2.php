<?php
defined('BASEPATH') or exit('No direct script access allowed');

class CodeInsertion2 extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    // inisialisasi untuk tumpukan yang isinya rule grammar
    var $stack = array();
    var $end_stack = 0;

    // inisialisasi grammar
    var $grammar_parent = array();
    var $grammar_child = array();

    // inisialisasi variabel lainnya
    var $diterima = 0;
    var $max_loop = 500000;
    var $key_token = 0;
    var $end_result = 0;
    var $list_token = array();
    var $result = array();
    var $max_token_success = 0;
    var $max_count_success = 0;
    // var $token_specific = array('begin', 'end.', 'end', 'readln;', "'", ",", ";", '(', ')');

    //token yang tidak dilakukan pengecekan (hanya ada di pascal)
    var $token_specific = array('begin', 'end.', 'end', 'readln;', "'", ",", ";", '(', ')', 'then');

    var $temp_result = array();

    /**
     * Fungsi process adalah fungsi utama dari tahap parsing
     * fungsi ini untuk mengecek kesesuaian token yang diinputkan oleh user dengan grammar NL yang ada didatabase
     * INPUT    = Array hasil scanning
     * OUTPUT   = info diterima/ditolak, hasil penurunan parsing, hasil scanning, pesan
     */
    public function Process($input)
    {
        // membuat list token hasil dari scanning
        $this->create_list_token($input);

        //var_dump($this->list_token);die;
        // mendapatkan grammar dari database
        $this->create_grammar();

        // menambahkan grammar hasil dari scanning
        $this->add_grammar_from_scanning($input, 'ProgramIdent,VariableIdent', 'IDENTIFIER');
        $this->add_grammar_from_scanning($input, 'String', 'STRING');
        $this->add_grammar_from_scanning($input, 'Number', 'NUMBER');

        // masukan rule(child) dengan parent 'START' pada stack
        $key = array_search("PROGRAM_MODULE", $this->grammar_parent); //cari key dari grammar parent
        $arr_rule = $this->add_array_rule(NULL, $this->grammar_child[$key]); //buat array rule baru
        $this->add_to_stack($arr_rule, $this->key_token, $this->end_result); //buat stack baru

        // mulai proses penurunan
        $count_loop = 0;
        $mesage = "";
        while ($this->diterima == 0 and $count_loop < $this->max_loop and count($this->stack) > 0) {
            //get rule
            $arr_rule = $this->stack[$this->end_stack]['rule'];
            $end_rule   = count($arr_rule) - 1;
            $rule_name  = $arr_rule[$end_rule];
            $token_name = $this->list_token[$this->key_token];

            $have_child = $this->check_child($rule_name);
            if ($have_child == TRUE) {
                // jika rule yang dicek masih mempunyai child, maka turunkan dulu dari grammarnya
                $child = $this->get_child($rule_name);
                $arr_child = explode("|", $child);
                $arr_child = array_reverse($arr_child);

                if (sizeof($arr_child) == 1) {
                    // turunkan rule lalu masukan ke arr_rule
                    $arr_rule = $this->add_array_rule($arr_rule, $child); // memasukan child ke array rule
                    $this->stack[$this->end_stack]['rule'] = $arr_rule; // ubah array rule lama ke array rule yang baru di stack
                    $mesage = "1";
                } else {
                    foreach ($arr_child as $key => $child) {
                        $new_arr_rule = $this->add_array_rule($arr_rule, $child); // membuat array rule baru
                        $this->add_to_stack($new_arr_rule, $this->key_token, $this->end_result); //menambahkan stack baru dengan array rule dan array parsing yang baru
                        $this->end_stack = $this->end_stack + 1; //tambahkan var end_stack dengan angka 1;
                    }
                    $this->end_stack = count($this->stack) - 1; //update var end_stack dengan key array dari stack terakhir
                    $mesage = "2";
                }
            } else {
                $arr_rule = $this->stack[$this->end_stack]['rule'];
                $total_list_token = count($this->list_token) - 1;
                $total_arr_rule = count($arr_rule);

                if ($rule_name == $token_name) {

                    if ($total_arr_rule == 1 and $this->key_token == $total_list_token) {
                        // jika ini rule dan token yang terakhir maka string diterima
                        $this->diterima = 1;
                        $mesage = "5";
                        // echo "$total_arr_rule == 1 dan $this->key_token = $total_list_token <br><br>";
                    } elseif ($total_arr_rule > 1 and $this->key_token < $total_list_token) {
                        // jika rule dan token masih ada maka tetap lakukan pengecekan
                        $this->checking_success($token_name);
                        $mesage = "6";
                    } elseif ($total_arr_rule == 1 and $this->key_token < $total_list_token) {
                        // jika rule habis dan token masih ada maka pengecekan gagal, hapus stack terakhir
                        $this->deleting_last_stack();
                        $mesage = "7";
                    } elseif ($total_arr_rule > 1 and $this->key_token == $total_list_token) {
                        // jika rule masih ada dan token sudah habis maka pengecekan gagal, hapus stack terakhir
                        $this->deleting_last_stack();
                        $mesage = "8";
                    } else {
                        echo "KONDISI TIDAK DITEMUKAN = $count_loop";
                        die;
                    }
                } elseif (in_array($rule_name, $this->token_specific)) {
                    $this->checking_specific_token($rule_name, $token_name);
                } else {
                    $this->deleting_last_stack();
                    $mesage = "9";
                }

                // pengecekan jika token sudah habis dan rule masih ada
                if ($this->stack != NULL) {
                    $arr_rule = $this->stack[$this->end_stack]['rule'];
                    if ($this->key_token >= $total_list_token) {
                        $this->diterima = 1;
                        foreach ($arr_rule as $key => $value) {
                            if (!in_array($value, $this->token_specific)) {
                                $this->diterima = 0;
                            }
                        }
                        if ($this->diterima != 1) {
                            $this->deleting_last_stack();
                        } else {
                            $end_rule = count($arr_rule) - 1;
                            for ($i = $end_rule; $i >= 0; $i--) {
                                $this->result[$this->end_result] = $arr_rule[$i];
                                $this->end_result = $this->end_result + 1;
                            }
                        }
                    }

                    if (count($arr_rule) < 1 and $this->key_token <= $total_list_token) {
                        $this->deleting_last_stack();
                    }
                }
            }

            $this->max_token_success = ($this->max_token_success < $this->key_token) ? $this->key_token : $this->max_token_success; //mendapatkan key_token paling tinggai
            if (count($this->result) >  count($this->temp_result)) {
                $this->temp_result = $this->result;
            }
            $count_loop++;
        }

        $data = array(
            'diterima' => $this->diterima,
            'loop' => $count_loop,
            'message' => $this->create_message(),
            'result' => $this->result,
            'temp' => $this->temp_result,
            // 'token' => $this->list_token,
            // 'stack' => $this->stack,
        );

        $this->restore_variables(); //mengembalikan isi variabel ke semula
        return $data;
    }

    /** Fungsi untuk mengembalikan isi variabel ke semula */
    public function restore_variables()
    {
        $this->stack = array();
        $this->end_stack = 0;
        $this->grammar_parent = array();
        $this->grammar_child = array();
        $this->diterima = 0;
        $this->max_loop = 500000;
        $this->key_token = 0;
        $this->end_result = 0;
        $this->list_token = array();
        $this->result = array();
        $this->max_token_success = 0;
        $this->max_count_success = 0;
    }

    /** Fungsi ketika token dan rule sama */
    public function checking_specific_token($rule_name, $token_name)
    {
        //update rule
        $arr_rule = $this->stack[$this->end_stack]['rule'];
        array_pop($arr_rule);
        $this->stack[$this->end_stack]['rule'] = $arr_rule;

        //update result
        $this->result[$this->end_result] = $rule_name;
        $this->end_result = $this->end_result + 1;
        $this->stack[$this->end_stack]['end_result'] = $this->end_result;
    }

    /** Fungsi untuk menampilkan pesan eror */
    public function create_message()
    {
        if ($this->diterima == 0) {
            $text = "";
            for ($i = 0; $i <= $this->max_token_success; $i++) {
                $text = $text . " " . $this->list_token[$i];
            }
            return "Message: Terjadi kesalahan kata " . $this->list_token[$this->max_token_success] . ". Kalimat : \"$text.......\"";
        } else {
            return implode(" ", $this->list_token);
        }
    }

    /** Fungsi untuk mendapatkan informasi penurunan grammar NL */
    public function create_result_parsing($key_token, $arr_rule)
    {
        if ($key_token > 0) {
            $text = "";
            for ($i = 0; $i < $key_token; $i++) {
                $text = $text . $this->list_token[$i] . " ";
            }
            $rule = implode(" ", array_reverse($arr_rule));
            return $text . $rule;
        } else {
            return implode(" ", array_reverse($arr_rule));
        }
    }

    /** Fungsi ketika token dan rule sama */
    public function checking_success($token_name)
    {
        $arr_rule = $this->stack[$this->end_stack]['rule']; //get array rule
        array_pop($arr_rule); // deleting last rule
        $this->result[$this->end_result] = str_replace("#", " ", $token_name);
        $this->key_token = $this->key_token + 1;
        $this->end_result = $this->end_result + 1;
        $this->stack[$this->end_stack]['rule'] = $arr_rule;
        $this->stack[$this->end_stack]['key_token'] = $this->key_token;
        $this->stack[$this->end_stack]['end_result'] = $this->end_result;
    }

    /** Fungsi untuk menghapus stack terakhir */
    public function deleting_last_stack()
    {
        if (count($this->stack) > 1) {
            array_pop($this->stack);
            $this->end_stack = count($this->stack) - 1;
            $this->key_token = $this->stack[$this->end_stack]['key_token'];
            $this->end_result = $this->stack[$this->end_stack]['end_result'];
        } else {
            array_pop($this->stack);
        }
    }

    /** Fungsi untuk manambahkan child ke array rule */
    public function add_array_rule($old_array, $child)
    {
        $new_arr_rule = explode(" ", $child);
        $new_arr_rule = array_reverse($new_arr_rule);

        if ($old_array == NULL) {
            return $new_arr_rule;
        } else {
            $key = count($old_array) - 1;
            foreach ($new_arr_rule as $value) {
                $old_array[$key] = $value;
                $key++;
            }
            return $old_array;
        }
    }

    /** Fungsi untuk mendapatkan child by parent */
    public function get_child($rule_name)
    {
        $key = array_search($rule_name, $this->grammar_parent);
        return $this->grammar_child[$key];
    }

    /** Fungsi untuk mengecek apakah rule punya child atau tidak */
    public function check_child($rule_name)
    {
        $key = array_search($rule_name, $this->grammar_parent);
        if ($key != "") {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /** Fungsu untuk menambahkan stack baru */
    public function add_to_stack($arr_rule, $key_token, $end)
    {
        $data = array(
            'key_token' => $key_token,
            'end_result' => $end,
            'rule' => $arr_rule,
        );
        $this->stack[$this->end_stack] = $data;
    }

    /** Fungsi ini untuk mendapatkan token yang nanti akan dilakukan penurunan.*/
    public function create_list_token($input)
    {
        foreach ($input as $key => $value) {
            if ($value['class'] != 'String') {
                if ($value['class'] != ',') {
                    $temp = explode(' ', $value['token']);
                    foreach ($temp as $t) {
                        array_push($this->list_token, $t);
                    }
                }
            } else {
                $str = preg_replace('/[ ]/', '#', $value['token']);
                array_push($this->list_token, $str);
            }
        }
    }

    /** Fungsi untuk mendapatkan grammar NL dari database */
    public function create_grammar()
    {
        $this->db->select('parent,child');
        $this->db->from('grammar_pascal');
        $result_db = $this->db->get()->result();
        foreach ($result_db as $key => $value) {
            array_push($this->grammar_parent, $value->parent);
            array_push($this->grammar_child, $value->child);
        }
    }

    /** Menambahkan grammar yang didapat dari hasil scanning */
    public function add_grammar_from_scanning($scanning, $class, $parent_name = NULL)
    {

        $temp_child = array();
        $list_name_class = explode(",", $class);
        //ambil programident dan varident
        foreach ($list_name_class as $key => $name_class) {
            foreach ($scanning as $key => $value) {
                if ($value['class'] == $name_class) {
                    array_push($temp_child, preg_replace('/[ ]/', '#', $value['token']));
                }
            }
        }
        //pastikan tidak ada token yang sama
        $temp_child = array_unique($temp_child);

        //get nama parent
        $name_class = ($parent_name != NULL) ? $parent_name : $name_class;

        // masukan ke grammar
        array_push($this->grammar_parent, $name_class);
        array_push($this->grammar_child, implode("|", $temp_child));
    }

    /** END PARSING */
}
