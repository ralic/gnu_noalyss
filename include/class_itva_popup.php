<?php
/*
 *   This file is part of PhpCompta.
 *
 *   PhpCompta is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   PhpCompta is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with PhpCompta; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
/* $Revision: 1615 $ */

// Copyright Author Dany De Bontridder ddebontridder@yahoo.fr

/*!\file
 * \brief Html Input
 */
require_once ('class_ipopup.php');
require_once('class_ibutton.php');
require_once('class_ispan.php');
/**
 *@brief let you choose a TVA in a popup
 *@code
    $a=new IPopup('popup_tva');
    $a->set_title('Choix de la tva');
    echo $a->input();
    $tva=new ITva_Popup("tva1");
    $tva->with_button(true);
    // We can add a label for the code
    $tva->add_label('code');
    $tva->js='onchange="set_tva_label(this);"';
    echo $tva->input();
@endcode
*/
 class ITva_Popup extends HtmlInput
{
  public function __construct($p_name=null) {
    $this->name=$p_name;
    $this->button=true;
  }
  function with_button($p) {
    if ($p == true ) 
      $this->button=true;
    else
      $this->button=false;
  }
  /*!\brief show the html  input of the widget*/
  public function input($p_name=null,$p_value=null)
  {
    $this->name=($p_name==null)?$this->name:$p_name;
    $this->value=($p_value==null)?$this->value:$p_value;
    $this->js=(isset($this->js))?$this->js:"";
    if ( $this->readOnly==true) return $this->display();

    $str='<input type="TEXT" name="%s" value="%s" id="%s" size="3" "%s">';
    $r=sprintf($str,$this->name,$this->value,$this->name,$this->js);
    if ( $this->button==true)
      $r.=$this->dbutton();

    if ( isset($this->code)){
      $r.=$this->code->input();
      $this->set_attribute('jcode',$this->code->name);
      $this->set_attribute('gDossier',dossier::id());
      $this->set_attribute('ctl',$this->name);
      $r.=$this->get_js_attr();
    }
    return $r;

  }
  /**
   *@brief show a button, if it is pushed show a popup to select the need vat
   *@note
   * - a ipopup must be created before with the name popup_tva
   * - the javascript scripts.js must be loaded 
   *@return string with html code
   */
  function dbutton() {
    if( trim($this->name)=='') throw new Exception (_('Le nom ne peut être vide'));
    // button
    $bt=new IButton('bt_'.$this->name);
    $bt->label=_('tva');
    $bt->set_attribute('gDossier',dossier::id());
    $bt->set_attribute('ctl',$this->name);
    $bt->set_attribute('popup','popup_tva');
    if ( isset($this->code))
      $bt->set_attribute('jcode',$this->code->name);
    $bt->javascript='popup_select_tva(this)';
    $r=$bt->input();
    return $r;
  }

  /*!\brief print in html the readonly value of the widget*/
  public function display()
  {
    $r='<input text name="%s" value="%s" id="%s" disabled>';
    $res=sprinf($r,$this->name,$this->value,$this->name);
    return $res;
  }
  public function add_label($p_code) {
    $this->code=new ISpan($p_code);
  }
  static public function test_me()
  {
    $a=new IPopup('popup_tva');
    $a->set_title('Choix de la tva');
    echo $a->input();
    $tva=new ITva_Popup("tva1");
    $tva->with_button(true);
    // We can add a label for the code
    $tva->add_label('code');
    $tva->js='onchange="set_tva_label(this);"';
    echo $tva->input();
    echo $tva->dbutton();
  }
}
