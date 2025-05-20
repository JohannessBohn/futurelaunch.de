<?php
$sendmail_ini_path = 'C:/xampp/sendmail/sendmail.ini';
$config = <<<INI
[sendmail]
smtp_server=smtp.gmail.com
smtp_port=587
error_logfile=error.log
debug_logfile=debug.log
auth_username=johannesbohn03@gmail.com
auth_password=hsmsedalsxvfvtna
force_sender=johannesbohn03@gmail.com
hostname=localhost
INI;

file_put_contents($sendmail_ini_path, $config);
echo "Sendmail configuration created successfully!\n";

// Also update php.ini mail settings
$php_ini_path = 'C:/xampp/php/php.ini';
$php_ini = file_get_contents($php_ini_path);

$php_ini = preg_replace('/SMTP\s*=.*/', 'SMTP=smtp.gmail.com', $php_ini);
$php_ini = preg_replace('/smtp_port\s*=.*/', 'smtp_port=587', $php_ini);
$php_ini = preg_replace('/sendmail_path\s*=.*/', 'sendmail_path="C:\xampp\sendmail\sendmail.exe -t"', $php_ini);
$php_ini = preg_replace('/sendmail_from\s*=.*/', 'sendmail_from=johannesbohn03@gmail.com', $php_ini);

file_put_contents($php_ini_path, $php_ini);
echo "PHP mail settings updated successfully!\n"; 