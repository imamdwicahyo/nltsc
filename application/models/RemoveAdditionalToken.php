<?php
defined('BASEPATH') or exit('No direct script access allowed');

class RemoveAdditionalToken extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        //Codeigniter : Write Less Do More
    }

    function process($scanning)
    {
        $result = [];
        foreach ($scanning as $key => $value) {
            if ($value['class'] != "AdditionalToken") {
                array_push($result, $value);
            }
        }
        return $result;
    }
}
