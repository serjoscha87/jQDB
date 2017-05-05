<!DOCTYPE html>
<html>
   <head>
      <meta charset="UTF-8">
      <title>jQDB - demo</title>
      <script src="jquery-2.1.1.min.js"></script>
      <script src="jquery-ui.min.js"></script>
      <script src="jQDB/jQDB.js" ></script><!-- the script lib -->
      
      <!-- your custom script to make configuration and definitions -->
      <script src="script_sqlite.js" ></script>
      
      <link type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" />
      
      <link type="text/css" href="jQDB/jQDB.css" rel="stylesheet" /><!-- default styles from and for jQDB -->
      <style type="text/css"></style>
   </head>
   <body>
      
      <!--
      Note: the demo requires a DB-table named "test_1" within a database named "test" having the fields id (int, ai, [primary]), foo (varchar), bar (int)
      -->
      
      <table class="jQDB table">
      </table>
      
   </body>
</html>
