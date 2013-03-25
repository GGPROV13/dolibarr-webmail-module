<?php
/* Copyright (C) 2004-2012	Laurent Destailleur		<eldy@users.sourceforge.net>
 * Copyright (C) 2006		Rodolphe Quiedeville	<rodolphe@quiedeville.org>
 * Copyright (C) 2007-2012	Regis Houssin			<regis@dolibarr.fr>
 * Copyright (C) 2011		Philippe Grand			<philippe.grand@atoo-net.com>
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
 *		\file       htdocs/theme/eldy/style.css.php
 *		\brief      File for CSS style sheet Eldy
 */
session_cache_limiter(FALSE);

require_once("../../main.inc.php");

// Load user to have $user->conf loaded (not done into main because of NOLOGIN constant defined)
if (empty($user->id) && ! empty($_SESSION['dol_login'])) $user->fetch('',$_SESSION['dol_login']);


// Define css type
header('Content-type: text/css');
// Important: Following code is to avoid page request by browser and PHP CPU at
// each Dolibarr page access.
if (empty($dolibarr_nocache))
  header('Cache-Control: max-age=3600, public, must-revalidate');
else
  header('Cache-Control: no-cache');

if (empty($conf->browser->phone)) { ?>

div.mainmenu.dolimail {
	background-image: url(<?php echo dol_buildpath($path.'/dolimail/img/menus/dolimail.png',1) ?>);
}

.seen, .seen a
{
font-weight: normal;
}

.unseen, .unseen a
{
font-weight: bold;
}

span#selected
{
    color : blue;
    text-weight : bold;
}


<?php
if (is_object($db)) $db->close();
}
?>
