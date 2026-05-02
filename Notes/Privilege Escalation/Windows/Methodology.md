- View environment variable (dir env:)
- [[Old Windows]] Sub-Directory/Backup?
- Check [[PS History]]
- Mail Server? --> [[Phishing with Windows Library File]]
- Webpage? Check if webroot is writable and use reverse shell --> potentially elevate privs.
- Run winPEAS.exe preferred over winPEAS.bat
	- Enumerate [[Windows AutoLogon Credentials Enum|AutoLogon Credentials.]]
	- [[Enumerating internal machines]]
- User Privileges whoami /privs
	- [[SeImpersonatePrivilege]]
		- [[PrintSpoofer.exe]]
		- Potatos
			- SweetPotato, works on all OS.
			- [[JuicyPotato]], pretty good for Windows Server 2008
			- HotPotato, pretty good for Windows Server 2008
			- SigmaPotato, viable for Windows Server 2012-2022
	- [[SeBackupPrivilege]]
	- [[SeRestorePrivilege]]
	- [[SeManageVolumePrivilege]]
- [[Windows Enum Users and Groups|Enumerating users and groups?]]
	- Am I an local service account with restricted privs? --> Do [[Windows Local Service Account Priv Esc|this.]]
- [[Windows Enum System Enum|Enumerating System Architecture]]
	- Outdated OS?
		- Windows Kernel Exploits --> winPEAS tells u which kernel exploits are available.
- [[Windows Installed Applications|Check Installed Applications]]
- [[Windows Enum Services & Processes|Check Running Services and Processes]]
	- Incase tools are restricted, we can utilize [[Port Scan PowerShell|this]] PS Script to enumerate running services.
- [[Windows File Enumeration|Searching for files.]]
- Check [[Windows Scheduled Tasks|Scheduled Tasks.]]
	- Write Access? --> Abuse.
- Enumerate [[Windows AutoLogon Credentials Enum|AutoLogon Credentials.]]
- Found Credentials?
	- Get RCE as this user by creating an ps object like [[Lateral Movement Windows PowerShell|this.]]
- Windows Priv Esc's
	- Run [[PowerUp.ps1]]
	- Found writable .exe file --> do [[Windows Privesc Binary Hijacking|this.]]
	- Found writable .dll file --> do [[Windows PrivEsc DLL Hijacking|this.]]
	- Write Access on Domain Policys? --> do [[Windows PrivEsc Writable GPO|this.]]
	- Write Perms on Unquoted Service Path? --> Do [[Windows Priv Esc Unquoted Service Path|this.]]

Lateral Movement

- [[RunasCs]]
#### Post Exploitation

- [[Credential Dumping]]
- [[File Transfers]]