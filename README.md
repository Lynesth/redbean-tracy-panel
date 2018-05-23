# RedBeanTracyPanel
A simple class to add [RedBeanPHP](https://github.com/gabordemooij/redbean) logged queries to [Tracy](https://github.com/nette/tracy)'s debug bar. Works with multiple databases.

## Usage

All examples assume you have loaded RedBeanPHP and Tracy.

### Simple example
```php
  require 'RedBeanTracyPanel.php';

  // Enable Tracy
  Tracy\Debugger::enable(\Tracy\Debugger::DEVELOPMENT);

  // Connect to your database
  R::setup(...);

  // Create a new panel, passing RedBean's name of the database as argument
  // When using R::setup() the name of the database is 'default'
  $panel = new RedBeanTracyPanel\Panel( 'default' );

  // Set the title of the panel (optionnal)
  $panel->setTitle('My awesome panel');

  // Bind the panel to Tracy's debug bar
  $panel->bind();

  // Do your things, all queries will be retrieved at the end
```
![firefox_2018-05-23_17-25-15](https://user-images.githubusercontent.com/5318258/40406961-76ed3402-5eae-11e8-9f8f-04865c16177c.png)

### Setting your own highlighter

You don't like the simple highlighting that RedBean's debug mode does ? Want to add something of your own ?

Here is an example using [SqlFormatter](https://github.com/jdorn/sql-formatter)
```php
  require 'RedBeanTracyPanel.php';
  require 'SqlFormatter.php';

  Tracy\Debugger::enable(\Tracy\Debugger::DEVELOPMENT);

  R::setup(...);
  $panel = new RedBeanTracyPanel\Panel( 'default' );

  // To get the color right
  SqlFormatter::$pre_attributes = 'style="color: black;"';
  // To use SqlFormatter's highlight function in our panel
  $panel->setHighlighter('SqlFormatter::highlight');

  $panel->bind();

  // Do your things, all queries will be retrieved at the end
```
![firefox_2018-05-23_17-26-01](https://user-images.githubusercontent.com/5318258/40406965-792294a6-5eae-11e8-9dcc-370ee16c7081.png)

`sethighlighter` accepts a callable as its argument so you can create your own function and use it.

### Using multiple databases
```php
  require 'RedBeanTracyPanel.php';

  Tracy\Debugger::enable(\Tracy\Debugger::DEVELOPMENT);

  R::addDatabase('My first db', ...);
  $panel = new RedBeanTracyPanel\Panel( 'My First db' );
  $panel->bind();

  R::addDatabase('My other db', ...);
  $panel = new RedBeanTracyPanel\Panel( 'My other db' );
  $panel->bind();

  // Do your things, all queries will be retrieved at the end
```
![firefox_2018-05-23_17-21-32](https://user-images.githubusercontent.com/5318258/40406966-7ab90d40-5eae-11e8-8bba-edbe0a034cc5.png)


## Disclaimer

Used https://github.com/filisko/tracy-redbean as a starting point, and added support for multiple databases, custom highlighter and modified some things. However since I don't know anything about PSR-7 middleware or whatever, you won't find that here.

Feel free to add PRs !
