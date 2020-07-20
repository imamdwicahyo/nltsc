<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ParsingV2 extends CI_Model
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
    var $max_loop = 100000;
    var $key_token = 0;
    var $list_token = array();
    var $max_token_success = 0;

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

        // mendapatkan grammar dari database
        $this->create_grammar();

        // menambahkan grammar hasil dari scanning
        $this->add_grammar_from_scanning($input, 'ProgramIdent', 'PROGRAM_IDENT');
        $this->add_grammar_from_scanning($input, 'VariableIdent', 'IDENT_VAR');
        $this->add_grammar_from_scanning($input, 'ConstIdent', 'IDENT_CONST');
        $this->add_grammar_from_scanning($input, 'String', 'STRING');
        $this->add_grammar_from_scanning($input, 'Number', 'NUMBER');

        // masukan rule(child) dengan parent 'START' pada stack
        $key = array_search("START", $this->grammar_parent); //cari key dari grammar parent
        $arr_rule = $this->add_array_rule(NULL, $this->grammar_child[$key]); //buat array rule baru
        $parsing = $this->create_result_parsing($this->key_token, $arr_rule); //buat info hasil penurunan
        $this->add_to_stack($arr_rule, $this->key_token, array($parsing)); //buat stack baru

        // mulai proses penurunan
        $count_loop = 0;
        while ($this->diterima == 0 and $count_loop < $this->max_loop and count($this->stack) > 0) {
            //get rule
            $arr_rule = $this->stack[$this->end_stack]['rule'];
            $arr_parsing = $this->stack[$this->end_stack]['result_parsing'];
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
                    $parsing = $this->create_result_parsing($this->key_token, $arr_rule); // membuat info hasil penurunan
                    array_push($arr_parsing, $parsing); // memasukan info hasil penurunan ke array parsing
                    $this->stack[$this->end_stack]['result_parsing'] = $arr_parsing; // update array parsing ke yang terbaru
                } else {
                    foreach ($arr_child as $key => $child) {
                        $new_arr_rule = $this->add_array_rule($arr_rule, $child); // membuat array rule baru
                        $new_arr_parsing = $arr_parsing;   // membuat array parsing baru
                        $parsing = $this->create_result_parsing($this->key_token, $new_arr_rule); // membuat info hasil penurunan
                        array_push($new_arr_parsing, $parsing); //memasukan info hasil penurunan ke array parsing baru
                        $this->add_to_stack($new_arr_rule, $this->key_token, $new_arr_parsing); //menambahkan stack baru dengan array rule dan array parsing yang baru
                        $this->end_stack = $this->end_stack + 1; //tambahkan var end_stack dengan angka 1;
                    }
                    $this->end_stack = count($this->stack) - 1; //update var end_stack dengan key array dari stack terakhir
                }
            } else {
                if ($rule_name == $token_name) {
                    $total_list_token = count($this->list_token) - 1;
                    $total_arr_rule = count($arr_rule);

                    if ($total_arr_rule == 1 and $this->key_token == $total_list_token) {
                        // jika ini rule dan token yang terakhir maka string diterima
                        $this->diterima = 1;
                        // echo "$total_arr_rule == 1 dan $this->key_token = $total_list_token <br><br>";
                    } elseif ($total_arr_rule > 1 and $this->key_token < $total_list_token) {
                        // jika rule dan token masih ada maka tetap lakukan pengecekan
                        $this->checking_success();
                    } elseif ($total_arr_rule == 1 and $this->key_token < $total_list_token) {
                        // jika rule habis dan token masih ada maka pengecekan gagal, hapus stack terakhir
                        $this->deleting_last_stack();
                    } elseif ($total_arr_rule > 1 and $this->key_token == $total_list_token) {
                        // jika rule masih ada dan token sudah habis maka pengecekan gagal, hapus stack terakhir
                        $this->deleting_last_stack();
                    } else {
                        echo "KONDISI TIDAK DITEMUKAN = $count_loop";
                        die;
                    }
                } else {
                    $this->deleting_last_stack();
                }
            }

            $this->max_token_success = ($this->max_token_success < $this->key_token) ? $this->key_token : $this->max_token_success; //mendapatkan key_token paling tinggai
            $count_loop++;
        }

        $data = array(
            'diterima' => $this->diterima,
            'loop' => $count_loop,
            'message' => $this->create_message(),
            'result' => ($this->stack != NULL) ? $this->stack[$this->end_stack]['result_parsing'] : "",
            'scanning' => $input,
            // 'stack' => $this->stack,
            // 'token' => $this->list_token,
        );

        $this->restore_variables(); //untuk mengebalikan isi variabel global
        return $data;
    }

    /** Funsi untuk mengembalikan isi variabel global */
    public function restore_variables()
    {
        $this->stack = array();
        $this->end_stack = 0;
        $this->grammar_parent = array();
        $this->grammar_child = array();
        $this->diterima = 0;
        $this->max_loop = 100000;
        $this->key_token = 0;
        $this->list_token = array();
        $this->max_token_success = 0;
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
    public function checking_success()
    {
        $arr_rule = $this->stack[$this->end_stack]['rule']; //get array rule
        array_pop($arr_rule); // deleting last rule
        $this->key_token = $this->key_token + 1;
        $this->stack[$this->end_stack]['rule'] = $arr_rule;
        $this->stack[$this->end_stack]['key_token'] = $this->key_token;
    }

    /** Fungsi untuk menghapus stack terakhir */
    public function deleting_last_stack()
    {
        if (count($this->stack) > 1) {
            array_pop($this->stack);
            $this->end_stack = count($this->stack) - 1;
            $this->key_token = $this->stack[$this->end_stack]['key_token'];
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
    public function add_to_stack($arr_rule, $key_token, $arr_parsing)
    {
        $data = array(
            'key_token' => $key_token,
            'rule' => $arr_rule,
            'result_parsing' => $arr_parsing,
        );
        $this->stack[$this->end_stack] = $data;
    }

    /** Fungsi ini untuk mendapatkan token yang nanti akan dilakukan penurunan.*/
    public function create_list_token($input)
    {
        foreach ($input as $key => $value) {
            if ($value['class'] != 'String') {
                $temp = explode(' ', $value['token']);
                foreach ($temp as $t) {
                    array_push($this->list_token, $t);
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
        $this->db->from('grammar_nl');
        $result_db = $this->db->get()->result();
        foreach ($result_db as $key => $value) {
            array_push($this->grammar_parent, $value->parent);
            array_push($this->grammar_child, $value->child);
        }
    }

    /** Menambahkan grammar yang didapat dari hasil scanning */
    public function add_grammar_from_scanning($scanning, $name_class, $parent_name = NULL)
    {
        $temp_child = array();
        foreach ($scanning as $key => $value) {
            if ($value['class'] == $name_class) {
                array_push($temp_child, preg_replace('/[ ]/', '#', $value['token']));
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
