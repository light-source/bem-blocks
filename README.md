# Bem blocks

## What is it
Helps create an MVC structure for BEM blocks.

## Installation
```
composer require lightsource/bem-blocks
```

## Required file structure

Target namespace must support PSR-4, because the namespace and a Controller class name will have used
for dynamic getting a path to a twig template.

Classes that extend a Controller class should have a '_C' suffix (it can be setup).
It's using for prevent names conflict (because each folder will contain a child of Controller and child of Model)

E.g. 'FirstHeader_C.php' will be converting to 'first-header.twig' and 
'Header_Type_Short_C.php' will be converting to 'header--type--short.php'

An example is below.

```
/Blocks/FirstHeader/
        Type/Short/
                FirstHeader_Type_Short_C.php
                first-header--type--short.twig
        Theme/Green/
                first-header--theme--green.scss   
        first-header.twig
        first-header.js
        first-header.scss
        FirstHeader_C.php
        FirstHeader.php // it's a model
 
```

## Example of usage

##### a) Include and setup the package

```
use LightSource\BemBlocks\Settings;

require_once __DIR__ . '/vendor/autoload.php';

Settings::Instance()->setBlocksDirPath( '[Path to a blocks directory here]' );
Settings::Instance()->setBlocksDirNamespace( '[Blocks directory namespace here]' ); // e.g. Project\Blocks
```

##### b) Create a new block

```
FirstHeader/
    FirstHeader_C.php // extends Controller
    FirstHeader.php // extends Model
    first-header.twig // template
```

FirstHeader_C.php (extends Controller)

```
use LightSource\BemBlocks\CONTROLLER;

/**
 * Class FirstHeader_C
 */
class FirstHeader_C extends CONTROLLER {


	//////// construct


	/**
	 * FirstHeader_C constructor.
	 */
	public function __construct() {
		parent::__construct( new FirstHeader() );
	}


	//////// override extend methods


	/**
	 * @return FirstHeader
	 */
	public function getModel() {
		return parent::getModel();
	}

}
```

FirstHeader.php (extends Model)

```
use LightSource\BemBlocks\MODEL;

/**
 * Class FirstHeader
 */
class FirstHeader extends MODEL {


	//////// fields


	/**
	 * @var string
	 */
	private $_value;


	//////// constructor


	/**
	 * FirstHeader constructor.
	 */
	public function __construct() {

		$this->_value = '';

	}


	//////// implementation abstract methods


	/**
	 * @return array
	 */
	public function getArgs() {
		return [
			'value' => $this->_value,
		];
	}


	//////// methods


	/**
	 * @param int $id
	 *
	 * @return void
	 */
	public function loadById( $id ) {
		$this->_value = 'Test with ' . $id;
	}

}
```

##### c) Render the block in a target place

```
$firstHeaderC = new FirstHeader_C();
$firstHeaderC->getModel()->loadById(3);
echo $firstHeaderC->render();
```

## Additional

Feel free to extend the Controller class functionality, e.g. you can add auto loading resources with using a built-in InitAll() method.
 
[An example auto loading in WordPress](https://github.com/light-source/wp-theme-bones/blob/master/resources/Blocks/BLOCK.php)
