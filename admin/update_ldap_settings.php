<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);


$config = include './ldap.config.php';

function testLDAPConnection($config) {
    $ldapconn = ldap_connect($config['ldap_server']);
    if (!$ldapconn) {
        echo "<p style='color: red;'>Could not connect to LDAP server.</p>";
        return;
    }

    ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);

    $bind = @ldap_bind($ldapconn, $config['ldap_user'], $config['ldap_password']);
    if ($bind) {
        echo "<p style='color: green;'>LDAP Connection and Bind Successful.</p>";
        ldap_unbind($ldapconn);
    } else {
        echo "<p style='color: red;'>LDAP Connection Successful, but Bind Failed.</p>";
        echo "<p>Error: " . ldap_error($ldapconn) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>LDAP Configuration Details</title>
    <link rel="stylesheet" type="text/css" href="theme.php">
    <style>
        /* Center-align the edit tables */
        table.edit-table {
            width: 40%;
            margin: 0 auto;
        }

        /* Adjust table styles for existing visiting persons and visit reasons */
        table.existing-table {
            width: 50%; /* Set both tables to be 50% of the page */
            border-collapse: collapse;
            margin: 0 auto; /* Center-align the tables */
        }

        table.existing-table th,
        table.existing-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        /* Ensure uniform width for action buttons */
        table.existing-table .actions {
            width: 150px;
        }
    </style>
</head>
<body>
    <h2>LDAP Configuration Details</h2>
    <img src="../img/dnd-project-sm-logo.png">
    <h2><a href="./" class="button">Return to Admin Dashboard</a></h2>

    <?php if ($config && is_array($config)): ?>
        <table class="centered-table edit-table">
            <?php foreach ($config as $key => $value): ?>
                <tr>
                    <td><?php echo htmlspecialchars($key); ?></td>
                    <td>
                        <?php
                        if ($key === 'use_tls') {
                            echo $value ? 'Yes' : 'No';
                        } elseif ($key === 'search_subcontexts' || $key === 'dereference_aliases') {
                            echo $value ? 'Yes' : 'No';
                        } else {
                            echo htmlspecialchars($value);
                        }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <form method="post">
	<p><p><p>

            <input type="submit" name="test_ldap" value="Test Your LDAP Connection">
        </form>

        <?php
        if (isset($_POST['test_ldap'])) {
            testLDAPConnection($config);
        }
        ?>

    <?php else: ?>
        <p>No configuration found or invalid.</p>
    <?php endif; ?>
</body>
</html>

