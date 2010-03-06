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
/* $Revision$ */

// Copyright Author Dany De Bontridder ddebontridder@yahoo.fr

/*!\file
 * \brief this file respond to an ajax request 
 * The parameters are
 * - PHPSESSID
 * - gDossier
 * - $op operation the file has to execute
 *
 */
require_once('class_database.php');
require_once ('class_fiche.php');
require_once('class_iradio.php');
require_once('function_javascript.php');
require_once('ac_common.php');
require_once ('class_user.php');

$var=array('PHPSESSID','gDossier');
$cont=0;
/*  check if mandatory parameters are given */
foreach ($var as $v) {
  if ( ! isset ($_REQUEST [$v] ) ) {
    echo "$v is not set ";
    $cont=1;
  }
}
if ( $cont != 0 ) exit();
extract($_GET );
set_language();

$cn=new Database($gDossier);
$user=new User($cn); $user->check(true);$user->check_dossier($gDossier,true);
$html=var_export($_REQUEST,true);
switch($op) 
  { 
    // display new calendar
  case 'cal':
    require_once('class_calendar.php');
    /* others report */
    $cal=new Calendar();
    $cal->set_periode($per);

    $html="";
    $html=$cal->display();
    $html=escape_xml($html);

header('Content-type: text/xml; charset=UTF-8');
echo <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<data>
<code>$html</code>
</data>
EOF;
 break;
 /* remove a cat of document */
  case 'rem_cat_doc':
    require_once('class_document_type.php');
    // if user can not return error message
    if(     $user->check_action(PARCATDOC)==0 ) {
      $html="nok";
      header('Content-type: text/xml; charset=UTF-8');
echo <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<data>
<dtid>$html</dtid>
</data>
EOF;
return;
    }
    // remove the cat if no action 
    $count_md=$cn->get_value('select count(*) from document_modele where md_type=$1',array($dt_id));
   $count_a=$cn->get_value('select count(*) from action_gestion where ag_type=$1',array($dt_id));		      

    if ( $count_md != 0 || $count_a != 0 ) {
      $html="nok";
      header('Content-type: text/xml; charset=UTF-8');
echo <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<data>
<dtid>$html</dtid>
</data>
EOF;
exit;
  }
$cn->exec_sql('delete from document_type where dt_id=$1',array($dt_id));
	 $html=$dt_id;
      header('Content-type: text/xml; charset=UTF-8');
echo <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<data>
<dtid>$html</dtid>
</data>
EOF;
	 return;
	 break;

  }
