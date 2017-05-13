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
      
      <button type="button" id="test-update-row">[test] Set val of field "bar" to a random num between 1 and 200 at row with id 1</button>
      
      <script>
         $('#test-update-row').click(function() {
            var inst = $('.jQDB').jQDB('get');
            inst.api.updateRow({
               table : inst.api.getOptions()['table'],
               update_data : {
                  bar : Math.floor(Math.random() * 200) + 1  
               },
               pk_data: {
                  id : 1
               }
            }, function() {
               console.info("done update!");
            });
         });
      </script>
      
   </body>
</html>
