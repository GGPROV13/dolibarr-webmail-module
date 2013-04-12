<?php

/* Copyright (C) 2007-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) ---Put here your own copyright and developer email---
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
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
 *  \file       dev/skeletons/usermailboxconfig.class.php
 *  \ingroup    mymodule othermodule1 othermodule2
 *  \brief      This file is an example for a CRUD class file (Create/Read/Update/Delete)
 * 				Initialy built by build_class_from_table on 2013-03-25 22:50
 */
// Put here all includes required by your class file
//require_once(DOL_DOCUMENT_ROOT."/core/class/commonobject.class.php");
//require_once(DOL_DOCUMENT_ROOT."/societe/class/societe.class.php");
//require_once(DOL_DOCUMENT_ROOT."/product/class/product.class.php");

/**
 * 	Put here description of your class
 */
// extends CommonObject 
class Usermailboxconfig
{

    var $db;       //!< To store db handler
    var $error;       //!< To return error code (or message)
    var $errors = array();    //!< To return several error codes (or messages)
//var $element='usermailboxconfig';			//!< Id that identify managed objects
//var $table_element='usermailboxconfig';	//!< Name of table without prefix where object is stored
    var $id;
    var $mailbox_imap_login;
    var $mailbox_imap_password;
    var $mailbox_imap_host;
    var $mailbox_imap_port;
    var $mailbox_imap_ssl;
    var $mailbox_imap_ssl_novalidate_cert;
    var $fk_user;

    /**
     *  Constructor
     *
     *  @param	DoliDb		$db      Database handler
     */
    function __construct($db)
    {
        $this->db = $db;
        return 1;
    }

    /**
     *  Create object into database
     *
     *  @param	User	$user        User that creates
     *  @param  int		$notrigger   0=launch triggers after, 1=disable triggers
     *  @return int      		   	 <0 if KO, Id of created object if OK
     */
    function create($user, $notrigger = 0)
    {
        global $conf, $langs;
        $error = 0;

// Clean parameters

        if (isset($this->mailbox_imap_login))
                $this->mailbox_imap_login = trim($this->mailbox_imap_login);
        if (isset($this->mailbox_imap_password))
                $this->mailbox_imap_password = trim($this->mailbox_imap_password);
        if (isset($this->mailbox_imap_host))
                $this->mailbox_imap_host = trim($this->mailbox_imap_host);
        if (isset($this->mailbox_imap_port))
                $this->mailbox_imap_port = trim($this->mailbox_imap_port);
        if (isset($this->mailbox_imap_ssl))
                $this->mailbox_imap_ssl = trim($this->mailbox_imap_ssl);
        if (isset($this->$mailbox_imap_ssl_novalidate_cert))
                $this->$mailbox_imap_ssl_novalidate_cert = trim($this->$mailbox_imap_ssl_novalidate_cert);
        if (isset($this->fk_user)) $this->fk_user = trim($this->fk_user);



// Check parameters
// Put here code to add control on parameters values
// Insert request
        $sql = "INSERT INTO " . MAIN_DB_PREFIX . "usermailboxconfig(";

        $sql.= "mailbox_imap_login,";
        $sql.= "mailbox_imap_password,";
        $sql.= "mailbox_imap_host,";
        $sql.= "mailbox_imap_port,";
        $sql.= "mailbox_imap_ssl,";
        $sql.= "mailbox_imap_ssl_novalidate_cert,";
        $sql.= "fk_user";


        $sql.= ") VALUES (";

        $sql.= " " . (!isset($this->mailbox_imap_login) ? 'NULL' : "'" . $this->db->escape($this->mailbox_imap_login) . "'") . ",";
        $sql.= " " . (!isset($this->mailbox_imap_password) ? 'NULL' : "'" . $this->db->escape($this->mailbox_imap_password) . "'") . ",";
        $sql.= " " . (!isset($this->mailbox_imap_host) ? 'NULL' : "'" . $this->db->escape($this->mailbox_imap_host) . "'") . ",";
        $sql.= " " . (!isset($this->mailbox_imap_port) ? 'NULL' : "'" . $this->db->escape($this->mailbox_imap_port) . "'") . ",";
        $sql.= " " . (!isset($this->mailbox_imap_ssl) ? 'NULL' : "'" . $this->db->escape($this->mailbox_imap_ssl) . "'") . ",";
        $sql.= " " . (!isset($this->mailbox_imap_ssl_novalidate_cert) ? 'NULL' : "'" . $this->db->escape($this->mailbox_imap_ssl_novalidate_cert) . "'") . ",";
        $sql.= " " . (!isset($this->fk_user) ? 'NULL' : "'" . $this->fk_user . "'") . "";


        $sql.= ")";

        $this->db->begin();

        dol_syslog(get_class($this) . "::create sql=" . $sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if (!$resql)
        {
            $error++;
            $this->errors[] = "Error " . $this->db->lasterror();
        }

        if (!$error)
        {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX . "usermailboxconfig");

            if (!$notrigger)
            {
// Uncomment this and change MYOBJECT to your own tag if you
// want this action calls a trigger.
//// Call triggers
//include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
//$interface=new Interfaces($this->db);
//$result=$interface->run_triggers('MYOBJECT_CREATE',$this,$user,$langs,$conf);
//if ($result < 0) { $error++; $this->errors=$interface->errors; }
//// End call triggers
            }
        }

// Commit or rollback
        if ($error)
        {
            foreach ($this->errors as $errmsg)
            {
                dol_syslog(get_class($this) . "::create " . $errmsg, LOG_ERR);
                $this->error.=($this->error ? ', ' . $errmsg : $errmsg);
            }
            $this->db->rollback();
            return -1 * $error;
        }
        else
        {
            $this->db->commit();
            return $this->id;
        }
    }

