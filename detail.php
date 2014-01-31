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
if (!$res && file_exists("../../../../main.inc.php"))
    $res = @include("../../../../main.inc.php");
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
require_once(DOL_DOCUMENT_ROOT . "/core/lib/agenda.lib.php");
require_once(DOL_DOCUMENT_ROOT . "/comm/action/class/cactioncomm.class.php");
require_once(DOL_DOCUMENT_ROOT . "/comm/action/class/actioncomm.class.php");
require_once(dirname(__FILE__) . '/class/usermailboxconfig.class.php');
require_once(dirname(__FILE__) . '/lib/lib_dolimail.php');
$langs->load("companies");
$langs->load("other");
$langs->load("dolimail@dolimail");

// Get parameters
$id = GETPOST('id', 'int');
$action = GETPOST('action', 'alpha');
$folder = GETPOST('folder', 'alpha');

// Protection if external user
if ($user->societe_id > 0) {
    //accessforbidden();
}




$mailboxconfig = new Usermailboxconfig($db);
$mailboxconfig->fetch_from_user($user->id);

$user->mailbox_imap_login = $mailboxconfig->mailbox_imap_login;
$user->mailbox_imap_password = $mailboxconfig->mailbox_imap_password;
$user->mailbox_imap_host = $mailboxconfig->mailbox_imap_host;
$user->mailbox_imap_port = $mailboxconfig->mailbox_imap_port;
$user->mailbox_imap_ssl = $mailboxconfig->mailbox_imap_ssl;
$user->mailbox_imap_ssl_novalidate_cert = $mailboxconfig->mailbox_imap_ssl_novalidate_cert;
$user->mailbox_imap_ref = $mailboxconfig->get_ref();
$user->mailbox_imap_connector_url = $mailboxconfig->get_connector_url();



    if (GETPOST('reference_mail_uid') && GETPOST('reference_rowid') && GETPOST('reference_type_element')) {

        $mbox = imap_open($user->mailbox_imap_connector_url.$folder, $user->mailbox_imap_login, $user->mailbox_imap_password);

        if (FALSE === $mbox) {
            $info = FALSE;
            $err = 'La connexion a échoué. Vérifiez vos paramètres!';
        } else {
            $uid = GETPOST('reference_mail_uid');
            list($charset, $htmlmsg, $plainmsg, $attachments) = getmsg($mbox, $uid);

            $headerText = imap_fetchHeader($mbox, GETPOST('reference_mail_uid'), FT_UID);
            $header = imap_rfc822_parse_headers($headerText);

            switch ($charset) {
                case 'ISO-8859-1':
                case 'ISO-8859-15':
                    $htmlmsg = utf8_encode($htmlmsg);
                    $plainmsg = utf8_encode(nl2br($plainmsg));
                    break;
                default:
                    $plainmsg = nl2br($plainmsg);
            }
            if ($htmlmsg != '')
                $corps = $htmlmsg;
            else
                $corps = $plainmsg;
        }
        imap_close($mbox);

        $actioncomm = new ActionComm($db);
        $cactioncomm = new CActionComm($db);

        // Initialisation objet actioncomm
        $usertodo = $user;
        $actioncomm->usertodo = $usertodo;

        if (GETPOST('reference_fk_socid', 'int') > 0) {
            $societe = new Societe($db);
            $societe->fetch(GETPOST('reference_fk_socid', 'int'));
            $actioncomm->societe = $societe;
        }

        if ($conf->global->CODE_ACTIONCOMM_WEBMAIL)
            $cactioncomm_code = $conf->global->CODE_ACTIONCOMM_WEBMAIL;
        else
            $cactioncomm_code = "AC_OTH";

        $result = $cactioncomm->fetch($cactioncomm_code);

        $actioncomm->type_id = $cactioncomm->id;
        $actioncomm->type_code = $cactioncomm->code;
        $actioncomm->priority = 0;
        $actioncomm->fulldayevent = 0;
        $actioncomm->location = '';
        $from = $header->from;
        // On utilise l'objet du mail 
        $actioncomm->label = trim(preg_replace('/<.*>|"/', '', @iconv_mime_decode(imap_utf8($header->subject))));
        // ou a défaut le label "Envoi de mail de %expediteur%"
        if (!$actioncomm->label) {
            $lblfrom = @iconv_mime_decode(imap_utf8($from[0]->personal)) . " [" . $from[0]->mailbox . "@" . $from[0]->host . "]";
            $actioncomm->label = $langs->transnoentitiesnoconv("MailFrom", $lblfrom);
        } else
            $actioncomm->label = $langs->transnoentitiesnoconv("Mail ") . $actioncomm->label;
        if (GETPOST('reference_type_element') == 'projet')
            $actioncomm->fk_project = GETPOST('reference_rowid');
        else
            $actioncomm->fk_project = 0;
        $actioncomm->datep = strtotime($header->date);

        // On recupère le contenu du mail qu'on place dans la note
        $actioncomm->note = trim($corps);

        $actioncomm->fk_element = GETPOST('reference_rowid');
        $actioncomm->elementtype = GETPOST('reference_type_element');

        // 
        // TODO
        // On recherche l'expediteur dans les contacts
        if (isset($_POST["contactid"])) {
            $actioncomm->contact = $contact;
        }
        // Special for module webcal and phenix
        if ($_POST["add_webcal"] == 'on' && $conf->webcalendar->enabled)
            $actioncomm->use_webcal = 1;
        if ($_POST["add_phenix"] == 'on' && $conf->phenix->enabled)
            $actioncomm->use_phenix = 1;


        if (!$error) {
            $db->begin();

            // On cree l'action
            $idaction = $actioncomm->add($user);

            if ($idaction > 0) {
                if (!$actioncomm->error) {
                    $db->commit();
                }
            }
        }
    }

