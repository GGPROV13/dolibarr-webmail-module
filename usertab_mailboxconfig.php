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
require_once(DOL_DOCUMENT_ROOT . '/core/lib/usergroups.lib.php');
require_once(DOL_DOCUMENT_ROOT . '/user/class/user.class.php');
require_once(dirname(__FILE__) . '/class/usermailboxconfig.class.php');


$id = GETPOST('id', 'int');
$action = GETPOST('action');

$langs->load("companies");
$langs->load("members");
$langs->load("bills");
$langs->load("users");
$langs->load("dolimail@dolimail");

$fuser = new User($db);
$fuser->fetch($id);

$mailboxconfig = new Usermailboxconfig($db);
$mailboxconfig->fetch_from_user($id);

// If user is not user read and no permission to read other users, we stop
if (($fuser->id != $user->id) && (!$user->rights->user->user->lire))
    accessforbidden();

// Security check
$socid = 0;
if ($user->societe_id > 0)
    $socid = $user->societe_id;
$feature2 = (($socid && $user->rights->user->self->creer) ? '' : 'user');
if ($user->id == $id)
    $feature2 = ''; // A user can always read its own card
$result = restrictedArea($user, 'user', $id, '&user', $feature2);

/* * *************************************************************************** */
/*                     Actions                                                */
/* * *************************************************************************** */

if ($action == 'update' && $user->rights->user->user->creer && !GETPOST("cancel")) {
    $db->begin();

    $mailboxconfig->id = GETPOST("mailboxuserid");
    $mailboxconfig->fk_user = GETPOST("id");
    $mailboxconfig->mailbox_imap_login = GETPOST("mailbox_imap_login");
    $mailboxconfig->mailbox_imap_password = GETPOST("mailbox_imap_password");
    $mailboxconfig->mailbox_imap_host = GETPOST("mailbox_imap_host");
    $mailboxconfig->mailbox_imap_port = GETPOST("mailbox_imap_port");
    $mailboxconfig->mailbox_imap_ssl = GETPOST("mailbox_imap_ssl");

    if ($mailboxconfig->id > 0)
        $res = $mailboxconfig->update($user);
    else
        $res = $mailboxconfig->create($user);

    if ($res < 0) {
        $mesg = '<div class="error">' . $adh->error . '</div>';
        $db->rollback();
    } else {
        $db->commit();
    }
}

$fuser->mailbox_id = $mailboxconfig->id;
$fuser->mailbox_imap_login = $mailboxconfig->mailbox_imap_login;
$fuser->mailbox_imap_password = $mailboxconfig->mailbox_imap_password;
$fuser->mailbox_imap_host = $mailboxconfig->mailbox_imap_host;
$fuser->mailbox_imap_port = $mailboxconfig->mailbox_imap_port;
$fuser->mailbox_imap_ssl = $mailboxconfig->mailbox_imap_ssl;
$fuser->mailbox_imap_ref = $mailboxconfig->get_ref();
$fuser->mailbox_imap_connector_url = $mailboxconfig->get_connector_url();



/* * *************************************************************************** */
/* Affichage fiche                                                            */
/* * *************************************************************************** */

llxHeader();

$form = new Form($db);

if ($id) {
    $head = user_prepare_head($fuser);

    $title = $langs->trans("User");
    dol_fiche_head($head, 'mailboxconfig', $title, 0, 'user');

    if ($msg)
        print '<div class="error">' . $msg . '</div>';

    print "<form method=\"post\" action=\"usertab_mailboxconfig.php\">";
    print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
    print '<input type="hidden" name="id" value="' . $id . '">';
    print '<input type="hidden" name="mailboxuserid" value="' . $fuser->mailbox_id . '">';
    print '<input type="hidden" name="action" value="update">';

    print '<table class="border" width="100%">';

    // Reference
    print '<tr><td width="20%">' . $langs->trans('Ref') . '</td>';
    print '<td colspan="3">';
    print $form->showrefnav($fuser, 'id', '', $user->rights->user->user->lire || $user->admin);
    print '</td>';
    print '</tr>';

    // Nom
    print '<tr><td>' . $langs->trans("Lastname") . ' ' . $langs->trans("Firstname") . '</td><td class="valeur" colspan="3">' . $fuser->nom . '&nbsp;' . $fuser->prenom . '&nbsp;</td></tr>';

    // Login
    print '<tr><td>' . $langs->trans("IMAP Login") . '</td><td class="valeur" colspan="3">';
    if ($action == 'edit')
        print '<input size="30" type="text" class="flat" name="mailbox_imap_login" value="' . $fuser->mailbox_imap_login . '">';
    else
        print $fuser->mailbox_imap_login . '&nbsp;';
    print '</td></tr>';
    // Server
    print '<tr><td>' . $langs->trans("IMAP Password") . '</td><td class="valeur" colspan="3">';
    if ($action == 'edit') {
        print '<input size="12" maxlength="32" type="password" class="flat" name="mailbox_imap_password" value="' . $fuser->mailbox_imap_password . '">';
    } else {
        if ($fuser->mailbox_imap_password)
            print preg_replace('/./i', '*', $fuser->mailbox_imap_password);
        else
            print $langs->trans("Hidden");
    }
    print '</td></tr>';
    // Server
    print '<tr><td>' . $langs->trans("IMAP Server") . '</td><td class="valeur" colspan="3">';
    if ($action == 'edit')
        print '<input size="30" type="text" class="flat" name="mailbox_imap_host" value="' . $fuser->mailbox_imap_host . '">';
    else
        print $fuser->mailbox_imap_host . '&nbsp;';
    print '</td></tr>';
    // Server
    print '<tr><td>' . $langs->trans("IMAP Port") . '</td><td class="valeur" colspan="3">';
    if ($action == 'edit')
        print '<input size="30" type="text" class="flat" name="mailbox_imap_port" value="' . $fuser->mailbox_imap_port . '">';
    else
        print $fuser->mailbox_imap_port . '&nbsp;';
    print '</td></tr>';
    print '<tr><td>' . $langs->trans("IMAP SSL") . '</td><td class="valeur" colspan="3">';
    if ($action == 'edit') {
        print '<select name="mailbox_imap_ssl" >';
        print '<option value="1" ';
        if ($fuser->mailbox_imap_ssl)
            print 'selected ';
        print '">Oui</option>';
        print '<option value="0" ';
        if (!$fuser->mailbox_imap_ssl)
            print 'selected ';
        print '">Non</option>';
        print '</select>';
    }
    else {
        if ($fuser->mailbox_imap_ssl)
            print 'Oui';
        if (!$fuser->mailbox_imap_ssl)
            print 'Non';
    }
    print '</td></tr>';
    print "</table>";

    if ($action == 'edit') {
        print '<center><br>';
        print '<input type="submit" class="button" name="update" value="' . $langs->trans("Save") . '">';
        print '&nbsp; &nbsp;';
        print '<input type="submit" class="button" name="cancel" value="' . $langs->trans("Cancel") . '">';
        print '</center>';
    }

    print "</form>\n";


    /*
     * Actions
     */
    print '</div>';
    print '<div class="tabsAction">';

    if ($user->rights->user->user->creer && $action != 'edit') {
        print "<a class=\"butAction\" href=\"usertab_mailboxconfig.php?id=" . $id . "&amp;action=edit\">" . $langs->trans('Modify') . "</a>";
    }

    print "</div>";
}

$db->close();

llxFooter();
?>
