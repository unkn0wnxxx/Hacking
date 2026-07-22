
If the DC runs on a different time than our local machine it can't sync with our requests properly. Let's fix it:

```
ntpdate -s blackfield.local
```