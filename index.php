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

require_once(DOL_DOCUMENT_ROOT . "/core/lib/agenda.lib.php");
require_once(DOL_DOCUMENT_ROOT . "/comm/action/class/cactioncomm.class.php");
require_once(DOL_DOCUMENT_ROOT . "/comm/action/class/actioncomm.class.php");
require_once(dirname(__FILE__) . '/class/usermailboxconfig.class.php');


// Change this following line to use the correct relative path from htdocs (do not remove DOL_DOCUMENT_ROOT)
//require_once(DOL_DOCUMENT_ROOT."/../dev/skeleton/skeleton_class.class.php");
// Load traductions files requiredby by page
$langs->load("companies");
$langs->load("other");
$langs->load("dolimail@dolimail");
$langs->load("agenda");

global $conf;
if ($conf->global->PAGINATION_WEBMAIL)
    $pagination = $conf->global->PAGINATION_WEBMAIL;
else
    $pagination = 50;

if (empty($_GET['num_page']))
    $_GET['num_page'] = 1;

// Get parameters
$id = GETPOST('id', 'int');
$action = GETPOST('action', 'alpha');
$folder = urldecode(GETPOST('folder', 'alpha'));
// Protection if external user
if ($user->societe_id > 0) {
    //accessforbidden();
}

