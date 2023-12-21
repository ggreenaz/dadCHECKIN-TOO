<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Function to write the configuration to a file
function writeConfigToFile($config) {
    $configData = "<?php\nreturn " . var_export($config, true) . ";\n?>";
    // Attempt to write the file and capture the result
    $result = file_put_contents('ldap.config.php', $configData);
    // Return true if successful, false otherwise
    return $result !== false;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ldap_config = [
        'ldap_server' => $_POST["ldap_server"] ?? 'ldap://your-ldap-server.com',
        'ldap_user' => $_POST["ldap_user"] ?? 'cn=read-only-admin,dc=example,dc=com',
        'ldap_password' => $_POST["ldap_password"] ?? 'password',
        'version' => $_POST["version"] ?? 3,
        'use_tls' => $_POST["use_tls"] === 'yes', // Converts 'yes' to true, 'no' to false
        'user_type' => $_POST["user_type"] ?? 'ActiveDirectory',
        'search_subcontexts' => isset($_POST["search_subcontexts"]) && $_POST["search_subcontexts"] === 'yes',
        'dereference_aliases' => isset($_POST["dereference_aliases"]) && $_POST["dereference_aliases"] === 'yes',
        'user_attribute' => $_POST["user_attribute"] ?? 'uid',
        'base_dn' => $_POST["base_dn"] ?? 'dc=example,dc=com' // Base DN field
    ];

    if (!writeConfigToFile($ldap_config)) {
        // Handle the error, e.g., by displaying a message
        echo "Error: Unable to write configuration file.";
        exit();
    }

    // Redirect to update_ldap_settings.php if file write is successful
    header('Location: update_ldap_settings.php');
    exit();
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>LDAP Settings Configuration</title>
    <link rel="stylesheet" type="text/css" href="./theme.php">
    <link rel="stylesheet" type="text/css" href="admin/theme.php">
    <!-- Add your additional styles if necessary -->
</head>
<body>
    <h2>Update LDAP Settings</h2>
    <h2><a href="./" class="button">Return to Admin Dashboard</a></h2>

    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <table class="edit-table">
            <!-- Original LDAP Configuration Fields -->
            <tr>
                <td><label for="ldap_server">Host URL:</label></td>
                <td><input type="text" id="ldap_server" name="ldap_server" required></td>
            </tr>
            <tr>
                <td><label for="ldap_user">Distinguished name:</label></td>
                <td><input type="text" id="ldap_user" name="ldap_user" required></td>
            </tr>
            <tr>
                <td><label for="ldap_password">LDAP Password:</label></td>
                <td><input type="password" id="ldap_password" name="ldap_password" required></td>
            </tr>
            <!-- New LDAP Configuration Fields -->
            <tr>
                <td><label for="version">Version:</label></td>
                <td>
                    <select id="version" name="version">
                        <option value="2">Version 2</option>
                        <option value="3">Version 3</option>
                    </select>
                </td>
            </tr>
	    <tr>
    		<td><label for="use_tls">Use TLS:</label></td>
    		<td>
        		<select id="use_tls" name="use_tls">
            		<option value="yes">Yes</option>
            		<option value="no">No</option>
        	</select>
    		</td>
		</tr>
            <tr>
                <td><label for="user_type">User Type:</label></td>
                <td>
                    <select id="user_type" name="user_type">
                        <option value="ActiveDirectory">ActiveDirectory</option>
                        <option value="sambaSamAccount">sambaSamAccount</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td><label for="contexts"><b>Contexts:</b> Contexts:</label></td>
                <td><input type="text" id="contexts" name="contexts"></td>
            </tr>
            <tr>
                <td><label for="search_subcontexts">Search Subcontexts:</label></td>
                <td>
                    <select id="search_subcontexts" name="search_subcontexts">
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td><label for="dereference_aliases">Dereference Aliases:</label></td>
                <td>
                    <select id="dereference_aliases" name="dereference_aliases">
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>
                </td>
            </tr>

	    <tr>
		    <td><label for="base_dn">Contexts (Base DN):</label></td>
		    <td><input type="text" id="base_dn" name="base_dn"></td>
	    </tr>

            <tr>
                <td><label for="user_attribute">User Attribute:</label></td>
                <td><input type="text" id="user_attribute" name="user_attribute"></td>
            </tr>
            <!-- Submit Button -->
            <tr>
                <td colspan="2"><input type="submit" value="Update Settings"></td>
            </tr>
        </table>
    </form>

</body>
</html>

