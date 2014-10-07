/*
 * Note: Error are printed to the console
 */
(function ($) {
   $.fn.jQDB = function (options) {
      
      var selectedElement = this;
      
      if(!selectedElement.is('table'))
         return console.error("Please use a <table> as base DOM Element for jQDB, not >"+selectedElement.prop("tagName")+"<");
      
      if(typeof options.fields === 'undefined')
         return console.error("No fields were configured for selection");

      if(typeof options.primary_key_fields === 'undefined')
         return console.error("No primary keys were configured for the queriess selection");
      
      /*
       * Default config values
       */
      options = $.extend({
         connector : 'mysql',
         //auto_fields : true,
         elements_per_page : 10,
         init_page : 1,
         delete_permitted : true,
         create_permitted : true,
         row_delete_markup : '&otimes;', // may also contain html
         paging_entry_markup : '%num ',
         prompt_before_delete : 'Are you sure?',
         codebase : './'
      }, options);
      
      /*
       * Callback Stuff
       */
      var callbacks = options.callbacks; // without those 2 lines the callback would be executed on init because they are ajax-tansmitted as part of the options
      options.callbacks = null;
      callbacks = $.extend({ // create default callbacks if there we none configed
         dataLoaded : function(){},
         updateSuccess : function(){},
         insertSuccess : function(){},
         deleteSuccess : function(){}
      }, callbacks);
      
      /*
       * LOAD data and build complete behaviour stracture 
       */
      var loadData = function(page) {
         options['page'] = page; // make the selected page beeing a part of the options that are posted to the db connector
         selectedElement.find('tr').remove(); // remove previous markup when paging nav is used
         $.ajax(options.codebase + 'jQDB/generic_connector_factory.php', {
            data : {
               action : 'load',
               data : options
            },
            dataType : 'json',
            type : 'post',
            //async : true, 
            success: function(res) {
               
               // catch errors for connect and loadData
               if(!res.success){
                  console.error(res.error_str);
                  return;
               }
               
               // Render table header
               var row = $(document.createElement('tr'));
               var i=0; // for the col class name
               $.each(options.fields, function(k,v) {
                  $(document.createElement('th'))
                          .addClass('col-'+(i++))
                          .html(v.label || k)
                          .appendTo(row);
               });
               selectedElement.append(row);

               // Render DB data fields
               $.each(res.results, function(k,v) {
                  /*
                   * ROWS
                   */
                  var row = $(document.createElement('tr'));
                  $.each(v, function(k2,v2) {
                     /*
                      * COLS / FIELDS AND THEIR INNER
                      */
                     if(typeof options.fields[k2] === "undefined")
                        return; // prevent printf of mysql-data-fields that were not selected by the user-config (but returned by the qry result)

                     var editable = options.fields[k2].editable;
                     var cellContent = null;
                     if (editable) {
                        var type = options.fields[k2].type || 'string'; // default type: string (when none is defined)

                        cellContent = $(document.createElement('input'))
                                          .val(v2)
                                          .change(function () {
                                             var additional_data = {
                                                pk_data : $(this).parent().data('pk_data'), // contains data that can be used to build a WHERE clause for a query (field1=>it's value ; field2=>also it's value [...])
                                                changed_field : $(this).parent().data('field'),
                                                changed_nu_val : type==='bool' ? ($(this).is(':checked') ? 1 : 0) : $(this).val()
                                             };
                                             /*
                                              * UPDATE
                                              */
                                             $.post(options.codebase + 'jQDB/generic_connector_factory.php', {
                                                action : 'update',
                                                data : $.extend(options, additional_data),
                                             }, function(res) {
                                                cellContent.fadeOut(150).fadeIn(150); // visual save feedback
                                                callbacks.updateSuccess.call(this, res);
                                             }, 'json');
                                          });

                        // restrict input to only numbers
                        if(type==='int'){
                           cellContent.keypress(function(e) {
                              if(!String.fromCharCode(e.charCode).match(/\d/) && e.charCode !== 13)
                                 e.preventDefault();
                           }).keyup(function(){
                              // color int fields when exceeding the max (default) int capacity
                              var mysql_int_max = 2147483647;
                              if(parseInt(cellContent.val()) > mysql_int_max)
                                 cellContent.css('background-color','#d9534f');
                              else
                                 cellContent.css('background-color','transparent');
                           });
                        }

                        if(type==='bool'){
                           cellContent.attr('type', 'checkbox');
                           cellContent.attr('checked', v2==="1" ); // mark checked / unchecked according to the db
                        }
                     }
                     else {
                        cellContent = $(document.createElement('span'))
                                          .text(v2);
                     }

                     var field = $(document.createElement('td'))
                             .click(function(){$(this).find('input').focus();}) // delegate focus when the inpu is smaller then the cell
                             .append(cellContent)
                             .appendTo(row);

                     if(editable) {
                        var pk_data = {};
                        $.each(options.primary_key_fields, function (k3,v3) { // set the (perhaps combined) id which is needed to update the field to every parent that can be edited
                           pk_data[v3] = v[v3];
                        });
                        field.data('pk_data', pk_data);
                        field.data('field', k2);
                     }

                  });
                  
                  /*
                   * Possibility to delete rows
                   */
                  if(options.delete_permitted) {
                     $(document.createElement('td'))
                        .html(options.row_delete_markup)
                        .data('pk_data', null)
                        .addClass('jqdb-delete-row-button')
                        .click(function (){
                           //var container_row = $(this).parent('tr'); // used to remove when the db delete was successfully
                           var pk_data = {};
                           $.each(options.primary_key_fields, function (k2,v2) {
                              pk_data[v2] = v[v2];
                           });
                           var additional_data = {
                              pk_data : pk_data
                           };
                           
                           // if prompting ist active
                           var doDelete = true;
                           if(options.prompt_before_delete)
                              doDelete = confirm(options.prompt_before_delete);
                           
                           /*
                            * do DELETE
                            */
                           if(doDelete) {
                              $.post(options.codebase + 'jQDB/generic_connector_factory.php', {
                                 action : 'delete',
                                 data : $.extend(options, additional_data),
                              }, function(res) {
                                 callbacks.deleteSuccess.call(this, res);
                                 if(res.success === 1)
                                    loadData(page);//container_row.remove();
                              }, 'json');
                           }
                        })
                        .appendTo(row);
                  } // end delete_permitted
                  
                  selectedElement.append(row);
               });

               /*
                * append a row for beeing able to insert new data into the db
                */ 
               if(options.create_permitted) {
                  var row = $(document.createElement('tr'));
                  $.each(options.fields, function (k,v){
                     $(document.createElement('td'))
                        .append(
                           $(document.createElement('input'))
                           .attr("placeholder", v.type==="bool"?'1|0':"+")
                           .attr("maxlength", (v.type==="bool"?'1':(v.type==="int"?'10':'')))
                           .css('width', (v.type==="bool"?'18px':'auto')) // make bool inputs smaller
                           .attr("name", k)
                           //.attr("type", v.type==="bool"?'checkbox':'text')
                           .keypress(function (e) {
                              
                              // prevent text in int fields
                              if(!String.fromCharCode(e.which).match(/\d/) && v.type==="int" && e.which !== 13)
                                 e.preventDefault();
                              
                              if(e.keyCode === 13){ // on enter-button
                                 // prevent inserting complete empty rows
                                 var textOverAll = '';
                                 var newRowInputs = $(this).parents('tr').find('input');
                                 $.each(newRowInputs, function() {
                                    textOverAll += $(this).val().trim();
                                 });
                                 if(textOverAll.length === 0) {
                                    newRowInputs.css('border', '2px solid red');
                                    return;
                                 }
                                 // if the "valid-check" was successful: post the data to the connector to insert a new row
                                 var additional_data = { insert_data : $(this).parents('tr').find('input').serialize() };
                                 $.post(options.codebase + 'jQDB/generic_connector_factory.php', {
                                    action : 'insert',
                                    data : $.extend(options, additional_data),
                                 }, function(res) {
                                    loadData(page);

                                    // callback
                                    callbacks.insertSuccess.call(this, res);
                                 });
                              }
                           }) // end input creation
                        )
                        .click(function(){$(this).find('input').focus();})
                        .appendTo(row);
                  });
                  selectedElement.append(row);
               }

               /*
                * Paging
                */
               if(options.elements_per_page < res.result_amount) {

                  var row = $(document.createElement('tr'));
                  var pagination = $(document.createElement('td'))
                        .addClass('jqdb-paging')
                        .attr('colspan', Object.keys(options.fields).length + (options.delete_permitted ? 1 : 0))
                        .appendTo(row);
                  selectedElement.append(row);

                  for(var i=0; i< Math.ceil(res.result_amount/options.elements_per_page); i++) {
                     var isActivePage = i === page;
                     var paging_button_markup = i+1;
                     var replacer = new RegExp('%num', 'g');
                     $(document.createElement('a'))
                        .data('page', i)
                        .on('click', function() { loadData($(this).data('page')); })
                        .html(options.paging_entry_markup.replace(replacer, paging_button_markup))
                        .addClass('jqdb-paging-button'+(isActivePage?' active':''))
                        .appendTo(pagination);
                  }

               }
               
               /*
                * Entry-Counter
                */
               var gotPaging = (selectedElement.find('.jqdb-paging').length === 1);
               var entry_counter_row = $(document.createElement( (gotPaging ? 'span' : 'td') ))
                     .addClass('jqdb-entry-counter')
                     .attr('colspan', Object.keys(options.fields).length + (options.delete_permitted ? 1 : 0)) // does not affect the span
                     //                elems from                                                     elems to                                        elems over all amount
                     .text( (page*options.elements_per_page) + " - " + (page*options.elements_per_page + Object.keys(res.results).length) + " / " + (res.result_amount) );
               
               if(gotPaging) {
                  // we got a paging row -> append the entry counter at the end
                  entry_counter_row.css({'float':'right'});
                  row.find('td').append(entry_counter_row);
               }
               else {
                  // there is no paging, create a fresh row
                  entry_counter_row.css({'text-align':'right'});
                  var row = $(document.createElement('tr'))
                     .append(entry_counter_row)
                     .appendTo(selectedElement);
               }
               

               // Exec Data loaded callback
               callbacks.dataLoaded.call(this, res);

            } // success
         }); // .ajax
         
      }; // loda data
      loadData(options.init_page-1); // load the data in page load
      
      return this;
   };
}(jQuery));