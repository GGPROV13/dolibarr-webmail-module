<?php

require_once(dirname(__FILE__) . '/../lib/lib_dolimail.php');

class ActionsDolimail {

    /** Overloading the doActions function : replacing the parent's function with the one below 
     *  @param      parameters  meta datas of the hook (context, etc...) 
     *  @param      object             the object you want to process (an invoice if you are in invoice module, a propale in propale's module, etc...) 
     *  @param      action             current action (if set). Generally create or edit or null 
     *  @return       void 
     */
    function doActions($parameters, &$object, &$action, $hookmanager) {
        global $langs, $user, $db;

        if ($action == "send") {
            switch ($parameters['context']) {
                case 'propalcard':
                    if (!GETPOST('addfile') && !GETPOST('removedfile') && !GETPOST('cancel')) {
                        $langs->load('mails');

                        $result = $object->fetch(GETPOST("id"));
                        $result = $object->fetch_thirdparty();

                        if ($result > 0) {
                            if (GETPOST('sendto')) {
                                // Le destinataire a ete fourni via le champ libre
                                $sendto = GETPOST('sendto');
                                $sendtoid = 0;
                            } elseif (GETPOST('receiver') != '-1') {
                                // Recipient was provided from combo list
                                if (GETPOST('receiver') == 'thirdparty') { // Id of third party
                                    $sendto = $object->client->email;
                                    $sendtoid = 0;
                                } else { // Id du contact
                                    $sendto = $object->client->contact_get_property(GETPOST('receiver'), 'email');
                                    $sendtoid = GETPOST('receiver');
                                }
                            }

                            if (dol_strlen($sendto)) {
                                $langs->load("commercial");

                                $from = GETPOST('fromname') . ' <' . GETPOST('frommail') . '>';
                                $replyto = GETPOST('replytoname') . ' <' . GETPOST('replytomail') . '>';
                                $message = GETPOST('message');
                                $sendtocc = GETPOST('sendtocc');
                                $deliveryreceipt = GETPOST('deliveryreceipt');

                                if (GETPOST('action') == 'send') {
                                    if (dol_strlen(GETPOST('subject')))
                                        $subject = GETPOST('subject');
                                    else
                                        $subject = $langs->transnoentities('Propal') . ' ' . $object->ref;
                                    $actiontypecode = 'AC_PROP';
                                    $actionmsg = $langs->transnoentities('MailSentBy') . ' ' . $from . ' ' . $langs->transnoentities('To') . ' ' . $sendto . ".\n";
                                    if ($message) {
                                        $actionmsg.=$langs->transnoentities('MailTopic') . ": " . $subject . "\n";
                                        $actionmsg.=$langs->transnoentities('TextUsedInTheMessageBody') . ":\n";
                                        $actionmsg.=$message;
                                    }
                                    $actionmsg2 = $langs->transnoentities('Action' . $actiontypecode);
                                }

                                // Create form object
                                include_once(DOL_DOCUMENT_ROOT . '/core/class/html.formmail.class.php');
                                $formmail = new FormMail($db);

                                $attachedfiles = $formmail->get_attached_files();
                                $filepath = $attachedfiles['paths'];
                                $filename = $attachedfiles['names'];
                                $mimetype = $attachedfiles['mimes'];

                                // Envoi de la propal
                                require_once(DOL_DOCUMENT_ROOT . '/core/class/CMailFile.class.php');
                                $mailfile = new CMailFile($subject, $sendto, $from, $message, $filepath, $mimetype, $filename, $sendtocc, '', $deliveryreceipt);

                                if (!$mailfile->error) {
                                    $mailfile->message = stripslashes($mailfile->message);

                                    $msg_prepared = $mailfile->headers . $mailfile->eol;
                                    $msg_prepared .= $mailfile->subject . $mailfile->eol;
                                    $msg_prepared .= $mailfile->message . $mailfile->eol;

                                    $object->msg_imap_result = store_email_into_folder($msg_prepared);
                                }
                            }
                        }
                    }
                    break;
                case 'ordercard':
                    if (!GETPOST('addfile') && !GETPOST('removedfile') && !GETPOST('cancel')) {
                        $langs->load('mails');

                        $result = $object->fetch(GETPOST("id"));
                        $result = $object->fetch_thirdparty();

                        if ($result > 0) {
                            if (GETPOST('sendto')) {
                                // Le destinataire a ete fourni via le champ libre
                                $sendto = GETPOST('sendto');
                                $sendtoid = 0;
                            } elseif (GETPOST('receiver') != '-1') {
                                // Recipient was provided from combo list
                                if (GETPOST('receiver') == 'thirdparty') { // Id of third party
                                    $sendto = $object->client->email;
                                    $sendtoid = 0;
                                } else { // Id du contact
                                    $sendto = $object->client->contact_get_property(GETPOST('receiver'), 'email');
                                    $sendtoid = GETPOST('receiver');
                                }
                            }

                            if (dol_strlen($sendto)) {
                                $langs->load("commercial");

                                $from = GETPOST('fromname') . ' <' . GETPOST('frommail') . '>';
                                $replyto = GETPOST('replytoname') . ' <' . GETPOST('replytomail') . '>';
                                $message = GETPOST('message');
                                $sendtocc = GETPOST('sendtocc');
                                $deliveryreceipt = GETPOST('deliveryreceipt');

                                if (GETPOST('action') == 'send') {
                                    if (dol_strlen(GETPOST('subject')))
                                        $subject = GETPOST('subject');
                                    else
                                        $subject = $langs->transnoentities('Propal') . ' ' . $object->ref;
                                    $actiontypecode = 'AC_PROP';
                                    $actionmsg = $langs->transnoentities('MailSentBy') . ' ' . $from . ' ' . $langs->transnoentities('To') . ' ' . $sendto . ".\n";
                                    if ($message) {
                                        $actionmsg.=$langs->transnoentities('MailTopic') . ": " . $subject . "\n";
                                        $actionmsg.=$langs->transnoentities('TextUsedInTheMessageBody') . ":\n";
                                        $actionmsg.=$message;
                                    }
                                    $actionmsg2 = $langs->transnoentities('Action' . $actiontypecode);
                                }

                                // Create form object
                                include_once(DOL_DOCUMENT_ROOT . '/core/class/html.formmail.class.php');
                                $formmail = new FormMail($db);

                                $attachedfiles = $formmail->get_attached_files();
                                $filepath = $attachedfiles['paths'];
                                $filename = $attachedfiles['names'];
                                $mimetype = $attachedfiles['mimes'];

                                // Envoi de la propal
                                require_once(DOL_DOCUMENT_ROOT . '/core/class/CMailFile.class.php');
                                $mailfile = new CMailFile($subject, $sendto, $from, $message, $filepath, $mimetype, $filename, $sendtocc, '', $deliveryreceipt);

                                if (!$mailfile->error) {
                                    $mailfile->message = stripslashes($mailfile->message);

                                    $msg_prepared = $mailfile->headers . $mailfile->eol;
                                    $msg_prepared .= $mailfile->subject . $mailfile->eol;
                                    $msg_prepared .= $mailfile->message . $mailfile->eol;

                                    $object->msg_imap_result = store_email_into_folder($msg_prepared);
                                }
                            }
                        }
                    }
                    break;
                case 'invoicecard':
                    if (!GETPOST('addfile') && !GETPOST('removedfile') && !GETPOST('cancel')) {
                        $langs->load('mails');

                        $result = $object->fetch(GETPOST("id"));
                        $result = $object->fetch_thirdparty();

                        if ($result > 0) {
                            if (GETPOST('sendto')) {
                                // Le destinataire a ete fourni via le champ libre
                                $sendto = GETPOST('sendto');
                                $sendtoid = 0;
                            } elseif (GETPOST('receiver') != '-1') {
                                // Recipient was provided from combo list
                                if (GETPOST('receiver') == 'thirdparty') { // Id of third party
                                    $sendto = $object->client->email;
                                    $sendtoid = 0;
                                } else { // Id du contact
                                    $sendto = $object->client->contact_get_property(GETPOST('receiver'), 'email');
                                    $sendtoid = GETPOST('receiver');
                                }
                            }

                            if (dol_strlen($sendto)) {
                                $langs->load("commercial");

                                $from = GETPOST('fromname') . ' <' . GETPOST('frommail') . '>';
                                $replyto = GETPOST('replytoname') . ' <' . GETPOST('replytomail') . '>';
                                $message = GETPOST('message');
                                $sendtocc = GETPOST('sendtocc');
                                $deliveryreceipt = GETPOST('deliveryreceipt');

                                if (GETPOST('action') == 'send') {
                                    if (dol_strlen(GETPOST('subject')))
                                        $subject = GETPOST('subject');
                                    else
                                        $subject = $langs->transnoentities('Propal') . ' ' . $object->ref;
                                    $actiontypecode = 'AC_PROP';
                                    $actionmsg = $langs->transnoentities('MailSentBy') . ' ' . $from . ' ' . $langs->transnoentities('To') . ' ' . $sendto . ".\n";
                                    if ($message) {
                                        $actionmsg.=$langs->transnoentities('MailTopic') . ": " . $subject . "\n";
                                        $actionmsg.=$langs->transnoentities('TextUsedInTheMessageBody') . ":\n";
                                        $actionmsg.=$message;
                                    }
                                    $actionmsg2 = $langs->transnoentities('Action' . $actiontypecode);
                                }

                                // Create form object
                                include_once(DOL_DOCUMENT_ROOT . '/core/class/html.formmail.class.php');
                                $formmail = new FormMail($db);

                                $attachedfiles = $formmail->get_attached_files();
                                $filepath = $attachedfiles['paths'];
                                $filename = $attachedfiles['names'];
                                $mimetype = $attachedfiles['mimes'];

                                // Envoi de la propal
                                require_once(DOL_DOCUMENT_ROOT . '/core/class/CMailFile.class.php');
                                $mailfile = new CMailFile($subject, $sendto, $from, $message, $filepath, $mimetype, $filename, $sendtocc, '', $deliveryreceipt);

                                if (!$mailfile->error) {
                                    $mailfile->message = stripslashes($mailfile->message);

                                    $msg_prepared = $mailfile->headers . $mailfile->eol;
                                    $msg_prepared .= $mailfile->subject . $mailfile->eol;
                                    $msg_prepared .= $mailfile->message . $mailfile->eol;


                                    $object->msg_imap_result = store_email_into_folder($msg_prepared);
                                }
                            }
                        }
                    }
                    break;
                case 'ordersuppliercard':
                    if (!GETPOST('addfile') && !GETPOST('removedfile') && !GETPOST('cancel')) {
                        $langs->load('mails');

                        $result = $object->fetch(GETPOST("id"));
                        $result = $object->fetch_thirdparty();

                        if ($result > 0) {
                            if (GETPOST('sendto')) {
                                // Le destinataire a ete fourni via le champ libre
                                $sendto = GETPOST('sendto');
                                $sendtoid = 0;
                            } elseif (GETPOST('receiver') != '-1') {
                                // Recipient was provided from combo list
                                if (GETPOST('receiver') == 'thirdparty') { // Id of third party
                                    $sendto = $object->client->email;
                                    $sendtoid = 0;
                                } else { // Id du contact
                                    $sendto = $object->client->contact_get_property(GETPOST('receiver'), 'email');
                                    $sendtoid = GETPOST('receiver');
                                }
                            }

                            if (dol_strlen($sendto)) {
                                $langs->load("commercial");

                                $from = GETPOST('fromname') . ' <' . GETPOST('frommail') . '>';
                                $replyto = GETPOST('replytoname') . ' <' . GETPOST('replytomail') . '>';
                                $message = GETPOST('message');
                                $sendtocc = GETPOST('sendtocc');
                                $deliveryreceipt = GETPOST('deliveryreceipt');

                                if ($_POST['action'] == 'send') {
                                    if (dol_strlen(GETPOST('subject')))
                                        $subject = GETPOST('subject');
                                    else
                                        $subject = $langs->transnoentities('Propal') . ' ' . $object->ref;
                                    $actiontypecode = 'AC_PROP';
                                    $actionmsg = $langs->transnoentities('MailSentBy') . ' ' . $from . ' ' . $langs->transnoentities('To') . ' ' . $sendto . ".\n";
                                    if ($message) {
                                        $actionmsg.=$langs->transnoentities('MailTopic') . ": " . $subject . "\n";
                                        $actionmsg.=$langs->transnoentities('TextUsedInTheMessageBody') . ":\n";
                                        $actionmsg.=$message;
                                    }
                                    $actionmsg2 = $langs->transnoentities('Action' . $actiontypecode);
                                }

                                // Create form object
                                include_once(DOL_DOCUMENT_ROOT . '/core/class/html.formmail.class.php');
                                $formmail = new FormMail($db);

                                $attachedfiles = $formmail->get_attached_files();
                                $filepath = $attachedfiles['paths'];
                                $filename = $attachedfiles['names'];
                                $mimetype = $attachedfiles['mimes'];

                                // Envoi de la propal
                                require_once(DOL_DOCUMENT_ROOT . '/core/class/CMailFile.class.php');
                                $mailfile = new CMailFile($subject, $sendto, $from, $message, $filepath, $mimetype, $filename, $sendtocc, '', $deliveryreceipt);

                                if (!$mailfile->error) {
                                    $mailfile->message = stripslashes($mailfile->message);

                                    $msg_prepared = $mailfile->headers . $mailfile->eol;
                                    $msg_prepared .= $mailfile->subject . $mailfile->eol;
                                    $msg_prepared .= $mailfile->message . $mailfile->eol;


                                    $object->msg_imap_result = store_email_into_folder($msg_prepared);
                                }
                            }
                        }
                    }
                    break;
                case 'invoicesuppliercard':
                    if (!GETPOST('addfile') && !GETPOST('removedfile') && !GETPOST('cancel')) {
                        $langs->load('mails');

                        $result = $object->fetch(GETPOST("id"));
                        $result = $object->fetch_thirdparty();

                        if ($result > 0) {
                            if (GETPOST('sendto')) {
                                // Le destinataire a ete fourni via le champ libre
                                $sendto = GETPOST('sendto');
                                $sendtoid = 0;
                            } elseif (GETPOST('receiver') != '-1') {
                                // Recipient was provided from combo list
                                if (GETPOST('receiver') == 'thirdparty') { // Id of third party
                                    $sendto = $object->client->email;
                                    $sendtoid = 0;
                                } else { // Id du contact
                                    $sendto = $object->client->contact_get_property(GETPOST('receiver'), 'email');
                                    $sendtoid = GETPOST('receiver');
                                }
                            }

                            if (dol_strlen($sendto)) {
                                $langs->load("commercial");

                                $from = GETPOST('fromname') . ' <' . GETPOST('frommail') . '>';
                                $replyto = GETPOST('replytoname') . ' <' . GETPOST('replytomail') . '>';
                                $message = GETPOST('message');
                                $sendtocc = GETPOST('sendtocc');
                                $deliveryreceipt = GETPOST('deliveryreceipt');

                                if (GETPOST('action') == 'send') {
                                    if (dol_strlen(GETPOST('subject')))
                                        $subject = GETPOST('subject');
                                    else
                                        $subject = $langs->transnoentities('Propal') . ' ' . $object->ref;
                                    $actiontypecode = 'AC_PROP';
                                    $actionmsg = $langs->transnoentities('MailSentBy') . ' ' . $from . ' ' . $langs->transnoentities('To') . ' ' . $sendto . ".\n";
                                    if ($message) {
                                        $actionmsg.=$langs->transnoentities('MailTopic') . ": " . $subject . "\n";
                                        $actionmsg.=$langs->transnoentities('TextUsedInTheMessageBody') . ":\n";
                                        $actionmsg.=$message;
                                    }
                                    $actionmsg2 = $langs->transnoentities('Action' . $actiontypecode);
                                }

                                // Create form object
                                include_once(DOL_DOCUMENT_ROOT . '/core/class/html.formmail.class.php');
                                $formmail = new FormMail($db);

                                $attachedfiles = $formmail->get_attached_files();
                                $filepath = $attachedfiles['paths'];
                                $filename = $attachedfiles['names'];
                                $mimetype = $attachedfiles['mimes'];

                                // Envoi de la propal
                                require_once(DOL_DOCUMENT_ROOT . '/core/class/CMailFile.class.php');
                                $mailfile = new CMailFile($subject, $sendto, $from, $message, $filepath, $mimetype, $filename, $sendtocc, '', $deliveryreceipt);

                                if (!$mailfile->error) {
                                    $mailfile->message = stripslashes($mailfile->message);

                                    $msg_prepared = $mailfile->headers . $mailfile->eol;
                                    $msg_prepared .= $mailfile->subject . $mailfile->eol;
                                    $msg_prepared .= $mailfile->message . $mailfile->eol;

                                    $object->msg_imap_result = store_email_into_folder($msg_prepared);
                                }
                            }
                        }
                    }
                    break;
            }
        }
    }

}

?>