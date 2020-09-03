# Bem blocks

## What is it
Helps create a structure for using BEM blocks.

## Installation
```
composer require lightsource/bem-blocks
```

## Required file structure

Target namespace should support PSR-4, because the namespace and a class name will have used
for dynamic getting a path to a twig template.

E.g. 'FirstHeader.php' will be converting to 'first-header.twig' and 
'Header_Type_Short.php' will be converting to 'header--type--short.php'

An example is below.

```
/Blocks/FirstHeader/
        Type/Short/
                Header_Type_Short.php
                header--type--short.twig
        Theme/Green/
                header--theme--green.scss   
        first-header.twig
        first-header.js
        first-header.scss
        FirstHeader.php
 
```

## Example of usage

##### a) Include and setup the package

```
use LightSource\BemBlocks\Settings;

require_once __DIR__ . '/vendor/autoload.php';

Settings::Instance()->setBlocksDirPath( '[Path to a blocks directory here]' );
Settings::Instance()->setBlocksDirNamespace( '[Blocks directory namespace here]' ); // e.g. Project\Blocks
```

##### b) Create a block class which will extends the BLOCK class and override the getTemplateArgs() method

```
use LightSource\BemBlocks\BLOCK;

/**
 * Class FirstHeader
 */
class FirstHeader extends BLOCK {

	/**
	 * @var int
	 */
	private $_id;
	/**
	 * @var string
	 */
	private $_value;

	/**
	 * FirstHeader constructor.
	 *
	 * @param int $id
	 */
	public function __construct( $id ) {

		$this->_id    = $id;
		$this->_value = '';

	}

	/**
	 * @return void
	 */
	public function load() {
		$this->_value = $this->_id * 3;
	}

	/**
	 * @return array
	 */
	public function getTemplateArgs() {
		return [
			'value' => $this->_value,
		];
	}

}
```

##### c) Render the block in a target place

```
$firstHeader = new FirstHeader(1);
$firstHeader->load();
echo $firstHeader->render();
```

## Additional

Feel free to extend the BLOCK class functionality, e.g. you can add auto loading resources with using a built-in InitAll() method.
 
[An example auto loading in WordPress](https://github.com/light-source/wp-theme-bones/blob/master/resources/Blocks/BLOCK.php)
