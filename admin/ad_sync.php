<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config.php';  // Ensure this path is correct
$config = require_once './ldap.config.php';

function logMessage($source, $message) {
    $logFile = '/var/log/ldap_sync.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] [$source] $message\n", FILE_APPEND);
}

function fetchFromLDAP($config) {
    $ldapconn = ldap_connect($config['ldap_server']);
    if (!$ldapconn) {
        logMessage("LDAP Connection", "Could not connect to LDAP server.");
        return false;
    }

    ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, $config['version']);
    if($config['use_tls']) {
        ldap_start_tls($ldapconn);
    }

    $bind = ldap_bind($ldapconn, $config['ldap_user'], $config['ldap_password']);
    if (!$bind) {
        logMessage("LDAP Connection", "Could not bind to LDAP server.");
        return false;
    }

$search = ldap_search($ldapconn, $config['base_dn'], "(samaccountname=*)");
    if (!$search) {
        logMessage("LDAP Fetch", "LDAP search failed.");
        return false;
    }

    $entries = ldap_get_entries($ldapconn, $search);
    ldap_unbind($ldapconn);
    return $entries;
}

function syncUsers($ldapUsers, $pdo) {
    $sql = "INSERT INTO users (first_name, last_name, email)
            VALUES (:firstName, :lastName, :email)
            ON DUPLICATE KEY UPDATE
            first_name = VALUES(first_name),
            last_name = VALUES(last_name),
            email = VALUES(email)";

    $stmt = $pdo->prepare($sql);
    if (!$stmt) {
        logMessage("Database Sync", "Prepare failed: " . $pdo->errorInfo()[2]);
        return;
    }

    foreach ($ldapUsers as $user) {
        $firstName = $user['givenname'][0] ?? null;
        $lastName = $user['sn'][0] ?? null;
        $email = $user['mail'][0] ?? null;

        if ($firstName && $lastName && $email) {
            $stmt->bindParam(':firstName', $firstName);
            $stmt->bindParam(':lastName', $lastName);
            $stmt->bindParam(':email', $email);

            if (!$stmt->execute()) {
                logMessage("Database Sync", "Execute failed: " . implode(", ", $stmt->errorInfo()));
            } else {
                logMessage("Database Sync", "Synced user: " . $email);
            }
        } else {
            logMessage("Database Sync", "Missing user information for a record, not synced.");
        }
    }
}

// Starting the script
logMessage("Script Status", "Script started.");

// Perform LDAP fetch
$ldapUsers = fetchFromLDAP($config);
if ($ldapUsers) {
    if(isset($pdo) && $pdo instanceof PDO) {
        syncUsers($ldapUsers, $pdo);
    } else {
        logMessage("Script Status", "Database connection not available.");
    }
} else {
    logMessage("Script Status", "No LDAP users fetched or an error occurred.");
}

// Script completion
logMessage("Script Status", "Script completed.");
?>
