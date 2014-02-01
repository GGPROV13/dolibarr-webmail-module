<?php

require_once(dirname(__FILE__) . '/../class/usermailboxconfig.class.php');

function store_email_into_folder($msg, $folder = 'SentFromDolibarr') {
    global $user, $db;
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

    $mbox = imap_open($user->mailbox_imap_connector_url . $folder, $user->mailbox_imap_login, $user->mailbox_imap_password);

    $check = imap_check($mbox);
    $before = $check->Nmsgs;

    $result = imap_append($mbox, $user->mailbox_imap_connector_url . $folder
            , $msg
    );
    
            $check = imap_check($mbox);
            $after = $check->Nmsgs;

    if ($result == FALSE) {
        if (imap_createmailbox($mbox, imap_utf7_encode($user->mailbox_imap_ref . $folder))) {
            $mbox = imap_open($user->mailbox_imap_connector_url . $folder, $user->mailbox_imap_login, $user->mailbox_imap_password);

            $check = imap_check($mbox);
            $before = $check->Nmsgs;

            $result = imap_append($mbox, $user->mailbox_imap_connector_url . $folder
                    , $msg
            );

            $check = imap_check($mbox);
            $after = $check->Nmsgs;
        }
    }
    imap_close($mbox);
}

function sanitize_imap_folder($folder) {
    return str_replace("?;", "&nbsp;", utf8_encode(mb_convert_encoding($folder, "ISO_8859-1", "UTF7-IMAP")));
}

function getmsg($mbox, $mid) {
    // input $mbox = IMAP stream, $mid = message id
    // output all the following:
    $htmlmsg = $plainmsg = $charset = '';
    $attachments = array();

    // add code here to get date, from, to, cc, subject...
    // BODY
    $s = imap_fetchstructure($mbox, $mid, FT_UID);
    if (!$s->parts)  // simple
        list($charset, $htmlmsg, $plainmsg, $attachments) = getpart($mbox, $mid, $s, 0, $charset, $htmlmsg, $plainmsg, $attachments);  // pass 0 as part-number
    else {  // multipart: cycle through each part
        foreach ($s->parts as $partno0 => $p)
            list($charset, $htmlmsg, $plainmsg, $attachments) = getpart($mbox, $mid, $p, $partno0 + 1, $charset, $htmlmsg, $plainmsg, $attachments);
    }
    return array($charset, $htmlmsg, $plainmsg, $attachments);
}

function getpart($mbox, $mid, $p, $partno, $charset, $htmlmsg, $plainmsg, $attachments) {
    // $partno = '1', '2', '2.1', '2.1.3', etc for multipart, 0 if simple
    // DECODE DATA
    if ($p->encoding != 3 || $partno < 2)
        $data = ($partno) ?
                imap_fetchbody($mbox, $mid, $partno, FT_UID) : // multipart
                imap_body($mbox, $mid, FT_UID);  // simple







        
// Any part may be encoded, even plain text messages, so check everything.
    if ($p->encoding == 4)
        $data = quoted_printable_decode($data);
    elseif ($p->encoding == 3)
        $data = base64_decode($data);

    // PARAMETERS
    // get all parameters, like charset, filenames of attachments, etc.
    $params = array();
    if ($p->parameters)
        foreach ($p->parameters as $x)
            $params[strtolower($x->attribute)] = $x->value;
    if ($p->dparameters)
        foreach ($p->dparameters as $x)
            $params[strtolower($x->attribute)] = $x->value;

    // ATTACHMENT
    // Any part with a filename is an attachment,
    // so an attached text file (type 0) is not mistaken as the message.
    if ($params['filename'] || $params['name']) {
        // filename may be given as 'Filename' or 'Name' or both
        $filename = ($params['filename']) ? $params['filename'] : $params['name'];
        // filename may be encoded, so see imap_mime_header_decode()
        $attachments[$filename] = $data;  // this is a problem if two files have same name
    }

    // TEXT
    if ($p->type == 0 && $data) {
        // Messages may be split in different parts because of inline attachments,
        // so append parts together with blank row.
        if (strtolower($p->subtype) == 'plain')
            $plainmsg .= trim($data) . "\n\n";
        else
            $htmlmsg .= $data . "<br /><br />";
        $charset = $params['charset'];  // assume all parts are same charset
    }

    // EMBEDDED MESSAGE
    // Many bounce notifications embed the original message as type 2,
    // but AOL uses type 1 (multipart), which is not handled here.
    // There are no PHP functions to parse embedded messages,
    // so this just appends the raw source to the main message.
    elseif ($p->type == 2 && $data) {
        $plainmsg .= $data . "\n\n";
    }

    // SUBPART RECURSION
    if ($p->parts) {
        foreach ($p->parts as $partno0 => $p2)
            list($charset, $htmlmsg, $plainmsg, $attachments) = getpart($mbox, $mid, $p2, $partno . '.' . ($partno0 + 1), $charset, $htmlmsg, $plainmsg, $attachments);  // 1.2, 1.2.1, etc.
    }
    return array($charset, $htmlmsg, $plainmsg, $attachments);
}

?>