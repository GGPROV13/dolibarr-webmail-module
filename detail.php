<?php

/* Copyright (C) 2007-2010 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) ---Put here your own copyright and developer email---
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
 *   	\file       dev/skeletons/skeleton_page.php
 * 		\ingroup    mymodule othermodule1 othermodule2
 * 		\brief      This file is an example of a php page
 * 					Put here some comments
 */
//if (! defined('NOREQUIREUSER'))  define('NOREQUIREUSER','1');
//if (! defined('NOREQUIREDB'))    define('NOREQUIREDB','1');
//if (! defined('NOREQUIRESOC'))   define('NOREQUIRESOC','1');
//if (! defined('NOREQUIRETRAN'))  define('NOREQUIRETRAN','1');
//if (! defined('NOCSRFCHECK'))    define('NOCSRFCHECK','1');
//if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL','1');
//if (! defined('NOREQUIREMENU'))  define('NOREQUIREMENU','1');	// If there is no menu to show
//if (! defined('NOREQUIREHTML'))  define('NOREQUIREHTML','1');	// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX','1');
//if (! defined("NOLOGIN"))        define("NOLOGIN",'1');		// If this page is public (can be called outside logged session)
// Change this following line to use the correct relative path (../, ../../, etc)
$res = 0;
if (!$res && file_exists("../main.inc.php"))
    $res = @include("../main.inc.php");
if (!$res && file_exists("../../main.inc.php"))
    $res = @include("../../main.inc.php");
if (!$res && file_exists("../../../main.inc.php"))
    $res = @include("../../../main.inc.php");
if (!$res && file_exists("../../../dolibarr/htdocs/main.inc.php"))
    $res = @include("../../../dolibarr/htdocs/main.inc.php");     // Used on dev env only
if (!$res && file_exists("../../../../dolibarr/htdocs/main.inc.php"))
    $res = @include("../../../../dolibarr/htdocs/main.inc.php");   // Used on dev env only
if (!$res && file_exists("../../../../../dolibarr/htdocs/main.inc.php"))
    $res = @include("../../../../../dolibarr/htdocs/main.inc.php");   // Used on dev env only
if (!$res)
    die("Include of main fails");
// Change this following line to use the correct relative path from htdocs (do not remove DOL_DOCUMENT_ROOT)
//require_once(DOL_DOCUMENT_ROOT."/../dev/skeleton/skeleton_class.class.php");
// Load traductions files requiredby by page
$langs->load("companies");
$langs->load("other");

// Get parameters
$id = GETPOST('id', 'int');
$action = GETPOST('action', 'alpha');

// Protection if external user
if ($user->societe_id > 0) {
    //accessforbidden();
}



$sql = "SELECT mailbox_imap_login, mailbox_imap_password, mailbox_imap_host, mailbox_imap_port ";
$sql.= " FROM " . MAIN_DB_PREFIX . "usermailboxconfig as u";
$sql.= " WHERE u.fk_user = " . $user->id;

$resql = $db->query($sql);
if ($resql) {
    if ($db->num_rows($resql)) {
        $obj = $db->fetch_object($resql);

        $user->mailbox_imap_login = $obj->mailbox_imap_login;
        $user->mailbox_imap_password = $obj->mailbox_imap_password;
        $user->mailbox_imap_host = $obj->mailbox_imap_host;
        $user->mailbox_imap_port = $obj->mailbox_imap_port;
    }
    $db->free($resql);
}

/* * *************************************************
 * VIEW
 *
 * Put here all code to build page
 * ************************************************** */

llxHeader('', 'Dolibarr Webmail', '');

// Connexion
$mbox = imap_open('{' . $user->mailbox_imap_host . ':' . $user->mailbox_imap_port . '}', $user->mailbox_imap_login, $user->mailbox_imap_password);

if (FALSE === $mbox) {
    $info = FALSE;
    $err = 'La connexion a échoué. Vérifiez vos paramètres!';
} else {
    $uid = $_GET['uid'];
    $headerText = imap_fetchHeader($mbox, $uid, FT_UID);
    $header = imap_rfc822_parse_headers($headerText);

    // REM: Attention s'il y a plusieurs sections
    $corps = trim( utf8_encode( quoted_printable_decode(imap_fetchbody($mbox, $uid, 1, FT_UID))));
}
imap_close($mbox);

print '<table>';
print '<tr><td  width="30%" nowrap><span class="fieldrequired">' . $langs->trans("Rattacher à ") . '</span></td><td>';
$out = '';
if ($conf->use_javascript_ajax)
    $out .= ajax_multiautocompleter('reference_0', array('reference_rowid_0', 'reference_type_element_0'), DOL_URL_ROOT . '/dolimail/core/ajax/reference.php', 'num_ligne=0') . "\n";
$out.= '<input id="reference_0" type="text" name="reference_0" value="' . GETPOST("reference_0");
print $out . '">' . "\n";
print '<input id="reference_rowid_0" type="hidden" name="reference_rowid_0" value="';
print GETPOST("reference_rowid_0");
print '">' . "\n";
print '<input id="reference_type_element_0" type="hidden" name="reference_type_element_0" value="';
print GETPOST("reference_type_element_0");
print '">' . "\n";
print '<input id="reference_fk_socid_0" type="hidden" name="reference_fk_socid_0" value="';
print GETPOST("reference_fk_socid_0");
print '">' . "\n";
print '</td></tr>';
print '</table>';
print '<h2>'.$header->subject.'</h2>';
$from = $header->from;
echo "Message de:" . $from[0]->personal . " [" . $from[0]->mailbox . "@" . $from[0]->host . "]<br /><br />";
echo nl2br($corps);

// End of page
llxFooter();
$db->close();
?>