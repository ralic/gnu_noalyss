<?php
/*
 *   This file is part of NOALYSS.
 *
 *   NOALYSS is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   NOALYSS is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with NOALYSS; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// Copyright 2015 Author Dany De Bontridder danydb@aevalys.eu
/**
 * @file
 * @brief display a box containing last actions
 */
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');
if ( $op == 'action_show')
{
    /** 
     * display action
     */
    require_once NOALYSS_INCLUDE.'/class_follow_up.php';
    $gestion=new Follow_Up($cn);
    $array=$gestion->get_last(25);
    $len_array=count($array);
    require_once NOALYSS_INCLUDE.'/template/action_show.php';
}