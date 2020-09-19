<?php

namespace LightSource\BemBlocks;

use ReflectionProperty;
use Exception;

/**
 * Class MODEL
 * @package LightSource\BemBlocks
 */
abstract class MODEL {


	//////// fields


	/**
	 * @var bool
	 */
	private $_isAutoLoadProtectedFields;
	/**
	 * @var array
	 */
	private $_fieldsInfo;


	//////// constructor


	/**
	 * MODEL constructor.
	 *
	 * @param bool $isAutoLoadProtectedFields
	 */
	public function __construct( $isAutoLoadProtectedFields = true ) {

		$this->_isAutoLoadProtectedFields = $isAutoLoadProtectedFields;
		$this->_fieldsInfo                = [];

		$this->_autoInitFields();

	}


	//////// methods


	/**
	 * @return array [ fieldName => [fieldType], fieldName => [fieldType1, fieldType2] ]
	 */
	final private function _getSelfFieldsInfo() {

		$fieldsInfo = [];

		// get_object_vars return all visible in this context fields, so children public && protected and can return our (BASE) private fields

		$fieldNames = array_keys( get_object_vars( $this ) );
		foreach ( $fieldNames as $fieldName ) {

			try {
				// used static for child support
				$property = new ReflectionProperty( static::class, $fieldName );
			} catch ( Exception $e ) {
				continue;
			}

			// we can't read private fields in children, get_object_vars can return our (BASE) private fields, ignore it
			// also we'll ignore public

			if ( $property->isPrivate() ||
			     $property->isPublic() ) {
				continue;
			}

			// types can be multiple ex. : 'string|false', so types info should be always array

			$docTypes = [];

			$docComment = $property->getDocComment();

			if ( $docComment ) {

				$matches = [];
				preg_match( '/@var\s*([^\s]+)/i', $docComment, $matches );

				if ( 2 === count( $matches ) ) {
					$docTypes = explode( '|', $matches[1] );
				}

			}

			$fieldsInfo[ $fieldName ] = $docTypes;

		}

		return $fieldsInfo;
	}

	/**
	 * @return void
	 */
	final private function _autoInitFields() {

		if ( ! $this->_isAutoLoadProtectedFields ) {
			return;
		}

		$this->_fieldsInfo = $this->_getSelfFieldsInfo();

		foreach ( $this->_fieldsInfo as $fieldName => $fieldTypes ) {

			$defaultValue = null;

			if ( $fieldTypes ) {
				switch ( $fieldTypes[0] ) {
					case 'int':
					case 'float':
						$defaultValue = 0;
						break;
					case 'bool':
						$defaultValue = false;
						break;
					case 'string':
						$defaultValue = '';
						break;
					case 'array':
					case 'string[]':
					case 'int[]':
					case 'float[]':
						$defaultValue = [];
						break;
				}
			}

			$this->{$fieldName} = $defaultValue;

		}

	}

	/**
	 * @return array
	 */
	public function getArgs() {

		$args = [];

		foreach ( $this->_fieldsInfo as $fieldName => $fieldTypes ) {

			$argName  = ltrim( $fieldName, '_' );
			$argValue = $this->{$fieldName};

			if ( $argValue instanceof CONTROLLER ) {
				$argValue = $argValue->getTemplateArgs();
			}

			$args[ $argName ] = $argValue;

		}

		return $args;
	}

}
