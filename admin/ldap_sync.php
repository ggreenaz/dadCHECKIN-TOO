<?php
require_once '../config.php';
$config = require_once './ldap.config.php';

function logMessage($source, $message) {
    $logFile = '/var/log/ldap_sync.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] [$source] $message\n", FILE_APPEND);
}

function fetchFromLDAP($config) {
    logMessage("LDAP Fetch", "Fetching user data from LDAP.");
    $ldapconn = ldap_connect($config['ldap_server']);

    if (!$ldapconn) {
        logMessage("LDAP Fetch", "Could not connect to LDAP server.");
        return false;
    }

    ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
    if (isset($config['use_tls']) && $config['use_tls']) {
        ldap_start_tls($ldapconn);
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
        ldap_control_paged_result($ldapconn, $pageSize, true, $cookie);

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

        ldap_control_paged_result_response($ldapconn, $result, $cookie);
    } while($cookie !== null && $cookie != '');

    ldap_unbind($ldapconn);
    logMessage("LDAP Fetch", "Fetched " . count($allEntries) . " entries from LDAP.");
    return $allEntries;
}

function syncUsers($users) {
    global $pdo;  // Use the PDO instance from config.php

    foreach ($users as $user) {
        $firstName = $user['givenname'][0] ?? null;
        $lastName = $user['sn'][0] ?? null;
        $email = $user['mail'][0] ?? null;

        if ($firstName && $lastName && $email) {
            $sql = "INSERT INTO users (first_name, last_name, email) VALUES (:first_name, :last_name, :email)
                    ON DUPLICATE KEY UPDATE first_name=:first_name, last_name=:last_name, email=:email";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([':first_name' => $firstName, ':last_name' => $lastName, ':email' => $email]);
            logMessage("Database Sync", "Synced user: " . $email);
        } else {
            logMessage("Database Sync", "Missing user information: " . json_encode($user));
        }
    }
}

logMessage("LDAP Sync Script", "Script started.");
$ldapUsers = fetchFromLDAP($config);
if ($ldapUsers) {
    syncUsers($ldapUsers);
}
logMessage("LDAP Sync Script", "Script completed.");
?>

