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
/* $Revision: 4267 $ */
// Copyright Author Dany De Bontridder ddebontridder@yahoo.fr
require_once("constant.php");
require_once('class_database.php');
require_once("class_acc_parm_code.php");

require_once('class_fiche.php');
require_once('class_acc_account_ledger.php');
require_once('user_common.php');
/*! \file
 * \brief Derived from class fiche Administration are a specific kind of card
 *        concerned only by official (or not) administration
 */
/*!
 * \brief  class  admin are a specific kind of card
 */

// Use the view vw_supplier
//
class Bank extends Fiche
{

    var $name;        /*!< $name name of the company */
    var $street;      /*!< $street Street */
    var $country;     /*!< $country Country */
    var $cp;          /*!< $cp Zip code */
    var $vat_number;  /*!< $vat_number vat number */

    /*! \brief Constructor
    /* only a db connection is needed */
    function __construct($p_cn,$p_id=0)
    {
        $this->fiche_def_ref=FICHE_TYPE_FIN;
        parent::__construct($p_cn,$p_id) ;
    }



}

?>