
When an IRC Service is running u can establish a connection by using following PoC's


#### Netcat Connectiom Registration

nc <target_ip> <target_port>

The commands described here are used to register a connection with an
IRC server as either a user or a server as well as correctly
disconnect.

```
PASS password
NICK saitama
USER <username> <hostname> <servername> :<realname>
USER saitama hostname2 servername2 :kpwasichschreibensoll
```

PoC Output could look like this:

```
nc irked.htb 6697
:irked.htb NOTICE AUTH :*** Looking up your hostname...
PASS password
NICK saitama
USER saitama hostname2 servernam2 :kpwasichschreibensoll
:irked.htb NOTICE AUTH :*** Couldn't resolve your hostname; using your IP address instead
:irked.htb 001 saitama :Welcome to the ROXnet IRC Network saitama!saitama@10.10.14.186
:irked.htb 002 saitama :Your host is irked.htb, running version Unreal3.2.8.1
:irked.htb 003 saitama :This server was created Mon May 14 2018 at 13:12:50 EDT
:irked.htb 004 saitama irked.htb Unreal3.2.8.1 iowghraAsORTVSxNCWqBzvdHtGp lvhopsmntikrRcaqOALQbSeIKVfMCuzNTGj
:irked.htb 005 saitama UHNAMES NAMESX SAFELIST HCN MAXCHANNELS=10 CHANLIMIT=#:10 MAXLIST=b:60,e:60,I:60 NICKLEN=30 CHANNELLEN=32 TOPICLEN=307 KICKLEN=307 AWAYLEN=307 MAXTARGETS=20 :are supported by this server
:irked.htb 005 saitama WALLCHOPS WATCH=128 WATCHOPTS=A SILENCE=15 MODES=12 CHANTYPES=# PREFIX=(qaohv)~&@%+ CHANMODES=beI,kfL,lj,psmntirRcOAQKVCuzNSMTG NETWORK=ROXnet CASEMAPPING=ascii EXTBAN=~,cqnr ELIST=MNUCT STATUSMSG=~&@%+ :are supported by this server
:irked.htb 005 saitama EXCEPTS INVEX CMDS=KNOCK,MAP,DCCALLOW,USERIP :are supported by this server
:irked.htb 251 saitama :There are 1 users and 0 invisible on 1 servers
:irked.htb 253 saitama 1 :unknown connection(s)
:irked.htb 255 saitama :I have 1 clients and 0 servers
:irked.htb 265 saitama :Current Local Users: 1  Max: 1
:irked.htb 266 saitama :Current Global Users: 1  Max: 1
:irked.htb 422 saitama :MOTD File is missing
:saitama MODE saitama :+iwx
```

Information were found in following resource:

```
https://datatracker.ietf.org/doc/html/rfc1459#section-4.1
```