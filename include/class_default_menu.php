<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class_default_menu
 *
 * @author dany
 */
require_once 'class_default_menu_sql.php';
require_once 'class_itext.php';

class Default_Menu
{

    /**
     * $a_menu_def is an array of Default_Menu_SQL
     */
    private $a_menu_def;

    /**
     * Possible value
     */
    private $code;

    function __construct()
    {
        global $cn;
        $menu = new Default_Menu_SQL($cn);
        $ret = $menu->seek();
        for ($i = 0; $i < Database::num_row($ret); $i++)
        {
            $tmenu = $menu->next($ret, $i);
            $idx = $tmenu->getp('md_code');
            $this->a_menu_def[$idx] = $tmenu->getp('me_code');
        }
        $this->code = explode(',', 'code_follow,code_invoice');
    }

    function input_value()
    {
        $code_invoice = new IText('code_invoice', $this->a_menu_def['code_invoice']);
        $code_follow = new IText('code_follow', $this->a_menu_def['code_follow']);
        echo '<p>' . _('Code pour création facture depuis gestion') . $code_invoice->input() . '</p>';
        echo '<p>' . _('Code pour appel gestion') . $code_follow->input() . '</p>';
    }

    private function check_code($p_string)
    {
        global $cn;
        $count = $cn->get_value('select count(*) from v_menu_description_favori where '
                . 'code = $1', array($p_string));
        if ($count == 0)
        {
            throw new Exception('code_inexistant');
        }
    }

    function verify()
    {
        foreach ($this->code as $code)
        {
            $this->check_code($this->a_menu_def[$code]);
        }
    }

    function set($p_string, $p_value)
    {
        if (in_array($p_string, $this->code) == false)
        {
            throw new Exception("code_invalid");
        }
        $this->a_menu_def[$p_string] = $p_value;
    }
    function get ($p_string)
    {
        return $this->a_menu_def[$p_string];
    }

    function save()
    {
        global $cn;
        try
        {
            $this->verify();
            foreach ($this->code as $key => $value)
            {
                $cn->exec_sql('update menu_default set me_code=$1 where
                        md_code =$2', array($value,$this->a_menu_def[$value]));
            }
        } catch (Exception $e)
        {
            $e->getTraceAsString();
            throw $e;
        }
    }

    static function test_me()
    {
        global $cn, $g_user, $g_succeed, $g_failed;

        echo h2('Constructor', '');
        $a = new Default_Menu();
        echo $g_succeed . 'constructor';
        if (count($a->a_menu_def) != 2)
            echo $g_failed;
        else
            echo $g_succeed;
        echo h2("input_value", "");
        $a->input_value();
        echo h2('verify');
        $a->verify();
        try {
            echo h2('Verify must failed');
            $a->set('code_follow', 'MEMNU/MEMEM/');
            $a->verify();   
        } catch (Exception $e) {
            echo $g_succeed. " OK ";
        }
        echo h2('Verify must succeed');
        try {
            $a->set('code_follow', 'GESTION/FOLLOW');
            $a->verify();
            echo $g_succeed. " OK ";
        } catch (Exception $e)
        {
            echo $g_failed."NOK";
        }
        echo h2('Save');
        $a->save();
        echo h2('GET');
        echo ( assert($a->get('code_follow')=='GESTION/FOLLOW') )?$g_succeed.$a->get('code_follow'):$g_failed.$a->get('code_follow');
        echo ( assert($a->get('code_invoice')=='COMPTA/VENMENU/VEN') )?$g_succeed.$a->get('code_invoice'):$g_failed.$a->get('code_invoice');
        echo $a->get('code_invoice');
    }

}