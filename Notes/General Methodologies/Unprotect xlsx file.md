
In order to unprotect the columns, we can unzip the file, remove the sheetProtection section and then update the archive.

```
unzip Phishing_Attempt.xlsx
sed -i 's/<sheetProtection[^>]*>//' xl/worksheets/sheet2.xml
zip -fr Phishing_Attempt.xlsx *
```

View the .xlsx file again and now we can see the column.

```
libreoffice --calc Phishing_Attempt.xlsx
```

OR 

Convert the .xlsx file into an .csv file and viewed it.

```
ssconvert Phishing_Attempt.xlsx Hello.csv
```

Viewed the .csv file.

```
cat Hello.csv            
firstname,lastname,password,Username
Payton,Harmon,;;36!cried!INDIA!year!50;;,Payton.Harmon
Cortez,Hickman,..10-time-TALK-proud-66..,Cortez.Hickman
Bobby,Wolf,??47^before^WORLD^surprise^91??,Bobby.Wolf
Margaret,Robinson,//51+mountain+DEAR+noise+83//,Margaret.Robinson
Scarlett,Parks,++47|building|WARSAW|gave|60++,Scarlett.Parks
Eliezer,Jordan,!!05_goes_SEVEN_offer_83!!,Eliezer.Jordan
Hunter,Kirby,~~27%when%VILLAGE%full%00~~,Hunter.Kirby
Sierra,Frye,$$49=wide=STRAIGHT=jordan=28$$18,Sierra.Frye
Annabelle,Wells,==95~pass~QUIET~austria~77==,Annabelle.Wells
Eve,Galvan,//61!banker!FANCY!measure!25//,Eve.Galvan
Jeramiah,Fritz,??40:student:MAYOR:been:66??,Jeramiah.Fritz
Abby,Gonzalez,&&75:major:RADIO:state:93&&,Abby.Gonzalez
Joy,Costa,**30*venus*BALL*office*42**,Joy.Costa
Vincent,Sutton,**24&moment&BRAZIL&members&66**,Vincent.Sutton
```
