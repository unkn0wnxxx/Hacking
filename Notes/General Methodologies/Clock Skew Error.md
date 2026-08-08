
If the DC runs on a different time than our local machine it can't sync with our requests properly. Let's fix it:

```
ntpdate -s blackfield.local
```

```
rdate -n dc.intelligence.htb
```

**Best Methodology** if doesn't work, because vm can't sync properly.

```
sudo timedatectl set-ntp off 
sudo systemctl stop systemd-timesyncd
sudo ntpdate -u 10.129.XX.XX
```