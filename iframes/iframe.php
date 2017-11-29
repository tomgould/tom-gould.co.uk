<?php
header('X-Frame-Options: SAMEORIGIN');
?><!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
  <head>
    <title>TODO supply a title</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">




  </head>
  <body>
    <div>

      <script type="text/JavaScript">
        document.write('<iframe id="theIframe" style="display:none" src="cookie-iframe.php"></iframe>');
      </script>

    </div>
    <script type="text/JavaScript">
      function updateIframe() {
      document.getElementById("theIframe").src = 'cookie-iframe.php?' + new Date();
      setTimeout(updateIframe, 1000);
      }

      updateIframe();
    </script>


  </body>
</html>
