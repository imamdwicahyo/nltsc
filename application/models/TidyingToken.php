<?php
defined('BASEPATH') or exit('No direct script access allowed');

class TidyingToken extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        //Codeigniter : Write Less Do More
    }

    function process($code_insert)
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
}
