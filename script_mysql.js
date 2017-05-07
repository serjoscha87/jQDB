$(document).ready(function() {

   /*
    * NOTE: The sqlite demo may be more up to date according to configurations
    */

   $('table.jQDB').jQDB({
      //connector            : 'mysql', // can be omitted - the default connector is mysql
      db                   : 'test',
      table                : 'test_1',
      primary_key_fields   : ['id'], //['foo','bar'], // the id field(s) of the table declared using an array.. example: composed PK from ['name','surname','address']
      
      fields : {
//         id : {
//            editable : false
//         },
         foo : {
            editable : true,
            type : 'string',
            //label : 'foo!',
            class : 'field-%field%'
         },
         bar : {
            editable : true,
            type : 'int',
            label : 'bar!!'
         },
         some_bool : {
            editable : true,
            type : 'bool'
         },
         dropdown : {
            editable : true,
            type : 'select',
            select_elements : [
               {value:'', label:''},
               {value:'foo', label:'foo'},
               {value:'bar', label:'bar'},
               {value:'quxx', label:'quxx'},
               {value:'bla', label:'bla'}
            ],
            select_free_edit : true // enables to right click the select box to enter a free value
         }
      },
      //order_by : ['foo', 'ASC'],
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
      prompt_before_delete : 'Bist du sicher?', // [false | string | <UNSET/property completely omitted>] ; don't use true
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


