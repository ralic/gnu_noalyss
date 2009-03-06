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
 * \brief Input HTML for the card show buttons, in the file, you have to add JS_SEARCH
 *
 */

  /*!\brief Input HTML for the card show buttons, in the file, you have to add JS_SEARCH
   *
   * extra2 is the label in the button
   * table = 1 then add the <td> between the button and the input text
   */
require_once('class_html_input.php');
class ICard extends HtmlInput
{
  /*!\brief show the html  input of the widget*/
  public function input($p_name=null,$p_value=null)
  {
    $this->name=($p_name==null)?$this->name:$p_name;
    $this->value=($p_value==null)?$this->value:$p_value;
    if ( $this->readOnly==true) return $this->display();
    $l_sessid=$_REQUEST['PHPSESSID'];

    //--
    $l_sessid=$_REQUEST['PHPSESSID'];

    if ( $this->javascript=="") { /* if javascript is empty then we
				     add a default behaviour */
      $this->javascript=sprintf('onBlur="ajaxFid(\'%s\',\'%s\',\'%s\',\'%s\',\'%s\')"',
				$this->name,
				$this->extra, //deb or cred
				$l_sessid,
				'js_search_only',
				'none'
				);
    }
      if ( $this->extra2 == "" ) $this->extra2="QuickCode";
      if ( $this->table==1)
	{
	  $r=sprintf('<TD>
         <INPUT TYPE="button" onClick="SearchCard(\'%s\',\'%s\',\'%s\')" value="%s">
            %s</TD><TD> <INPUT class="input_text"  TYPE="Text"  " '.
		     ' NAME="%s" ID="%s" VALUE="%s" SIZE="8" %s>',
		     $l_sessid,
		     $this->extra,
		     $this->name,
		     $this->extra2,
		     $this->label,
		     $this->name,
		     $this->name,
		     $this->value,
		     $this->javascript
		     );
	}
      else
	{
	  $r=sprintf('
         <INPUT TYPE="button" onClick="SearchCard(\'%s\',\'%s\',\'%s\')" value="%s">
            %s <INPUT TYPE="Text"  style="border:solid 1px blue;" '.
		     ' NAME="%s" ID="%s" VALUE="%s" SIZE="8"  %s">',
		     $l_sessid,
		     $this->extra,
		     $this->name,
		     $this->extra2,
		     $this->label,
		     $this->name,
		     $this->name,
		     $this->value,
		     $this->javascript
		     );
	}
    
    //--    
    return $r;

  }
  /*!\brief print in html the readonly value of the widget*/
  public function display()
  {
    $r=sprintf('         <INPUT TYPE="hidden" NAME="%s" VALUE="%s" SIZE="8">',
	       $this->name,
	       $this->value 
	       );
    return $r;

  }
  static public function test_me()
  {

  }
}
