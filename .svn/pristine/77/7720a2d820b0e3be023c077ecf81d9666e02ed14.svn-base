$(document).ready(function() {

   $('table.jQDB').jQDB({
      db                   : 'test',
      table                : 'test_1',
      primary_key_fields   : ['id'], //['foo','bar'], // the id field(s) of the table declared using an array.. example: composed PK from ['name','surname','address']
      
      // TODO not working properly (config property "auto_fields")
      //auto_fields : false, // if true, all fields will be loaded and editable
      fields : {
//         id : {
//            editable : false
//         },
         foo : {
            editable : true,
            type : 'string',
            //label : 'foo!'
         },
         bar : {
            editable : true,
            type : 'int',
            label : 'bar!!'
         },
         some_bool : {
            editable : true,
            type : 'bool'
         }
      },
      order_by : ['foo', 'ASC'],
      condition : [
         //['foo', 'LIKE', '%test%', 'OR'],
         //['bar', 'LIKE', '%test%']
         //['id', '=', 66]
      ],
      elements_per_page : 10,
      delete_permitted : true,
      create_permitted : true,
      //init_page : 3,
      codebase : './', // where jQDB sources are located; in most cases this prop can be bypassed
      //row_delete_markup : '<b title="LÖSCHEN">X</b>',
      paging_entry_markup : '<b title="Seite %num">%num</b>',
      prompt_before_delete : 'Bist du sicher?', // [false | string | <UNSET>] ; don't use true
      //prompt_before_delete : false,
      callbacks : {
         dataLoaded : function(res) {
            //console.info(res);
         },
         updateSuccess : function(res) {
            //console.info(res);
         },
         insertSuccess : function (res) {
            //console.info(res);
         },
         deleteSuccess : function (res) {
            //console.info(res);
         }
      }
   });

});


