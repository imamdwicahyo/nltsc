<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ChangeToken extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        //Codeigniter : Write Less Do More
    }

    function process($cleanToken)
    {
        //get tabel grammar NL
        $grammar_nl = $this->get_grammar_NL();

        //membuat Desain Grammar NL untuk melakukan pengecekan;
        $grammarParent = [];
        $grammarChild = [];
        foreach ($grammar_nl as $key => $listGrammar) {
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

    function get_grammar_NL()
    {
        $this->db->select('*');
        return $this->db->get("grammar_nl")->result();
    }
}
