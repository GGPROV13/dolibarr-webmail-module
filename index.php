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


require_once(DOL_DOCUMENT_ROOT . "/core/lib/agenda.lib.php");
require_once(DOL_DOCUMENT_ROOT . "/comm/action/class/cactioncomm.class.php");
require_once(DOL_DOCUMENT_ROOT . "/comm/action/class/actioncomm.class.php");

// Change this following line to use the correct relative path from htdocs (do not remove DOL_DOCUMENT_ROOT)
//require_once(DOL_DOCUMENT_ROOT."/../dev/skeleton/skeleton_class.class.php");
// Load traductions files requiredby by page
$langs->load("companies");
$langs->load("other");
$langs->load("dolimail");
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
        $user->mailbox_imap_ref = "{" . $user->mailbox_imap_host . "}INBOX";
    }
    $db->free($resql);
}

if (GETPOST('reference_mail_uid') && GETPOST('reference_rowid') && GETPOST('reference_type_element')) {

    $mbox = imap_open('{' . $user->mailbox_imap_host . ':' . $user->mailbox_imap_port . '}', $user->mailbox_imap_login, $user->mailbox_imap_password);

    if (FALSE === $mbox) {
        $info = FALSE;
        $err = 'La connexion a échoué. Vérifiez vos paramètres!';
    } else {
        $uid = $_GET['uid'];
        $headerText = imap_fetchHeader($mbox, GETPOST('reference_mail_uid'), FT_UID);
        $header = imap_rfc822_parse_headers($headerText);

        // REM: Attention s'il y a plusieurs sections
        $corps = trim( utf8_encode( quoted_printable_decode(imap_fetchbody($mbox, GETPOST('reference_mail_uid'), 1, FT_UID))));
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

/* * *************************************************
 * VIEW
 *
 * Put here all code to build page
 * ************************************************** */

llxHeader('', 'Dolibarr Webmail', '');

// Connexion
$mbox = imap_open('{' . $user->mailbox_imap_host . ':' . $user->mailbox_imap_port . '}INBOX' . $folder, $user->mailbox_imap_login, $user->mailbox_imap_password);

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
    echo $err;
} else {
    echo "<div style='float:left;width:19%;'>";
    echo "<h2><a href='" . DOL_URL_ROOT . "/dolimail/index.php'>" . $langs->trans("Boite de réception") . "</a></h2>";
    foreach ($menus as &$m) {
        $cible = $m;
        # Subfolders
        $ex = explode('/', $m);
        while (count($ex) > 1) {
            $p = array_shift($ex);
            $m = '&nbsp;&nbsp;' . str_replace($p . "/", '', $m);
        }
        echo "<a href='" . DOL_URL_ROOT . "/dolimail/index.php?folder=" . urlencode(str_replace($user->mailbox_imap_ref, '', $cible)) . "'>";
        echo str_replace($user->mailbox_imap_ref, '', $m);
        echo "</a>";
        echo "<br />";
    }
    echo "</div>";
    echo "<div style='float:left;width:79%'>";
    echo "<table style='width:100%;'>\n";
    echo "    <tr>\n";
    echo "      <th style='text-align:left;'>";
    echo "<h2>";
    if (GETPOST('folder') == "") {
        print $langs->trans("Boite de réception");
    } else {
        print GETPOST('folder');
    }
    print " (" . $info->Nmsgs . ")</h2>";
    echo "      </th>\n";
    echo "    </tr>\n";
    echo "    <tr>\n";
    echo "      <th style='text-align:right;'>";
    $page_precedente = GETPOST("num_page") - 1;
    $page_suivante = GETPOST("num_page") + 1;
    if ($page_precedente > 0)
        print '<a href="' . DOL_URL_ROOT . '/dolimail/index.php?folder=' . GETPOST('folder') . '&num_page=' . $page_precedente . '">Precedente</a> ';

    for ($num_page = 1; $num_page <= ceil($info->Nmsgs / $pagination); $num_page++) {
        if ($num_page != GETPOST("num_page"))
            print '<a href="' . DOL_URL_ROOT . '/dolimail/index.php?folder=' . GETPOST('folder') . '&num_page=' . $num_page . '">' . $num_page . '</a> ';
        else
            print $num_page;

        if ($num_page < ceil($info->Nmsgs / $pagination))
            print ', ';
    }

    if ($page_suivante < ceil($info->Nmsgs / $pagination))
        print '<a href="' . DOL_URL_ROOT . '/dolimail/index.php?folder=' . GETPOST('folder') . '&num_page=' . $page_suivante . '">Suivante</a> ';
    echo "      </th>\n";
    echo "    </tr>\n";
    echo "</table>\n";
    echo "<table style='width:100%;'>\n";
    echo "  <thead>\n";
    echo "    <tr>\n";
    echo "      <th colspan='2'>" . $langs->trans("DolimaillObject") . "</th>\n";
    echo "      <th>" . $langs->trans("DolimaillFrom") . "</th>\n";
    echo "      <th>" . $langs->trans("DolimaillDate") . "</th>\n";
    echo "      <th>" . $langs->trans("DolimaillTaille") . "</th>\n";
    echo "      <th>" . $langs->trans("DolimaillFlagged") . "</th>\n";
    echo "      <th>" . $langs->trans("DolimaillLinked") . "</th>\n";
    echo "    </tr>\n";
    echo "  </thead>\n";
    echo "  <tbody>\n";
    foreach ($mails as $i => $mail) {
        if ($mail->deleted == 0) {
            if (!isset($mail->subject) or trim($mail->subject) == '')
                $mail->subject = $langs->trans('[ Empty subject ]');
            echo "    <tr class='";
            if ($i % 2 == 0)
                echo "impair "; else
                echo "pair ";
            if ($mail->seen)
                echo "seen "; else
                echo "unseen ";
            echo "'>\n";
            echo "      <td style='text-align:center;width:30px;'>";
            if ($mail->answered)
                echo "        <img src='" . DOL_URL_ROOT . "/dolimail/img/answered.png' alt='answered' />";
            echo "      </td>\n";
            echo "      <td><a href='" . DOL_URL_ROOT . "/dolimail/detail.php?uid=" . $mail->uid . "'>" . @iconv_mime_decode(imap_utf8($mail->subject)) . "</a></td>\n";
            echo "      <td>" . trim(preg_replace('/<.*>|"/', '', @iconv_mime_decode(imap_utf8($mail->from)))) . "</td>\n";
            echo "      <td style='text-align:center;width: 115px;'>" . date("d/m/Y H:i", strtotime($mail->date)) . "</td>\n";
            echo "      <td style='text-align:right;'>" . number_format($mail->size / 1000, 2) . "Ko</td>\n";
            echo "      <td>";
            if ($mail->flagged)
                echo "<img src='" . DOL_URL_ROOT . "/dolimail/img/flagged.png' alt='flagged' />"; else
                echo "<img src='" . DOL_URL_ROOT . "/dolimail/img/unflagged.png' alt='unflagged' />";
            echo "</td>\n";
            echo '<td>';
            echo '<form name="link_' . $i . '" method="POST">';
            echo '<table><tr><td>';
            $out = '';
            if ($conf->use_javascript_ajax)
                $out .= ajax_multiautocompleter('reference_' . $i, array('reference_rowid_' . $i, 'reference_type_element_' . $i, 'reference_fk_socid_' . $i), DOL_URL_ROOT . '/dolimail/core/ajax/reference.php', 'num_ligne=' . $i) . "\n";
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
            echo img_picto('attacher', 'lock');
            print '</a>';
            print '</td></tr></table>';
            print '</form>';
            print '</td>';
            echo "    </tr>\n";
        }
    }

    echo "  </tbody>\n";
    echo "</table>\n";
    echo "<table style='width:100%;'>\n";
    echo "    <tr>\n";
    echo "      <th style='text-align:right;'>";
    $page_precedente = GETPOST("num_page") - 1;
    $page_suivante = GETPOST("num_page") + 1;
    if ($page_precedente > 0)
        print '<a href="' . DOL_URL_ROOT . '/dolimail/index.php?folder=' . GETPOST('folder') . '&num_page=' . $page_precedente . '">Precedente</a> ';

    for ($num_page = 1; $num_page <= ceil($info->Nmsgs / $pagination); $num_page++) {
        if ($num_page != GETPOST("num_page"))
            print '<a href="' . DOL_URL_ROOT . '/dolimail/index.php?folder=' . GETPOST('folder') . '&num_page=' . $num_page . '">' . $num_page . '</a> ';
        else
            print $num_page;

        if ($num_page < ceil($info->Nmsgs / $pagination))
            print ', ';
    }

    if ($page_suivante < ceil($info->Nmsgs / $pagination))
        print '<a href="' . DOL_URL_ROOT . '/dolimail/index.php?folder=' . GETPOST('folder') . '&num_page=' . $page_suivante . '">Suivante</a> ';
    echo "      </th>\n";
    echo "    </tr>\n";
    echo "</table>\n";
    echo "</div>";
}


// End of page
llxFooter();
$db->close();
?>