    /**
     *  Load object in memory from the database
     *
     *  @param	int		$fk_user    id of user to load
     *  @return int          	<0 if KO, >0 if OK
     */
    function fetch_from_user($fk_user)
    {
        global $langs;
        $sql = "SELECT";
        $sql.= " t.rowid,";

        $sql.= " t.mailbox_imap_login,";
        $sql.= " t.mailbox_imap_password,";
        $sql.= " t.mailbox_imap_host,";
        $sql.= " t.mailbox_imap_port,";
        $sql.= " t.mailbox_imap_ssl,";
        $sql.= " t.mailbox_imap_ssl_novalidate_cert,";
        $sql.= " t.fk_user";


        $sql.= " FROM " . MAIN_DB_PREFIX . "usermailboxconfig as t";
        $sql.= " WHERE t.fk_user = " . $fk_user;

        dol_syslog(get_class($this) . "::fetch_from_user sql=" . $sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if ($resql)
        {
            if ($this->db->num_rows($resql))
            {
                $obj = $this->db->fetch_object($resql);

                $this->id = $obj->rowid;

                $this->mailbox_imap_login = $obj->mailbox_imap_login;
                $this->mailbox_imap_password = $obj->mailbox_imap_password;
                $this->mailbox_imap_host = $obj->mailbox_imap_host;
                $this->mailbox_imap_port = $obj->mailbox_imap_port;
                $this->mailbox_imap_ssl = $obj->mailbox_imap_ssl;
                $this->mailbox_imap_ssl_novalidate_cert = $obj->mailbox_imap_ssl_novalidate_cert;
                $this->fk_user = $obj->fk_user;
            }
            $this->db->free($resql);

            return 1;
        }
        else
        {
            $this->error = "Error " . $this->db->lasterror();
            dol_syslog(get_class($this) . "::fetch_from_user " . $this->error,
                                 LOG_ERR);
            return -1;
        }
    }

    /**
     *  Load object in memory from the database
     *
     *  @param	int		$id    Id object
     *  @return int          	<0 if KO, >0 if OK
     */
    function fetch($id)
    {
        global $langs;
        $sql = "SELECT";
        $sql.= " t.rowid,";

        $sql.= " t.mailbox_imap_login,";
        $sql.= " t.mailbox_imap_password,";
        $sql.= " t.mailbox_imap_host,";
        $sql.= " t.mailbox_imap_port,";
        $sql.= " t.mailbox_imap_ssl,";
        $sql.= " t.mailbox_imap_ssl_novalidate_cert,";
        $sql.= " t.fk_user";


        $sql.= " FROM " . MAIN_DB_PREFIX . "usermailboxconfig as t";
        $sql.= " WHERE t.rowid = " . $id;

        dol_syslog(get_class($this) . "::fetch sql=" . $sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if ($resql)
        {
            if ($this->db->num_rows($resql))
            {
                $obj = $this->db->fetch_object($resql);

                $this->id = $obj->rowid;

                $this->mailbox_imap_login = $obj->mailbox_imap_login;
                $this->mailbox_imap_password = $obj->mailbox_imap_password;
                $this->mailbox_imap_host = $obj->mailbox_imap_host;
                $this->mailbox_imap_port = $obj->mailbox_imap_port;
                $this->mailbox_imap_ssl = $obj->mailbox_imap_ssl;
                $this->mailbox_imap_ssl_novalidate_cert = $obj->mailbox_imap_ssl_novalidate_cert;
                $this->fk_user = $obj->fk_user;
            }
            $this->db->free($resql);

            return 1;
        }
        else
        {
            $this->error = "Error " . $this->db->lasterror();
            dol_syslog(get_class($this) . "::fetch " . $this->error, LOG_ERR);
            return -1;
        }
    }

    /**
     *  Update object into database
     *
     *  @param	User	$user        User that modifies
     *  @param  int		$notrigger	 0=launch triggers after, 1=disable triggers
     *  @return int     		   	 <0 if KO, >0 if OK
     */
    function update($user = 0, $notrigger = 0)
    {
        global $conf, $langs;
        $error = 0;

// Clean parameters

        if (isset($this->mailbox_imap_login))
                $this->mailbox_imap_login = trim($this->mailbox_imap_login);
        if (isset($this->mailbox_imap_password))
                $this->mailbox_imap_password = trim($this->mailbox_imap_password);
        if (isset($this->mailbox_imap_host))
                $this->mailbox_imap_host = trim($this->mailbox_imap_host);
        if (isset($this->mailbox_imap_port))
                $this->mailbox_imap_port = trim($this->mailbox_imap_port);
        if (isset($this->mailbox_imap_ssl))
                $this->mailbox_imap_ssl = trim($this->mailbox_imap_ssl);
        if (isset($this->mailbox_imap_ssl_novalidate_cert))
                $this->mailbox_imap_ssl_novalidate_cert = trim($this->mailbox_imap_ssl_novalidate_cert);
        if (isset($this->fk_user)) $this->fk_user = trim($this->fk_user);



// Check parameters
// Put here code to add a control on parameters values
// Update request
        $sql = "UPDATE " . MAIN_DB_PREFIX . "usermailboxconfig SET";

        $sql.= " mailbox_imap_login=" . (isset($this->mailbox_imap_login) ? "'" . $this->db->escape($this->mailbox_imap_login) . "'"
                            : "null") . ",";
        $sql.= " mailbox_imap_password=" . (isset($this->mailbox_imap_password) ? "'" . $this->db->escape($this->mailbox_imap_password) . "'"
                            : "null") . ",";
        $sql.= " mailbox_imap_host=" . (isset($this->mailbox_imap_host) ? "'" . $this->db->escape($this->mailbox_imap_host) . "'"
                            : "null") . ",";
        $sql.= " mailbox_imap_port=" . (isset($this->mailbox_imap_port) ? "'" . $this->db->escape($this->mailbox_imap_port) . "'"
                            : "null") . ",";
        $sql.= " mailbox_imap_ssl=" . (isset($this->mailbox_imap_ssl) ? "'" . $this->db->escape($this->mailbox_imap_ssl) . "'"
                            : "null") . ",";
        $sql.= " mailbox_imap_ssl_novalidate_cert=" . (isset($this->mailbox_imap_ssl_novalidate_cert)
                            ? "'" . $this->db->escape($this->mailbox_imap_ssl_novalidate_cert) . "'"
                            : "null") . ",";
        $sql.= " fk_user=" . (isset($this->fk_user) ? $this->fk_user : "null") . "";


        $sql.= " WHERE rowid=" . $this->id;

        $this->db->begin();

        dol_syslog(get_class($this) . "::update sql=" . $sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if (!$resql)
        {
            $error++;
            $this->errors[] = "Error " . $this->db->lasterror();
        }

        if (!$error)
        {
            if (!$notrigger)
            {
// Uncomment this and change MYOBJECT to your own tag if you
// want this action calls a trigger.
//// Call triggers
//include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
//$interface=new Interfaces($this->db);
//$result=$interface->run_triggers('MYOBJECT_MODIFY',$this,$user,$langs,$conf);
//if ($result < 0) { $error++; $this->errors=$interface->errors; }
//// End call triggers
            }
        }

// Commit or rollback
        if ($error)
        {
            foreach ($this->errors as $errmsg)
            {
                dol_syslog(get_class($this) . "::update " . $errmsg, LOG_ERR);
                $this->error.=($this->error ? ', ' . $errmsg : $errmsg);
            }
            $this->db->rollback();
            return -1 * $error;
        }
        else
        {
            $this->db->commit();
            return 1;
        }
    }

    /**
     *  Delete object in database
     *
     * 	@param  User	$user        User that deletes
     *  @param  int		$notrigger	 0=launch triggers after, 1=disable triggers
     *  @return	int					 <0 if KO, >0 if OK
     */
    function delete($user, $notrigger = 0)
    {
        global $conf, $langs;
        $error = 0;

        $this->db->begin();

        if (!$error)
        {
            if (!$notrigger)
            {
// Uncomment this and change MYOBJECT to your own tag if you
// want this action calls a trigger.
//// Call triggers
//include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
//$interface=new Interfaces($this->db);
//$result=$interface->run_triggers('MYOBJECT_DELETE',$this,$user,$langs,$conf);
//if ($result < 0) { $error++; $this->errors=$interface->errors; }
//// End call triggers
            }
        }

        if (!$error)
        {
            $sql = "DELETE FROM " . MAIN_DB_PREFIX . "usermailboxconfig";
            $sql.= " WHERE rowid=" . $this->id;

            dol_syslog(get_class($this) . "::delete sql=" . $sql);
            $resql = $this->db->query($sql);
            if (!$resql)
            {
                $error++;
                $this->errors[] = "Error " . $this->db->lasterror();
            }
        }

// Commit or rollback
        if ($error)
        {
            foreach ($this->errors as $errmsg)
            {
                dol_syslog(get_class($this) . "::delete " . $errmsg, LOG_ERR);
                $this->error.=($this->error ? ', ' . $errmsg : $errmsg);
            }
            $this->db->rollback();
            return -1 * $error;
        }
        else
        {
            $this->db->commit();
            return 1;
        }
    }

    /**
     * 	Load an object from its id and create a new one in database
     *
     * 	@param	int		$fromid     Id of object to clone
     * 	@return	int					New id of clone
     */
    function createFromClone($fromid)
    {
        global $user, $langs;

        $error = 0;

        $object = new Usermailboxconfig($this->db);

        $this->db->begin();

// Load source object
        $object->fetch($fromid);
        $object->id = 0;
        $object->statut = 0;

// Clear fields
// ...
// Create clone
        $result = $object->create($user);

// Other options
        if ($result < 0)
        {
            $this->error = $object->error;
            $error++;
        }

        if (!$error)
        {
            
        }

// End
        if (!$error)
        {
            $this->db->commit();
            return $object->id;
        }
        else
        {
            $this->db->rollback();
            return -1;
        }
    }

    /**
     * 	Initialise object with example values
     * 	Id must be 0 if object instance is a specimen
     *
     * 	@return	void
     */
    function initAsSpecimen()
    {
        $this->id = 0;

        $this->mailbox_imap_login = '';
        $this->mailbox_imap_password = '';
        $this->mailbox_imap_host = '';
        $this->mailbox_imap_port = '';
        $this->mailbox_imap_ssl = '';
        $this->mailbox_imap_ssl_novalidate_cert = '';
        $this->fk_user = '';
    }

    function get_ref()
    {
        return "{" . $this->mailbox_imap_host . "}";
    }

    function get_connector_url()
    {
        $mailbox_imap_connector_url = '{' . $this->mailbox_imap_host . ':' . $this->mailbox_imap_port;
        if ($this->mailbox_imap_ssl)
        {
            if ($this->mailbox_imap_ssl_novalidate_cert)
            {
                $mailbox_imap_connector_url .= '/ssl/novalidate-cert';
            }
            else
            {
                $mailbox_imap_connector_url .= '/ssl';
            }
        }
        $mailbox_imap_connector_url .= '}';

        return $mailbox_imap_connector_url;
    }

}

?>
