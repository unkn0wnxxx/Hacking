<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
    
    <style>
    html { display: flex; justify-content: center;}
    body {background-color: black; color: white;}
    p { color: white; font-family: "Monospace";}
    p2 { color: white; font-family: "Monospace"; text-align: center; font-size: 10px;}
    p3 { color: green; font-family: "Monospace"; text-align: center; font-size: 15px;}
    </style>
    
        <title>MZEE-AV - Check your files</title>
    </head>
    <body>
        <pre>
  __  __ _______ ___     ___   __ 
 |  \/  |_  / __| __|__ /_\ \ / / 
 | |\/| |/ /| _|| _|___/ _ \ V /  
 |_|  |_/___|___|___| /_/ \_\_/   
                                  
        </pre>
<hr>
        
        <br>
		<?php
		  print("List checked files:<br><br>");
		  
		  $mydir = './upload/'; 
		  $myfiles = array_diff(scandir($mydir), array('.', '..')); 
		  //print_r($myfiles);
		  
		  foreach($myfiles as $key => $value)
			{
			$file = './upload/' . $value;
  			echo "<p3>" . $value . " - MD5: " . md5_file($file) . " - seems to be clean!</p3><br>";
			}
		?>
        <br>
        
		<p>Check your PE-files with the online AV engine. <a href="index.html">Home</a></p>
        <br>
        <br>
        <hr>
        <p2>by MZEE-AV 2022</p2>
    </body>
</html>
