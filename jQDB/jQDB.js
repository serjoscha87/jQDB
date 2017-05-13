(function ($) {

   $.fn.jQDB = function (options) {
      
      var selectedElement = this;
      
      if(!selectedElement.is('table'))
         return publishError("Please use a <table> as base DOM Element for jQDB, not >"+selectedElement.prop("tagName")+"<");
      
      if(typeof options.fields === 'undefined')
         return publishError("No fields were configured for view-selection. Please do so. (config property 'fields')");

      if(typeof options.primary_key_fields === 'undefined')
         return publishError("No primary keys were configured. Please do so. (config property 'primary_key_fields')");
      
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
         codebase : './',
         editable_default : false, // when the "editable" configuration for fields are omitted: this will be used 
         required_default : false // "" for required
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
      var loadData = function(page, internal_callback) {
         options['page'] = page; // make the selected page beeing a part of the options that are posted to the db connector
         $.ajax(options.codebase + 'jQDB/generic_connector_factory.php', {
            data : {
               action : 'load',
               options : options
            },
            dataType : 'json',
            type : 'post',
            //async : true, 
            success: function(res) {
               selectedElement.find('tr').remove(); // remove previous markup when paging nav is used
               
               if(!res.success) // catch errors for connect and loadData
                  return publishError(res.error_str);
               
               // Render table header
               var row = $(document.createElement('tr'));
               var i=0; // for the col class name
               $.each(options.fields, function(k,v) {
                  if(Object.keys(v).length===0) // throw out fields without configuration ('editable' for example) for they wont be submitted by jquery
                     delete options.fields[k];
                  else
                     $(document.createElement('th'))
                        .addClass('jqdb-col-header jqdb-col-header-'+(i++))
                        .addClass(v.type==='hidden'?'hidden':'')
                        .html(v.label || k)
                        .appendTo(row);
               });
               selectedElement.append(row);

               // Render DB data fields
               $.each(res.results, function(k,v) {
                  /*
                   * ROWS
                   */
                  var row = $(document.createElement('tr')).addClass('jqdb-data-row');
                  var i=0;
                  $.each(v, function(k2,v2) {
                     /*
                      * COLS / FIELDS AND THEIR INNER
                      */
                     if(typeof options.fields[k2] === "undefined") // || options.fields[k2].type === "hidden"
                        return; // prevent printf of mysql-data-fields that were not selected by the user-config (but returned by the qry result)

                     var editable = options.fields[k2].editable || options.editable_default; // default when not configured -> use default config option as val
                     var cellContent = null;
                     if (editable) {
                        
                        var type = options.fields[k2].type || 'string'; // default type: string (when none is defined)

                        if(type === 'select') {
                           var select_elements = options.fields[k2].select_elements.slice(); // slice: this is a hack; see http://stackoverflow.com/questions/7486085/copying-array-by-value-in-javascript
                           if(typeof select_elements==='undefined') return publishError('You configured a select box but did not tell any fixed drop down options. Please do so using >select_elements< next to type >select<');
                           cellContent = $(document.createElement('select'));
                           
                           if(options.fields[k2].select_free_edit) { // build right click functionality for the "select_free_edit" config property
                              select_elements.push({value:v2, label:v2}); // add the free value
                              cellContent.on('contextmenu', function (e) {
                                 if (e.button === 2) {
                                    var free_val = prompt('Value?');
                                    if(free_val){
                                       cellContent.find('option:selected').text(free_val);
                                       cellContent.change();
                                    }
                                    return false;
                                 }
                                 return true;
                              }); 
                           }
                           
                           $.each(select_elements, function(sk,sv) {
                              $(document.createElement('option'))
                                      .text(sv.label)
                                      .val(sv.value)
                                      .attr('selected', sv.value == v2) // mark selected from db
                                      .appendTo(cellContent);
                           });
                        }
                        else {
                           cellContent = 
                              $(document.createElement('input'))
                                 .attr('type', type)
                                 .val(v2);
                        }
                        /*
                         * bind change event to the input
                         */ 
                        cellContent.change(function () {
                           var additional_data = {
                              pk_data : $(this).parent().data('pk_data'), // contains data that can be used to build a WHERE clause for a query (field1=>it's value ; field2=>also it's value [...])
                              changed_field : $(this).parent().data('field'),
                              changed_nu_val : type==='bool' ? ($(this).is(':checked') ? 1 : 0) : (type==='select' ? $(this).find(':selected').val() : $(this).val())
                           };
                           /*
                            * UPDATE
                            */
                           $.post(options.codebase + 'jQDB/generic_connector_factory.php', {
                              action : 'update',
                              options : $.extend(options, additional_data),
                           }, function(res) {
                              cellContent.fadeOut(150).fadeIn(150); // visual save feedback
                              callbacks.updateSuccess.call(this, res);
                           }, 'json');
                        });

                        // restrict input to only numbers
                        if(type==='int') {
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

                        if(type==='bool') { // change type of the input to a checkbox
                           cellContent.attr('type', 'checkbox');
                           cellContent.attr('checked', parseInt(v2)===1 ); // mark checked / unchecked according to the db
                        }

                        if(type==='string') {
                           cellContent.attr('type', 'text');
                        }
                        
                     }
                     else { // not editable
                        cellContent = $(document.createElement('span'))
                                          .text(v2);
                     }
                     
                     cellContent.attr('data-field', k2);
                     
                     /*if(options.fields[k2].class) {
                        cellContent.addClass(options.fields[k2].class.replace('%field%', k2));
                     }*/
                     cellContent.addClass( (options.fields[k2].class || 'jQDBField-%field%').replace('%field%', k2));

                     var field = $(document.createElement('td'))
                             .click(function(){$(this).find('input').focus();}) // delegate focus when the inpu is smaller then the cell
                             .addClass('jqdb-col jqdb-col-'+(i++))
                             .addClass(type==='hidden' ? 'hidden' : '')
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
                        .click(function () {
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
                                 options : $.extend(options, additional_data),
                              }, function(res) {
                                 callbacks.deleteSuccess.call(this, res);
                                 //if(res.success === 1)
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
                  var row = $(document.createElement('tr'))
                              .addClass('insert-row-row');
                  var i=0;
                  $.each(options.fields, function (k,v) {
                     $(document.createElement('td'))
                        .addClass('jqdb-new_row-col jqdb-new_row-col-'+(i++))
                        .addClass(v.type==='hidden'?'hidden':'')
                        .append(
                           $(document.createElement(v.type === 'select' ? 'select' : 'input'))
                           .attr("placeholder", v.placeholder || "+")
                           .attr("maxlength", v.type==="int"?'10':'')
                           .addClass(v.type==='hidden'?'hidden':'')
                           .attr("name", k)
                           .each(function(){ // trick to chain a custom function that conditional may not affect the chain at all but COULD affect it
                              if(v.default_value)
                                 return $(this).val(v.default_value);
                           })
                           .attr("type", (v.type==="bool"?'checkbox': 'text')) // remember: unticked checkboxes wont be submitted
                           .data("type", v.type) // the real type that comes configed in js 
                           .prop("required",  v.required || options.required_default)
                           .each(function() { // hack for conditional working on the temp input-dom-element. This enables us to have a real function body. Dont be confused. The function / loop will for every element only be run once
                              var de = $(this);
                              if(de.data('type') === 'select') {
                                 v.select_elements.forEach(function(v,k) {
                                    $(document.createElement('option'))
                                            .text(v.label)
                                            .val(v.value)
                                            .appendTo(de);
                                 });
                              }
                           })
                           .keypress(function (e) { // within any input
                              // prevent text in int fields
                              if(!String.fromCharCode(e.which).match(/\d/) && v.type==="int" && e.which !== 13)
                                 e.preventDefault();
                              
                              var all_required_filled = true;
                              
                              if(e.keyCode === 13) { // on enter-button (for any input)
                                 var newRowInputs = $(this).parents('tr').find('input,select');
                                 var additional_data = { insert_data : {} }; // filled within loop
                                 $.each(newRowInputs, function(num_key, dom_input) {
                                    var di = $(dom_input);
                                    
                                    di.removeClass('error-required'); // remove (perhaps) previously added error class 
                                    
                                    var val = ( // find the val according to the current element type (simple input, select / checkbox)
                                            di.data('type') === 'bool' ? (di.is(':checked')?1:0) : 
                                                (di.data('type') === 'select' ? di.find('option:selected').text() : di.val() )
                                    );
                                    // assign additional data for posting
                                    additional_data.insert_data[di.attr('name')] = val;
                                    
                                    // check if current field is required and unset in order to show an error
                                    if(di.is(':required') && val.length===0) {
                                       all_required_filled = false;
                                       di.addClass('error-required');
                                    }
                                 });
                                 if(!all_required_filled)
                                    return;

                                 // if the "valid-check" (at least one text input is filled) was successful: post the data to the connector to insert a new row
                                 $.post(options.codebase + 'jQDB/generic_connector_factory.php', {
                                    action : 'insert',
                                    options : $.extend(options, additional_data),
                                 }, function(res) {
                                    loadData(page, function(){
                                       //console.info(row);
                                       //window.foo = selectedElement.find('.insert-row-row');
                                       //console.info(selectedElement.find('.insert-row-row'));
                                       selectedElement.find('.insert-row-row input, .insert-row-row select').effect('highlight', {color:'lime'}); // .animate(.. with colors and .effect('highlight... ONLY work when jQuery UI is loaded with efect-core (and those specific effects needed)
                                    });

                                    // call up callback
                                    callbacks.insertSuccess.call(this, res);
                                 });
                              }
                           }) // end input creation
                        )
                        .click(function(){$(this).find('input').focus();})
                        .appendTo(row);
                  });
                  $(document.createElement('td')) // this td can also be clicked to omit pressing enter in any field
                          .html('<b>+</b>')
                          .attr('title', 'Add this as a new row')
                          .css('cursor', 'pointer')
                          .click(function () {
                              $(this).parents('tr').find('td > *').eq(0).trigger($.Event('keypress', { keyCode: 13 } ));
                          })
                          .appendTo(row);
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
               
               // exec the internal callback function that may be appended
               if(typeof internal_callback === 'function')
                  internal_callback.call(this);

            } // end of success-callback
         }); // end of .ajax(..
         
      }; // loda data
      loadData(options.init_page-1); // load the data in page load
      
      function publishError(msg) {
         $(document.createElement('div'))
                 .text('jQDB: '+msg)
                 .addClass('jQDB-error') // so eventual error msgs can be hidden via css
                 .css({'color':'red'})
                 .appendTo($('body'));
      }
      
      return this;
   };
}(jQuery));