if (!(extension_loaded("IMAP"))) {
    llxHeader('', 'Dolibarr Webmail', '');

    $head[0][0] = dol_buildpath('/dolimail/index.php', 1);
    $head[0][1] = $langs->trans("DolimailMailbox");
    $head[0][2] = 'mailbox';

    dol_fiche_head($head, 'mailbox', $langs->trans("Webmail"), 0, 'mailbox@dolimail');
    print $langs->trans('Erreur : Module Imap non chargé');
} else {

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

        $mbox = imap_open($user->mailbox_imap_connector_url, $user->mailbox_imap_login, $user->mailbox_imap_password);

        if (FALSE === $mbox) {
            $info = FALSE;
            $err = 'La connexion a échoué. Vérifiez vos paramètres!';
        } else {
            $uid = $_GET['uid'];
            $headerText = imap_fetchHeader($mbox, GETPOST('reference_mail_uid'), FT_UID);
            $header = imap_rfc822_parse_headers($headerText);

            // REM: Attention s'il y a plusieurs sections
            $corps = trim(utf8_encode(quoted_printable_decode(imap_fetchbody($mbox, GETPOST('reference_mail_uid'), 1, FT_UID))));
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
        // On utilise l'objet du mail 
        $actioncomm->label = $header->subject;
        // ou a défaut le label "Envoi de mail de %expediteur%"
        if (!$actioncomm->label) {
            if (trim($header->from->personnal) == "")
                $actioncomm->label = $langs->transnoentitiesnoconv("MailFrom", $header->fromadress);
            else
                $actioncomm->label = $langs->transnoentitiesnoconv("MailFrom", $header->from->personnal);
        }

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

    /*     * *************************************************
     * VIEW
     *
     * Put here all code to build page
     * ************************************************** */

    llxHeader('', 'Dolibarr Webmail', '');

    $head[0][0] = dol_buildpath('/dolimail/index.php', 1);
    $head[0][1] = $langs->trans("DolimailMailbox");
    $head[0][2] = 'mailbox';

    dol_fiche_head($head, 'mailbox', $langs->trans("Webmail"), 0, 'mailbox@dolimail');

// Connexion
    $mbox = imap_open($user->mailbox_imap_connector_url . $folder, $user->mailbox_imap_login, $user->mailbox_imap_password);
    if (FALSE === $mbox) {
        $info = FALSE;
        $err = 'La connexion a échoué. Vérifiez vos paramètres!';
    } else {
        $info = imap_check($mbox);
        if (FALSE !== $info) {
            $indice_msgend = $info->Nmsgs - ($pagination * (GETPOST('num_page') - 1));
            $indice_msgbegin = max(1, $indice_msgend - $pagination + 1);
            $mails = array_reverse(imap_fetch_overview($mbox, $indice_msgbegin . ':' . $indice_msgend, 0));
            $menus = imap_list($mbox, $user->mailbox_imap_ref, '*');
            sort($menus);
            imap_close($mbox);
        } else {
            $err = 'Impossible de lire le contenu de la boite mail';
        }
    }

    if (FALSE === $info) {
        print $err;
    } else {
        if ($folder == '')
            $folder = 'INBOX';
        $lbl_folder = array_reverse(explode('/', $folder));
        $lbl_folder = str_replace($user->mailbox_imap_ref, '', str_replace('INBOX.', '', $lbl_folder[0]));
        print '<div style="float:left;width:19%;">';
        print '<div class="TitleImapDirectories"><a href="' . dol_buildpath('/dolimail/index.php', 1) . '">' . $langs->trans($lbl_folder) . ' (' . $info->Nmsgs . ') </a></div>';
        print '<ul id="MenuDirectory">';
        foreach ($menus as &$m) {
            $cible = $m;
            # Subfolders
            $ex = explode('/', $m);
            while (count($ex) > 1) {
                $p = array_shift($ex);
                $m = '&nbsp;&nbsp;' . str_replace($p . "/", '', $m);
            }
            print '<li class="ImapDirectory"><a href="' . dol_buildpath('/dolimail/index.php', 1) . '?folder=' . urlencode(str_replace($user->mailbox_imap_ref, '', $cible)) . '">';
            $nb_decalage = mb_substr_count($m, 'INBOX.');
            for ($decalage = 0; $decalage < $nb_decalage; $decalage++)
                print '&nbsp;&nbsp;';
            print $langs->trans(str_replace($user->mailbox_imap_ref, '', str_replace('INBOX.', '', $m)));
            print '</a></li>';
        }
        print '</ul>';
        print '</div>';
        print '<div style="float:left;width:79%">';
        print '<table style="width:100%;">';
        print '    <tr>';
        print '      <th style="text-align:right;">';
        $page_precedente = GETPOST("num_page") - 1;
        $page_suivante = GETPOST("num_page") + 1;
        if ($page_precedente > 0)
            print '<a href="' . dol_buildpath('/dolimail/index.php', 1) . '?folder=' . GETPOST('folder') . '&num_page=' . $page_precedente . '">Precedente</a> ';

        for ($num_page = 1; $num_page <= ceil($info->Nmsgs / $pagination); $num_page++) {
            if ($num_page != GETPOST("num_page"))
                print '<a href="' . dol_buildpath('/dolimail/index.php', 1) . '?folder=' . GETPOST('folder') . '&num_page=' . $num_page . '">' . $num_page . '</a> ';
            else
                print '<span id="selected">' . $num_page . '</span>';

            if ($num_page < ceil($info->Nmsgs / $pagination))
                print ', ';
        }

        if ($page_suivante < ceil($info->Nmsgs / $pagination))
            print '<a href="' . dol_buildpath('/dolimail/index.php', 1) . '?folder=' . GETPOST('folder') . '&num_page=' . $page_suivante . '">Suivante</a> ';
        print '      </th>';
        print '    </tr>';
        print '</table>';
        print '<table class="noborder" width="100%">';
        print '<tr class="liste_titre">';
        print '      <td class="liste_titre" align="center" colspan="2">' . $langs->trans("DolimaillObject") . '</td>';
        print '      <td class="liste_titre"  align="center">' . $langs->trans("DolimaillFrom") . '</td>';
        print '      <td class="liste_titre"  align="center">' . $langs->trans("DolimaillDate") . '</td>';
        print '      <td class="liste_titre"  align="center">' . $langs->trans("DolimaillTaille") . '</td>';
        print '      <td class="liste_titre"  align="center">' . $langs->trans("DolimaillFlagged") . '</td>';
        print '      <td class="liste_titre"  align="center">' . $langs->trans("DolimaillLinked") . '</td>';
        print '    </tr>';
        foreach ($mails as $i => $mail) {
            if ($mail->deleted == 0) {
                if (!isset($mail->subject) or trim($mail->subject) == '')
                    $mail->subject = $langs->trans('[ Empty subject ]');
                print '    <tr class="';
                if ($i % 2 == 0)
                    print 'impair ';
                else
                    print 'pair ';
                if ($mail->seen)
                    print 'seen ';
                else
                    print 'unseen ';
                print '">';
                print '      <td style="text-align:center;width:30px;">';
                if ($mail->answered)
                    print img_picto('answered', 'answered@dolimail');
                if ($mail->size < 1024) {
                    $unit = '&nbsp;o.';
                } else if ($mail->size / 1024 > 1024) {
                    $mail->size = $mail->size / 1024 / 1024;
                    $unit = '&nbsp;Mo.';
                } else {
                    $mail->size = $mail->size / 1024;
                    $unit = '&nbsp;Ko.';
                }
                print '      </td>';
                print '      <td><a href="' . dol_buildpath('/dolimail/detail.php', 1) . '?uid=' . $mail->uid . ($folder != '' ? '&folder=' . $folder : '') . '">' . trim(utf8_encode(@iconv_mime_decode(imap_utf8($mail->subject)))) . '</a></td>';
                print '      <td>' . trim(preg_replace('/<.*>|"/', '', @iconv_mime_decode(imap_utf8($mail->from)))) . '</td>';
                print '      <td style="text-align:center;width: 115px;">' . date("d/m/Y H:i", strtotime($mail->date)) . '</td>';
                print '      <td style="text-align:right;">' . number_format($mail->size, 2) . $unit . '</td>';
                print '      <td align="center">';

                if ($mail->flagged)
                    print img_picto('flagged', 'flagged@dolimail');
                else
                    print img_picto('unflagged', 'unflagged@dolimail');
                print '</td>';
                print '<td>';
                print '<form name="link_' . $i . '" method="POST">';
                print '<table><tr><td>';
                $out = '';
                if ($conf->use_javascript_ajax)
                    $out .= ajax_multiautocompleter('reference_' . $i, array('reference_rowid_' . $i, 'reference_type_element_' . $i, 'reference_fk_socid_' . $i), dol_buildpath('/dolimail/core/ajax/reference.php', 1), 'num_ligne=' . $i) . "\n";
                $out.= '<input id="reference_' . $i . '" type="text" name="reference" value="';
                print $out . '">' . "\n";
                print '<input id="reference_rowid_' . $i . '" type="hidden" name="reference_rowid" value="';
                print '">' . "\n";
                print '<input id="reference_type_element_' . $i . '" type="hidden" name="reference_type_element" value="';
                print '">' . "\n";
                print '<input id="reference_fk_socid_' . $i . '" type="hidden" name="reference_fk_socid" value="';
                print '">' . "\n";
                print '<input id="reference_mail_uid_' . $i . '" type="hidden" name="reference_mail_uid" value="';
                print $mail->uid;
                print '">' . "\n";
                print '</td><td>';
                print '<a href="javascript:;" onclick="link_' . $i . '.submit();">';
                print img_picto('attacher', 'lock');
                print '</a>';
                print '</td></tr></table>';
                print '</form>';
                print '</td>';
                print '</tr>';
            }
        }
        print '</table>';
        print '<table style="width:100%;">';
        print '    <tr>';
        print '      <th style="text-align:right;">';
        $page_precedente = GETPOST("num_page") - 1;
        $page_suivante = GETPOST("num_page") + 1;
        if ($page_precedente > 0)
            print '<a href="' . dol_buildpath('/dolimail/index.php', 1) . '?folder=' . GETPOST('folder') . '&num_page=' . $page_precedente . '">Precedente</a> ';

        for ($num_page = 1; $num_page <= ceil($info->Nmsgs / $pagination); $num_page++) {
            if ($num_page != GETPOST("num_page"))
                print '<a href="' . dol_buildpath('/dolimail/index.php', 1) . '?folder=' . GETPOST('folder') . '&num_page=' . $num_page . '">' . $num_page . '</a> ';
            else
                print $num_page;

            if ($num_page < ceil($info->Nmsgs / $pagination))
                print ', ';
        }

        if ($page_suivante < ceil($info->Nmsgs / $pagination))
            print '<a href="' . dol_buildpath('/dolimail/index.php', 1) . '?folder=' . GETPOST('folder') . '&num_page=' . $page_suivante . '">Suivante</a> ';
        print '      </th>';
        print '    </tr>';
        print '</table>';
        print '</div>';
        print '<div style="clear:both;"></div>';
    }
}

// End of page
llxFooter();
$db->close();
?>