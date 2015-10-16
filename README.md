<h1>jQDB - jQuery inline Database-Table Editor plugin</h1>

Well, massive headline. Now something about it.

<h3>Prolog</h3>
My story begins with the search for a plugin-like tool... something I need in a non-generic form again and again from time to time:
**simple, direct and mostly unfiltered automatic inline editing of (relational) Database-Table data within any  Web-Page** (formally in  Admin/Config Backends)

<h3>And this is what the search resulted in</h3>
<img src="https://cloud.githubusercontent.com/assets/4697715/10541073/5b3a420a-740e-11e5-8a9b-148e70a9db64.png" alt="" />

this screenshot show what jQDB creates from this test structure and it's contained data:

```
CREATE TABLE IF NOT EXISTS `test_1` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `foo` varchar(200) COLLATE utf8_bin NOT NULL,
  `bar` int(11) NOT NULL,
  `some_bool` tinyint(1) NOT NULL,
  `dropdown` varchar(80) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
```

<h3>Purpose of this jQuery Plugin</h3>
This plugnin shall serve an - **easy to use** - way to create generic inline editing areas within any of your pages for data which is autoloaded from a relational Database-Table through a predefined connector. Currently there is only a mySQL connector but it shall be simple to add more connectors for postgresql or what ever needed.



<h3>compat</h3>
Tested in Chrome and Firefox
