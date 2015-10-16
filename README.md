<h1>jQDB - jQuery inline Database-Table Editor plugin</h1>

Well, massive headline. Now something about it.

<h3>Prolog</h3>
My story begins with the search for a plugin-like tool... something I need in a non-generic form again and again from time to time:
**simple, direct and mostly unfiltered automatic inline editing of (relational) Database-Table data within any  Web-Page** (formally in  Admin/Config Backends)

<h3>And this is what I came up with due the none successful search for something that fits my needs</h3>
<img src="https://cloud.githubusercontent.com/assets/4697715/10541073/5b3a420a-740e-11e5-8a9b-148e70a9db64.png" alt="" />

this screenshot show what jQDB creates from the following test structure and it's contained data:

```sql
CREATE TABLE IF NOT EXISTS `test_1` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `foo` varchar(200) COLLATE utf8_bin NOT NULL,
  `bar` int(11) NOT NULL,
  `some_bool` tinyint(1) NOT NULL,
  `dropdown` varchar(80) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
```
the only thing needed is some configuration which you can read about in the wiki.

And here is what the concrete configuration for this result looks like:

```javascript
$(document).ready(function() {

   $('table.jQDB').jQDB({
      //connector            : 'mysql', // can be omitted - the default connector is mysql
      db                   : 'test',
      table                : 'test_1',
      primary_key_fields   : ['id'], //['foo','bar'], // the id field(s) of the table declared using an array.. example: composed PK from ['name','surname','address']
      
      fields : {
         // fields that shall not be displayed may simply be omitted in the conf
         //id : {
             //editable : false
         //},
         foo : {
            editable : true,
            type : 'string',
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
               '',
               'foo',
               'bar',
               'quxx',
               'bla'
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
      //row_delete_markup : '<b title="DELETE">X</b>',
      paging_entry_markup : '<b title="PAGE %num">%num</b>',
      prompt_before_delete : 'Are you sure?', // [false | string | <UNSET/property completely omitted>] ; don't use true
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
```

All this demo stuff is also included in the archive you can download

<h3>Purpose of this jQuery Plugin</h3>
This plugnin shall serve an - **easy to use** - way to create generic inline editing areas within any of your pages for data which is autoloaded from a relational Database-Table through a predefined connector. Currently there is only a mySQL connector but it shall be simple to add more connectors for postgresql or what ever needed.

<h3>Now once again... Why not simply use phpMyAdmin? Whats the feature? Whats the difference?</h3>
The tiny edit areas which can be created with jQDB are none-competing with phpMyAdmin in any way. They are nothing more then inline table editors.<br/>
**The only advantage is: you can put these editor areas in every page on every spot**<br/>
Want them within an option tab of the setting section of your page? No problem! Want more of these on your page to edit multiple tables? No problem! For any bigger needs this plugin in no match for your problem.

<h3>Styling</h3>
You think the layout is awful? No problem, the stylesheet coming along shall only demonstrate what everything could look like. It should be no problem defining own styles, changing colors or whatever.

<h3>Documentation</h3>
The documentation may be found in the github project wiki

<h3>Requirements</h3>
* This plugin **needs** jQuery (devoloped with version 2.1.1). Optionally you can add jQuey-ui to your project but that's **not** necessary and is only needed for a single feedback (a short "success" highlight when you insert a fresh new row). Without jquery-ui you will be missing that one hapical effect. If you dont want to include the giant jquery-ui pack you can also download a custom version only containing the effect core and "highlight". In that case you won't even need the stylesheets or something.

* PHP (guess you better have any php5 installed)<br\>
This is needed for the logical server side which evaluates data and sends it to the db-server and receives it from there

* **MySQL** as database platform: altough it should be easy to write a new one, currently there is only a mysql connector

<h4>Note</h4>
I wrote this plugin for backends which are only used by your self or people you really trust. There is a basic secure mode which should prevent SQL-Injections, choosing other tables then desired and malicious usage but there has **never been a serious focus on maximum security** when writing this. So really be careful when thinking where to put this plugin in.

<h3>Compatibility</h3>
* Tested in Chrome and Firefox (under ubuntu)
* Tested with jQuery 2.1.1
* Tested with php 5.6.2
