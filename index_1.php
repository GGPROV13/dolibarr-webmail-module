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
$folder = urldecode(GETPOST('folder', 'alpha'));
// Protection if external user
if ($user->societe_id > 0)
{
  //accessforbidden();
}



$sql = "SELECT mailbox_imap_login, mailbox_imap_password, mailbox_imap_host, mailbox_imap_port ";
$sql.= " FROM " . MAIN_DB_PREFIX . "usermailboxconfig as u";
$sql.= " WHERE u.fk_user = " . $user->id;

$resql = $db->query($sql);
if ($resql)
{
  if ($db->num_rows($resql))
  {
    $obj = $db->fetch_object($resql);

    $user->mailbox_imap_login = $obj->mailbox_imap_login;
    $user->mailbox_imap_password = $obj->mailbox_imap_password;
    $user->mailbox_imap_host = $obj->mailbox_imap_host;
    $user->mailbox_imap_port = $obj->mailbox_imap_port;
    $user->mailbox_imap_ref = "{" . $user->mailbox_imap_host . "}INBOX";
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
$mbox = imap_open('{' . $user->mailbox_imap_host . ':' . $user->mailbox_imap_port . '}INBOX' . $folder, $user->mailbox_imap_login, $user->mailbox_imap_password);

if (FALSE === $mbox)
{
  $info = FALSE;
  $err = 'La connexion a échoué. Vérifiez vos paramètres!';
}
else
{
  $info = imap_check($mbox);
  if (FALSE !== $info)
  {
    $nbMessages = min(50, $info->Nmsgs);
    
    //$mails = imap_sort($mbox, SORTDATE, 1, SE_UID);
    $mails = imap_fetch_overview($mbox, '1:' . $nbMessages, 0);
    $menus = imap_list($mbox, $user->mailbox_imap_ref, '*');
    sort($menus);
    //print_r($mails);
    imap_close($mbox);
  }
  else
  {
    $err = 'Impossible de lire le contenu de la boite mail';
  }
}

if (FALSE === $info)
{
  echo $err;
}
else
{
  echo "<div style='float:left;width:280px;'>";
  echo "<h2><a href='" . DOL_URL_ROOT . "/dolimail/index.php'>" . $langs->trans("Boite de réception") . "</a></h2>";
  foreach ($menus as &$m)
  {
    $cible = $m;
    # Subfolders
    $ex = explode('/', $m);
    while (count($ex) > 1)
    {
      $p = array_shift($ex);
      $m = '&nbsp;&nbsp;' . str_replace($p . "/", '', $m);
    }
      echo "<a href='" . DOL_URL_ROOT . "/dolimail/index.php?folder=" . urlencode(str_replace($user->mailbox_imap_ref, '', $cible)) . "'>";
      echo str_replace($user->mailbox_imap_ref, '', $m);
      echo "</a>";
      echo "<br />";
  }
  echo "</div>";
  echo "<div style='float:left;'>";
  echo "<table style='width:100%;'>\n";
  echo "  <thead>\n";
  echo "    <tr>\n";
  echo "      <th>" . $langs->trans("DolimaillObject") . "</th>\n";
  echo "      <th>" . $langs->trans("DolimaillFrom") . "</th>\n";
  echo "      <th>" . $langs->trans("DolimaillDate") . "</th>\n";
  echo "      <th>" . $langs->trans("DolimaillTaille") . "</th>\n";
  echo "      <th>" . $langs->trans("DolimaillFlagged") . "</th>\n";
  echo "    </tr>\n";
  echo "  </thead>\n";
  echo "  <tbody>\n";
  foreach ($mails as $i => $mail)
  {

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
    echo "      <td><a href='" . DOL_URL_ROOT . "/dolimail/detail.php?uid=" . $mail->uid . "'>" . @iconv_mime_decode(imap_utf8($mail->subject)) . "</a></td>\n";
    echo "      <td>" . trim(preg_replace('/<.*>|"/', '', @iconv_mime_decode(imap_utf8($mail->from)))) . "</td>\n";
    echo "      <td style='text-align:center;width: 115px;'>" . date("d/m/Y H:i", strtotime($mail->date)) . "</td>\n";
    echo "      <td style='text-align:right;'>" . number_format($mail->size / 1000, 2) . "Ko</td>\n";
    echo "      <td>";
    if ($mail->flagged)
      echo "<img src='" . DOL_URL_ROOT . "/dolimail/img/flagged.png' alt='flagged' />"; else
      echo "<img src='" . DOL_URL_ROOT . "/dolimail/img/unflagged.png' alt='unflagged' />";
    echo "</td>\n";
    echo "    </tr>\n";
  }

  echo "  </tbody>\n";
  echo "</table>\n";
  echo "</div>";
}


// End of page
llxFooter();
$db->close();
?>