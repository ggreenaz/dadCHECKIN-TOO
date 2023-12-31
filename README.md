# dadCHECKIN-TOO
Introducing dadCHECKIN-TOO: Simple Check-In for Organizations

🌟 dadCHECKIN-TOO: Simplify Check-Ins for Everyone!

Imagine a check-in system so user-friendly even your tech-challenged dad can handle it without calling for help. Introducing dadCHECKIN-TOO – the hassle-free check-in/check-out solution designed for organizations with email addresses from school or work. It's as straightforward as dad's jokes! Whether you're managing events, offices, or school activities, dadCHECKIN-TOO simplifies visitor tracking without complex reports or confusing code. Easy setup – no reading glasses required!

What's the secret behind its simplicity?

dadCHECKIN-TOO cuts through the clutter, focusing solely on what matters: who's visiting, when, and why. No bells, no whistles, just the essentials. It's like your dad's approach to social media – he might not understand hashtags, but he sure knows how to hit 'Like'!

Key Features:

    DIFFERENT FROM dadCHECKIN: 

    Administrator Upload: Designed for organizations, administrators can easily upload user data.
    Email Prefix Login: Users simply supply their email prefix to log in and out.
    Dad-Proof Design: Robust enough to withstand accidental coffee spills and those infamous 'just tinkering' sessions.

Ideal For:

    Schools, offices, events – anywhere that needs a simple visitor log.
    People who appreciate dad-level simplicity (and dad jokes!).

In a world of complex software, dadCHECKIN-TOO is your simple oasis. It proudly says, "So easy, even a dad can use it!" If you chuckled, you're our target audience! Join the dadCHECKIN-TOO revolution – where simplicity meets functionality, and dad jokes are welcome! 👨‍💼


// Install Directions //

Let's go ahead and get started.

Upload the latest version of dadCHECKIN-TOO to your web server's root directory. Be sure to set your directory privileges so your web servers has acess. For example:

    chown -R www-data:www-data /path/to/dadCHECKIN-TOO 

If  you are new to the process of setting permissions on directories, read the short description below. If you are a SaltyDog, move on.
On an Ubuntu Linux web server, the recommended file permissions for the /var/www/html directory, which typically contains web content, are as follows:

Directories: 755 (drwxr-xr-x): This setting allows the owner to read, write, and execute, while the group and others can only read and execute. This is important for allowing web server processes to access and serve the directory contents.
Files: 644 (rw-r--r--): This means the owner can read and write the files, but the group and others can only read them. This ensures that web server processes can serve these files without unnecessary write permissions, which is a good security practice.

In Ubuntu Linux, the recommended file permissions for the /var/www/html directory are typically as follows:

    Directories: 755 (drwxr-xr-x)
    Files: 644 (rw-r--r--)


When you are sure of your file permissions, set them to (in my example): 

To set directory permissions to 755 (drwxr-xr-x):

    sudo find /var/www/html -type d -exec chmod 755 {} \;

To set file permissions to 644 (rw-r--r--):

    sudo find /var/www/html -type f -exec chmod 644 {} \;

    
It's crucial to set these permissions correctly to balance security and functionality.  Too restrictive permissions can prevent the web server from accessing these files, while too permissive settings can pose a security risk.

Let's run the installation script after you have set your ownership and directory permissions accordingly. 

// HOSTED SITES NOTE: In some cases, you will may struggle with getting the install script to run on hosted sites. I have included a default.config.php file for you to edit manually. Keep in mind, if you are hosting OnPrem you most likelly not have an issue with the install script, however you may run into issues on hosted websites. Edit this file manually to ensure you can run the software without having to "programmatically" have it done for you.

Also, I want to talk about the paths to ../img and ../css/ directories. You'll need to configure those as well based on the web root of your site. I am putting those paths in for many users, but NOT ALL USERS. If the look of the site does not reflect the photos in the Wiki, the /css/styles.css file is not configured well. Check that out.

If you are getting broken images, the same may be true for the /img/ direcotry. Look at how your scripts are pointing to your img/ direcotory. Now, onto our redullarly schecduled install. \\


Point your web browser to:

    http(s)://localhost/install/install.php

You will be asked to provide your database credentials to your database. This README is assuming you have already set those before you try to run the Install script. If you need help on that, this site is a good starting point, but you do  you! https://www.hostinger.com/tutorials/mysql/how-create-mysql-user-and-grant-permissions-command-line
    
        You can start the installation by hitting the Install button. 

You will need to populate your database with the desired information for your dropdown menus. You will see the button that allows you to add Persons and Reasons for the visit. Point your browser to:

    http(s)://localhost/admin/

Add your data to the database.

IF YOU WANT TO USE LDAP/Active Directory Connection: 

You can use the LDAP/Active Directory configuration script or use the example below by configuring this manually.  You have a setup page called settings.php, where you need to enter all your data to have the ldap.config.php file created. In that file, you will need to fill out the information below:
    
    <?php
    return array (
      'ldap_server' => 'ldap://192.168.1.10',
      'ldap_user' => 'ldapsearch@youdomain.org',
      'ldap_password' => 'ldap_pass',
      'version' => '3',
      'use_tls' => false,
      'user_type' => 'ActiveDirectory',
      'search_subcontexts' => true,
      'dereference_aliases' => true,
      'user_attribute' => 'samaccountname',
      'base_dn' => 'cn=ldapuser,ou=public,o=org',
    );
    ?>

