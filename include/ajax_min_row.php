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

/**
 * @file
 * @brief
 *
 */
require_once('class_user.php');
require_once('class_dossier.php');
extract($_GET);
/* check the parameters */
foreach ( array('j','ctl') as $a )
{
    if ( ! isset(${$a}) )
    {
        echo "missing $a";
        return;
    }
}

if ( $g_user->check_jrn($_GET['j'])=='X' ) { echo  '{"row":"0"}';exit();}

$row=$cn->get_value('select jrn_deb_max_line from jrn_def where jrn_def_id=$1',array($_GET['j']));

echo '{"row":"'.$row.'"}';

?>