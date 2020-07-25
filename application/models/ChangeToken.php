<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ChangeToken extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        //Codeigniter : Write Less Do More
    }

    var $array_change = array(
        'PROGRAM_KEYWORD' => 'program',
        'VAR_KEYWORD' => 'var',
        'CONST_KEYWORD' => 'const',
        'KEYWORD_DATA_TYPE' => ':',
        'INPUT_OPR' => ':=',
        'MULTIPLICATION_OPR' => '*',
        'ADDITION_OPR' => '+',
        'REDUCTION_OPR' => '-',
        'DIVISION_OPR' => '/',
        'MOD_OPR' => 'mod',
        'DIV_OPR' => 'div',
        'BIG_OPR' => '>',
        'SMALL_OPR' => '<',
        'BIG_SAME_OPR' => '>=',
        'SMALL_SAME_OPR' => '<=',        
        'NOT_EQUALS_OPR' => '<>',
        'KEYWORD_WHILE' => 'while',
        'KEYWORD_FOR' => 'for',
        'KEYWORD_REPEAT' => 'repeat',
        'KEYWORD_REPEAT2' => 'until',
        'LOOP_OPR' => 'do',
        'BETWEEN' => 'to',
        'BETWEEN2' => 'downto',
        'KEYWORD_INPUT' => 'readln',
        'KEYWORD_OUTPUT' => 'writeln',
        'INT' => 'integer',
        'FRACTION' => 'real',
        'KEYWORD_SQRT' => 'sqrt',
        'KEYWORD_ABS' => 'abs',
        'KEYWORD_OUTPUT2' => 'write',
        'KEYWORD_IF' => 'if',
        'KEYWORD_ELSE' => 'else',
        'OR' => 'or',
        'KEYWORD_EXP' => 'exp',
        'KEYWORD_ARCTAN' => 'arctan',
        'KEYWORD_LOG' => 'ln',
        'KEYWORD_ROUND' => 'round',
        'KEYWORD_SQR' => 'sqr',
        'KEYWORD_CASE' => 'case',
        'CHAR' => 'char',
		'END_LOOP' => 'end',
		'BOOLEAN' => 'boolean',
		'BOOLEAN_TRUE' => 'true',
		'BOOLEAN_FALSE' => 'false',
		'EQUALS_OPR' => '=',
    );

    function process($cleanToken)
    {
        //get tabel grammar NL
        $grammar_nl = $this->get_grammar_NL();

        //membuat Desain Grammar NL untuk melakukan pengecekan;
        $grammar_parent = [];
        $grammar_child  = [];
        foreach ($grammar_nl as $key => $listGrammar) {
            $expld = explode('|', $listGrammar->child);
            foreach ($expld as $word) {
                array_push($grammar_parent, $listGrammar->parent);
                array_push($grammar_child, $word);
            }
        }

        $jika_samadengan = 0;
       //var_dump($grammar_child);die();
        //var_dump($cleanToken[0]['token']);die();


        foreach ($cleanToken as $key => $value) {
            $token = $value['token'];
            $class = $value['class'];
            if ($token == 'jika') {
              $jika_samadengan = 1;
            }


            if ($class == "Keyword" or $class == "ArithmeticOperator" or $class == "KeywordElse" or $class == "KeywordIf") {
                //mendapatkan parent dari token
                $keyParent = array_search($token, $grammar_child);
                $parent = $grammar_parent[$keyParent];

                //mengubah inputoperator jika sedang inisialisasi constanta
                if (isset($this->array_change[$parent])) {
                    if ($key>0 && $cleanToken[$key-1]['class']== "ConstIdent") {
                        $cleanToken[$key]['token'] = "=";
                    } else {
                    $cleanToken[$key]['token'] = $this->array_change[$parent];
                    }
                }
              } elseif ($class == "LogicOperator") {
             switch ($token) {
                 case 'dan':
                     $cleanToken[$key]['token'] = 'and';
                     break;
                 case 'atau':
                     $cleanToken[$key]['token'] = 'or';
                     break;
                 default:
                     $cleanToken[$key]['token'] = $token;
                     break;
             }
            } elseif ($class == "ArithmeticOperator2") {
              $cleanToken[$key]['token'] = '=';
              $jika_samadengan = 0;
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