On you are done, you will need to set a cron job 

    0 0 * * * /usr/bin/php /path/to/ldap_sync.php

In most cases, if you are on a Linux server, it would be 

    0 0 * * * /usr/bin/php /var/www/html/admin/ldap_sync.php

NOTE: I want to add that for your cron job, based upon your distribution, you may need specify in your ldap_sync.php file the absolute path in your code. I have made the paths relative, and this cold cause you issues with your running the cron job. Therefore, if your cron breaks, make your paths absolute, based upon the example I have in my ldap_sync.php below.

    <?php
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    
    require_once '/var/www/html/config.php';  // Ensure this path is correct
    $config = require_once '/var/www/html/admin/ldap.config.php';
    
    function logMessage($source, $message) {
        $logFile = '/var/log/ldap_sync.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[$timestamp] [$source] $message\n", FILE_APPEND);
    }
    
Once you have filled out the settings.php credentials, you will redirected to the  update_ldap_settings.php page, where you can test your connection. 

For the first time, many people will populate their database by running a CLI command from the directory /path/to/dad/admin/: 

    php ./ldap_sync.php > /var/log/ldap_sync.log 2>&1 &

This assumes you want to get the logs of your output sent to ldap_sync.log. That file needs to be created manually before that. This script will not do that automatically. 

Once you confirm that your server is bound to your Active Directory, you should delete the entire contents of directories and either change the permissions on the update_ldap_settings.php file or delete it thoroughly to ensure the user maintains the security of your installation. 

    /install/
    update_ldap_settings.php


MANUALLY UPLOAD Data

There is an example CSV template for you to use to fill in the user information you will need. Be sure to save your CSV file using UTF8. Once you have uploaded your data you will be able to use dadCHECKIN-TOO. 


In future distributions of dadCHECKIN-TOO, I plan to add the authentication to protect your admin/ directory, but that will come a bit later unless you want to do that work and contribute. Love to have. In the meantime, we are going to do this with a simple, and yes, I know, unsophisticated, use of the .htacces process.  

The primary reason for using .htaccess for basic authentication is to add a layer of security to your web directories. By requiring a username and password, you can restrict access to your admin/ directory.

I am suggesting this for now because of the ease of use and because I have not written this into the database yet.  Implementing basic authentication via .htaccess is straightforward and doesn't require extensive configuration changes in the main Apache configuration files. All of this is to say that you can apply these settings to specific directories without impacting the security or functionality of other parts of your website.
Control: It allows for decentralized management of access control. Different directories can have different authentication requirements.

Instructions for Password Protecting a Directory
Step 1: Create the .htaccess File

Open a terminal on your Ubuntu server. In this example, I am assuming that you will put your files in the /var/www/html web root, but you may want to create a subdirectory like/var/www/html/dad/. In the end, you decide, Okay?

Navigate to your web directory:

    cd /var/www/html/admin

Create the .htaccess file:

    sudo nano .htaccess

Enter the following code into the file:


    AuthType Basic
    AuthName "Restricted Access"
    AuthUserFile /etc/apache2/.htpasswd
    Require valid-user

Save and exit the editor (in nano, press CTRL + X, then Y, and Enter).

Step 2: Create the .htpasswd File (You'll need to create a .htpasswd file to store usernames and passwords. Use the htpasswd utility for this. If it's not installed, install it using);

    sudo apt-get install apache2-utils

--> (I know, it's old school)

Create the .htpasswd file and add a user (replace username with your desired username):

    sudo htpasswd -c /etc/apache2/.htpasswd your_username

You'll be prompted to enter and confirm the password.

Step 3: Update Apache Configuration

Ensure that your Apache configuration allows .htaccess overrides. Edit your Apache configuration file for the site:

    sudo nano /etc/apache2/sites-available/000-default.conf

Inside the section --> <Directory /var/www/html> section, add or modify the line:

    <VirtualHost *:80>
        <Directory /var/www/html>
            AllowOverride All
        </Directory>
        ServerName dad.garlandgreen.com (example)
        ServerAdmin dad@garlandgreen.com (example)
        DocumentRoot /var/www/html
        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog ${APACHE_LOG_DIR}/access.log combined
    </VirtualHost>


Save and exit the editor.
I suggest you check your configuration file to make sure there are no syntax errors by running:

    sudo apache2ctl configtest

Step 4: Restart Apache:

    sudo systemctl restart apache2

To apply the changes, restart Apache:

// Test the Configuration

Open a web browser and navigate to the protected directory (e.g., http://yourserver.com/admin).

A login prompt should appear. Enter the username and password you created.

This setup will protect your /var/www/html/admin directory with basic authentication, restricting access to authorized users only. To remind you, basic authentication transmits credentials in an encoded but not encrypted form, so it's best to use it in conjunction with SSL/TLS for enhanced security.

Okay, that's it for now. If you need to connect with me, I have for you my deets below.

Enjoy

Garland H. Green Jr.

    dad@garlandgreen.com
    https://dad.garlandgreen.com

