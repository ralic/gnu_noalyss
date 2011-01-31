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
 * \brief
 */

require_once('class_database.php');
require_once('class_acc_ledger.php');

$gDossier=dossier::id();
$cn=new Database($gDossier);

$user=new User($cn);
$user->Check();
$act=$user->check_dossier($gDossier);
if ( $act=='P')
  {
    redirect("extension.php?".dossier::get(),0);
    exit();
  }
if ( $act=='X')
  {
    echo alert('Accès interdit');
    exit();
  }
$user->can_request(IMPJRN,0);

header('Pragma: public');
header('Content-type: application/csv');
header('Content-Disposition: attachment;filename="histo-export.csv"',FALSE);

$ledger=new Acc_Ledger($cn,0);
list($sql,$where)=$ledger->build_search_sql($_GET);

$order=" order by jr_date_order asc,substring(jr_pj_number,'\\\d+$')::numeric asc ";

$res=$cn->get_array($sql.$order);

printf('"%s";',"Internal");
printf('"%s";',"Journal");
printf('"%s";',"Date");
printf('"%s";',"Echeance");
printf('"%s";',"Piece");
printf('"%s";',"Description");
printf('"%s";',"Note");
printf('"%s"',"Montant");
printf("\r\n");

for ($i=0;$i<count($res);$i++)
  {
    printf('"%s";',$res[$i]['jr_internal']);
    printf('"%s";',$res[$i]['jrn_def_name']);
    printf('"%s";',$res[$i]['jr_date']);
    printf('"%s";',$res[$i]['jr_ech']);
    printf('"%s";',$res[$i]['jr_pj_number']);
    printf('"%s";',$res[$i]['jr_comment']);
    printf('"%s";',$res[$i]['n_text']);
    printf('%s',nb($res[$i]['jr_montant']));
    printf("\r\n");

  }