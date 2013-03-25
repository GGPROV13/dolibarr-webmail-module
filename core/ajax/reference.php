<?php

/* Copyright (C) 2010 Regis Houssin       <regis@dolibarr.fr>
 * Copyright (C) 2011 Laurent Destailleur <eldy@users.sourceforge.net>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *       \file      htdocs/core/ajax/ziptown.php
 *       \ingroup	core
 *       \brief     File to return Ajax response on zipcode or town request
 */
if (!defined('NOTOKENRENEWAL'))
    define('NOTOKENRENEWAL', 1); // Disables token renewal
if (!defined('NOREQUIREMENU'))
    define('NOREQUIREMENU', '1');
if (!defined('NOREQUIREHTML'))
    define('NOREQUIREHTML', '1');
if (!defined('NOREQUIREAJAX'))
    define('NOREQUIREAJAX', '1');
if (!defined('NOREQUIRESOC'))
    define('NOREQUIRESOC', '1');
if (!defined('NOCSRFCHECK'))
    define('NOCSRFCHECK', '1');

require('../../../main.inc.php');



/*
 * View
 */

// Ajout directives pour resoudre bug IE
//header('Cache-Control: Public, must-revalidate');
//header('Pragma: public');
//top_htmlhead("", "", 1);  // Replaced with top_httphead. An ajax page does not need html header.
top_httphead();

//print '<!-- Ajax page called with url '.$_SERVER["PHP_SELF"].'?'.$_SERVER["QUERY_STRING"].' -->'."\n";

dol_syslog("GET is " . join(',', $_GET));
//var_dump($_GET);
// Generation of list of zip-town
if (!empty($_GET['reference_' . $_GET['num_ligne']])) {
    $return_arr = array();

    $reference = $_GET['reference_' . $_GET['num_ligne']] ? $_GET['reference_' . $_GET['num_ligne']] : '';
    // Recherche parmis les societes
    $sql = "SELECT s.rowid as rowid, CONCAT_WS(' - ', s.code_client, s.code_fournisseur) as reference, s.nom as nom_societe, 'societe' as type_element, s.rowid as fk_socid";
    $sql.= " FROM " . MAIN_DB_PREFIX . "societe as s";
    $sql.= " WHERE ";
    $sql.=" (s.code_client LIKE '%" . $db->escape($reference) . "%' OR s.nom LIKE '%" . $db->escape($reference) . "%' OR s.code_fournisseur LIKE '%" . $db->escape($reference) . "%')";
    $sql .= " UNION ";
    // Recherche parmis les projets
    $sql .= "SELECT p.rowid as rowid, CONCAT_WS(' - ', p.ref, p.title) as reference, s.nom as nom_societe, 'projet' as type_element, s.rowid as fk_socid";
    $sql.= " FROM " . MAIN_DB_PREFIX . "projet as p";
    $sql.= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as s ON s.rowid=p.fk_soc";
    $sql.= " WHERE ";
    $sql.=" (p.ref LIKE '%" . $db->escape($reference) . "%' OR p.title LIKE '%" . $db->escape($reference) . "%')";
    $sql .= " UNION ";
    // Recherche parmis les propales clients
    $sql .= "SELECT p.rowid as rowid, CONCAT_WS(' - ', p.ref, p.ref_client) as reference, s.nom as nom_societe, 'propal' as type_element, s.rowid as fk_socid";
    $sql.= " FROM " . MAIN_DB_PREFIX . "propal as p";
    $sql.= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as s ON s.rowid=p.fk_soc";
    $sql.= " WHERE ";
    $sql.=" (p.ref LIKE '%" . $db->escape($reference) . "%' OR p.ref_client LIKE '%" . $db->escape($reference) . "%')";
    $sql .= " UNION ";
    // Recherche parmis les commandes clients
    $sql .= "SELECT c.rowid as rowid, CONCAT_WS(' - ', c.ref, c.ref_client) as reference, s.nom as nom_societe, 'order' as type_element, s.rowid as fk_socid";
    $sql.= " FROM " . MAIN_DB_PREFIX . "commande as c";
    $sql.= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as s ON s.rowid=c.fk_soc";
    $sql.= " WHERE ";
    $sql.=" (c.ref LIKE '%" . $db->escape($reference) . "%' OR c.ref_client LIKE '%" . $db->escape($reference) . "%')";
    $sql .= " UNION ";
    // Recherche parmis les factures clients
    $sql .= "SELECT f.rowid as rowid, CONCAT_WS(' - ', f.facnumber, f.ref_client) as reference, s.nom as nom_societe, 'invoice' as type_element, s.rowid as fk_socid";
    $sql.= " FROM " . MAIN_DB_PREFIX . "facture as f";
    $sql.= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as s ON s.rowid=f.fk_soc";
    $sql.= " WHERE ";
    $sql.=" (f.facnumber LIKE '%" . $db->escape($reference) . "%' OR f.ref_client LIKE '%" . $db->escape($reference) . "%')";
    $sql .= " UNION ";
    // Recherche parmis les commandes fournisseurs
    $sql .= "SELECT c.rowid as rowid, CONCAT_WS(' - ', c.ref, c.ref_supplier) as reference, s.nom as nom_societe, 'order_supplier' as type_element, s.rowid as fk_socid";
    $sql.= " FROM " . MAIN_DB_PREFIX . "commande_fournisseur as c";
    $sql.= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as s ON s.rowid=c.fk_soc";
    $sql.= " WHERE ";
    $sql.=" (c.ref LIKE '%" . $db->escape($reference) . "%' OR c.ref_supplier LIKE '%" . $db->escape($reference) . "%')";
    $sql .= " UNION ";
    // Recherche parmis les factures fournisseurs
    $sql .= "SELECT f.rowid as rowid, f.facnumber as reference, s.nom as nom_societe, 'invoice_supplier' as type_element, s.rowid as fk_socid";
    $sql.= " FROM " . MAIN_DB_PREFIX . "facture_fourn as f";
    $sql.= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as s ON s.rowid=f.fk_soc";
    $sql.= " WHERE ";
    $sql.=" (f.facnumber LIKE '%" . $db->escape($reference) . "%')";
    $sql.= " ORDER BY reference, nom_societe";
    $sql.= $db->plimit(50); // Avoid pb with bad criteria
    //print $sql;
    $resql = $db->query($sql);
    //var_dump($db);
    if ($resql) {
        while ($row = $db->fetch_array($resql)) {
            $row_array['label'] = $row['reference'] . ' / ' . $row['nom_societe'];
            $row_array['value'] = $row['reference'];
            $row_array['reference_rowid_' . $_GET['num_ligne']] = $row['rowid'];
            $row_array['reference_type_element_' . $_GET['num_ligne']] = $row['type_element'];
            $row_array['reference_fk_socid_' . $_GET['num_ligne']] = $row['fk_socid'];

            array_push($return_arr, $row_array);
        }
    }

    echo json_encode($return_arr);
} else {
    
}
?>
