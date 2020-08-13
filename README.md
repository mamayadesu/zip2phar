# zip2phar
This PHP-application converts your ZIP-archive to PHAR-archive<br>
<br>
1. Run<br>
2. Insert path to your ZIP-archive and press Enter<br>
3. Insert path to your PHAR-archive when it will be created and press Enter<br>
4. Done<br>
<br>
If in root of archive file <code>autoload.php</code> exists, it will be used to bootable file
<br>
Also you can run application with arguments. So you don't need to insert paths everytime. Example:
<code>php zip2phar.phar /path/to/my%20app.zip /path/to/my%20app.phar</code><br>
<code>%20</code> is space