/* * *************************************************
 * VIEW
 *
 * Put here all code to build page
 * ************************************************** */

llxHeader('', 'Dolibarr Webmail', '');

$head[0][0] = dol_buildpath('/dolimail/index.php',1);
$head[0][1] = $langs->trans("DolimailMailbox");
$head[0][2] = 'mailbox';

$head[1][0] = $_SERVER['PHP_SELF'];
$head[1][1] = $langs->trans("DolimailDetail");
$head[1][2] = 'detail';

dol_fiche_head($head, 'detail', $langs->trans("Webmail"), 0, 'mailbox@dolimail');

// Connexion
$mbox = imap_open($user->mailbox_imap_connector_url.$folder, $user->mailbox_imap_login, $user->mailbox_imap_password);


if (FALSE === $mbox) {
    $info = FALSE;
    $err = 'La connexion a échoué. Vérifiez vos paramètres!';
} else {
    $uid = $_GET['uid'];
    $headerText = imap_fetchHeader($mbox, $uid, FT_UID);
    $header = imap_rfc822_parse_headers($headerText);

    list($charset, $htmlmsg, $plainmsg, $attachments) = getmsg($mbox, $uid);
}
imap_close($mbox);
switch($charset)
{
    case 'ISO-8859-1':
    case 'ISO-8859-15':
        $htmlmsg = utf8_encode($htmlmsg);
        $plainmsg = utf8_encode(nl2br($plainmsg));
        break;
    default:
        $plainmsg = nl2br($plainmsg);        
}
print '<form name="link_0" method="POST">';
print '<table>';
print '<tr><td  width="30%" nowrap><span class="fieldrequired">' . $langs->trans("DolimaillLinked") . '</span></td><td>';
$out = '';
if ($conf->use_javascript_ajax)
    $out .= ajax_multiautocompleter('reference_0', array('reference_rowid_0', 'reference_type_element_0', 'reference_fk_socid_0'), dol_buildpath('/dolimail/core/ajax/reference.php',1), 'num_ligne=0') . "\n";
$out.= '<input id="reference_0" type="text" name="reference_0" value="' . GETPOST("reference_0");
print $out . '">' . "\n";
print '<input id="reference_rowid_0" type="hidden" name="reference_rowid" value="';
print GETPOST("reference_rowid_0");
print '">' . "\n";
print '<input id="reference_type_element_0" type="hidden" name="reference_type_element" value="';
print GETPOST("reference_type_element_0");
print '">' . "\n";
print '<input id="reference_fk_socid_0" type="hidden" name="reference_fk_socid" value="';
print GETPOST("reference_fk_socid_0");
print '">' . "\n";
print '<input id="reference_mail_uid_0" type="hidden" name="reference_mail_uid" value="';
print $uid;
print '">' . "\n";

print '<a href="javascript:;" onclick="link_0.submit();">';
print img_picto('attacher', 'lock');
print '</a>';
print '</td></tr>';
print '</table>';
print '</form>';
print '<div class="titre">' . trim(preg_replace('/<.*>|"/', '', @iconv_mime_decode(imap_utf8($header->subject)))) . '</div>';
$from = $header->from;
echo "Message de : " . @iconv_mime_decode(imap_utf8($from[0]->personal)) . " [" . $from[0]->mailbox . "@" . $from[0]->host . "]<br /><br /><br />";

if ($htmlmsg != '')
    print ($htmlmsg);
else
    print ($plainmsg);
if (sizeof($attachments) > 0) {
    echo '<br /><hr />';
    foreach ($attachments as $att_name => $value) {
        print 'PJ : <a href="javascript:;" onclick="alert(\'Fonctionnalite en cours de developpement\');">' . $att_name . '</a><br />';
    }
}

// End of page
llxFooter();
$db->close();
?>