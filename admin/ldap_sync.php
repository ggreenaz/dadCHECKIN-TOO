<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config.php';  // Make sure this path is correct
$config = require_once './ldap.config.php';

function logMessage($source, $message) {
    $logFile = '/var/log/ldap_sync.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] [$source] $message\n", FILE_APPEND);
}

function fetchFromLDAP($config) {
    logMessage("LDAP Fetch", "Starting LDAP fetch process.");
    $ldapconn = ldap_connect($config['ldap_server']);

    if (!$ldapconn) {
        logMessage("LDAP Fetch", "Could not connect to LDAP server.");
        return false;
    }

    ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
    if (isset($config['use_tls']) && $config['use_tls']) {
        if (!ldap_start_tls($ldapconn)) {
            logMessage("LDAP Fetch", "Failed to start TLS.");
            return false;
        }
    }

    if (!ldap_bind($ldapconn, $config['ldap_user'], $config['ldap_password'])) {
        logMessage("LDAP Fetch", "LDAP bind failed: " . ldap_error($ldapconn));
        return false;
    }

    $searchFilter = "(objectclass=user)";
    $justthese = array("ou", "sn", "givenname", "mail");

    $pageSize = 1000;
    $cookie = '';
    $allEntries = array();

    do {
        $control = array(
            array(
                "oid" => LDAP_CONTROL_PAGEDRESULTS,
                "value" => array("size" => $pageSize, "cookie" => $cookie)
            )
        );

        ldap_set_option($ldapconn, LDAP_OPT_SERVER_CONTROLS, $control);

        $result = ldap_search($ldapconn, $config['base_dn'], $searchFilter, $justthese);
        if (!$result) {
            logMessage("LDAP Fetch", "LDAP search failed: " . ldap_error($ldapconn));
            break;
        }

        $entries = ldap_get_entries($ldapconn, $result);
        foreach ($entries as $e) {
            if (is_array($e)) {
                $allEntries[] = $e;
            }
        }

        ldap_parse_result($ldapconn, $result, $errcode, $matcheddn, $errmsg, $referrals, $controls);
        $cookie = $controls[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie'] ?? '';

    } while (!empty($cookie));

    ldap_unbind($ldapconn);
    logMessage("LDAP Fetch", "Fetched " . count($allEntries) . " entries from LDAP.");

    if (count($allEntries) == 0) {
        logMessage("LDAP Fetch", "No LDAP entries were fetched.");
        return false;
    }

    return $allEntries;
}


function syncUsers($ldapUsers) {
    $conn = getDBConnection(); // Get MySQLi connection from config.php

    $sql = "INSERT INTO users (first_name, last_name, email) VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE first_name=?, last_name=?, email=?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        logMessage("Database Sync", "Prepare failed: " . $conn->error);
        return;
    }

    foreach ($ldapUsers as $user) {
        // Ensure these attribute names match your LDAP attributes
        $firstName = $user['givenname'][0] ?? null;
        $lastName = $user['sn'][0] ?? null;
        $email = $user['mail'][0] ?? null;

        if ($firstName && $lastName && $email) {
            $stmt->bind_param("ssssss", $firstName, $lastName, $email, $firstName, $lastName, $email);
            if (!$stmt->execute()) {
                logMessage("Database Sync", "Execute failed: " . $stmt->error);
            } else {
                logMessage("Database Sync", "Synced user: " . $email);
            }
        } else {
            logMessage("Database Sync", "Missing user information for a record, not synced.");
        }
    }

    $stmt->close();
    $conn->close();
}



// Starting the script
logMessage("Script Status", "Script started.");

// Perform LDAP fetch
$ldapUsers = fetchFromLDAP($config);
if ($ldapUsers) {
    syncUsers($ldapUsers);
} else {
    logMessage("Script Status", "No LDAP users fetched or an error occurred.");
}

// Script completion
logMessage("Script Status", "Script completed.");
?>
