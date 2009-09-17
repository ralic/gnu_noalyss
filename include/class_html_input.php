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

/* $Revision: 2350 $ */

// Copyright Author Dany De Bontridder ddebontridder@yahoo.fr

/*! \file
 * \brief This class is used to create all the HTML INPUT TYPE
 */

/*!
 * \brief class widget This class is used to create all the HTML INPUT TYPE
 *        and some specials which works with javascript like 
 *        js_search.
 *
 * special value 
 *    js_search and js_search_only :you need to add a span widget the name
 *    of the js_* widget + '_label' , the member extra contains cred,deb to 
 *    filter the search of cred of deb of a jrn or contains a string with 
 *    a list of frd_id.
 *    Possible type 
 *    $type 
 *      - TEXT 
 *      - HIDDEN
 *      - BUTTON in this->js you have the javascript code
 *      - SELECT the options are passed via this->value, this array is
 *        build thanks the make_array function, each array (of the
 *        array) aka row must contains a field value and a field label
 *      - PASSWORD
 *      - CHECKBOX
 *      - RADIO
 *      - TEXTAREA
 *      - RICHTEXT
 *      - FILE
 *      - JS_SEARCH_POSTE  call a popup window for searching the account
 *      - JS_SEARCH call a popup window for searching a quickcode or to add one
 *      - JS_SEARCH_ONLY like JS_SEARCH but without adding a quickcode
 *      - JS_SEARCH_CARD_CTRL like js_search_only but the tag to update is given
 *      - SPAN
 *      - JS_TVA        open a popup window for the VAT
 *      - JS_CONCERNED  open a popup window for search a operation, if extra == 0 then
 *                      get the amount thx javascript
 *      - js_DATE show a calendar
 *
 *    For JS_SEARCH_POST,JS_SEARCH or JS_SEARCH_ONLY
 *     - $extra contains 'cred', 'deb', 'all' or a list of fiche_def_ref (frd_id) 
 *           to filter the search/add for the card
 *        
 *     - $extra2 filter on the card parameter, which are given in Avance->journaux menu, 
 *            it is the journal id. If empty, there is no link with a ledger
 *
 */
class HtmlInput {

  var $type;                      /*!<  $type type of the widget */
  var $name;                      /*!<  $name field NAME of the INPUT */    
  var $value;                     /*!<  $value what the INPUT contains */
  var $readOnly;                  /*!<  $readonly true : we cannot change value */
  var $size;                      /*!<  $size size of the input */
  var $selected;                  /*!<  $selected for SELECT RADIO and CHECKBOX the selected value */
  var $table;                     /*!<  $table =1 add the table tag */
  var $label;                     /*!<  $label the question before the input */
  var $disabled;                  /*!<  $disabled poss. value == true or nothing, to disable INPUT*/
  var $extra;                     /*!<  $extra different usage, it depends of the $type */
  var $extra2;                    /*!<  $extra2 different usage,
									it depends of the $type */
  var $javascript;				   /*!< $javascript  is the javascript to add to the widget */
  var $ctrl;						/*!<$ctrl is the control to update (see js_search_card_control) */

  var $tabindex; 
  function __construct($p_name="",$p_value="") {
	$this->name=$p_name;
    $this->readOnly=false;
    $this->size=20;
    $this->width=50;
    $this->heigh=20;
    $this->value=$p_value;
    $this->selected="";
    $this->table=0;
    $this->disabled=false;
    $this->javascript="";
	$this->extra2="all";
  }
  function setReadOnly($p_read) {
    $this->readonly=$p_read;
  }

  //#####################################################################
  /* Debug
   */
  function debug() {
    echo "Type ".$this->type."<br>";
    echo "name ".$this->name."<br>";
    echo "value". $this->value."<br>";
    $readonly=($this->readonly==false)?"false":"true";
    echo "read only".$readonly."<br>";
  }
  static   function submit ($p_name,$p_value,$p_javascript="") {
    
    return '<INPUT TYPE="SUBMIT" NAME="'.$p_name.'" VALUE="'.$p_value.'" '.$p_javascript.'>';
  }
  static   function button ($p_name,$p_value,$p_javascript="") {
    
    return '<INPUT TYPE="button" NAME="'.$p_name.'" ID="'.$p_name.'" VALUE="'.$p_value.'" '.$p_javascript.'>';
  }

  static function reset ($p_value) {
    return '<INPUT TYPE="RESET"  VALUE="'.$p_value.'">';
  }
  static function hidden($p_name,$p_value) {
    return '<INPUT TYPE="hidden" id="'.$p_name.'" NAME="'.$p_name.'" VALUE="'.$p_value.'">';
  }
  /*!\brief create a button with a ref
   *\param $p_label the text
   *\param $p_value the location of the window, the PHPSESSID is added, but not the gDossier
   *\param $p_name the id of the span
   *\return string with htmlcode
   */
  static function button_href($p_label,$p_value,$p_name="") {
    $str='&PHPSESSID='.$_REQUEST['PHPSESSID'];
    $r=sprintf('<span id="%s" class="action"> <A class="mtitle" href="%s">%s</A></span>',
	       $p_name,
	       $p_value.$str,
	       $p_label);
    return $r;
  }
  static function infobulle($p_comment){
    $r='<A HREF="#" style="display:inline;color:black;background-color:yellow;padding-left:4px;padding-right:4px;text-decoration:none;" onmouseover="showBulle(\''.$p_comment.'\')"  onclick="showBulle(\''.$p_comment.'\')" onmouseout="hideBulle(0)">?</A>';
    return $r;
  }
  static function warnbulle($p_comment){
    $r='<A HREF="#" style="display:inline;color:black;background-color:red;padding-left:4px;padding-right:4px;text-decoration:none;" onmouseover="showBulle(\''.$p_comment.'\')"  onclick="showBulle(\''.$p_comment.'\')" onmouseout="hideBulle(0)">XX</A>';
    return $r;
  }

}
