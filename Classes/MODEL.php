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


	// fields have a double prefix for prevent a name conflict

	private bool $__isAutoLoadProtectedFields;
	private array $__childFieldsInfo;
	private array $__external;
	private bool $__isLoaded;


	//////// constructor


	/**
	 * MODEL constructor.
	 *
	 * @param bool $isAutoLoadProtectedFields
	 */
	public function __construct( $isAutoLoadProtectedFields = true ) {

		$this->__isAutoLoadProtectedFields = $isAutoLoadProtectedFields;
		$this->__childFieldsInfo           = [];
		$this->__external                  = [];
		$this->__isLoaded                  = false;

		$this->_autoInitFields();

	}


	//////// methods


	/**
	 * @return array [ fieldName => [fieldType], fieldName => [fieldType1, fieldType2] ]
	 */
	final private function _getChildFieldsInfo() {

		$fieldsInfo = [];

		// get only child fields (public, protected), so ignore all self fields (public, protected, private)

		$selfFieldNames  = array_keys( get_class_vars( self::class ) );
		$childFieldNames = array_keys( get_class_vars( static::class ) );
		$childFieldNames = array_diff( $childFieldNames, $selfFieldNames );

		foreach ( $childFieldNames as $childFieldName ) {

			try {
				// used static for child support
				$property = new ReflectionProperty( static::class, $childFieldName );
			} catch ( Exception $ex ) {

				Settings::Instance()->callErrorCallback( [
					'message' => $ex->getMessage(),
					'file'    => $ex->getFile(),
					'line'    => $ex->getLine(),
					'trace'   => $ex->getTraceAsString(),
				] );

				continue;
			}

			// ignore public fields (private fields don't available in the get_class_vars() method)

			if ( $property->isPublic() ) {
				continue;
			}

			// types can be multiple ex. : 'string|false', so types info should be always array

			$docTypes = [];

			$docComment   = $property->getDocComment();
			$propertyType = $property->getType();

			if ( $propertyType ) {
				$docTypes[] = $propertyType->getName();
			} else if ( $docComment ) {

				$matches = [];
				preg_match( '/@var\s*([^\s]+)/i', $docComment, $matches );

				if ( 2 === count( $matches ) ) {
					$docTypes = explode( '|', $matches[1] );
				}

			}

			$fieldsInfo[ $childFieldName ] = $docTypes;

		}

		return $fieldsInfo;
	}

	/**
	 * @return void
	 */
	final private function _autoInitFields() {

		if ( ! $this->__isAutoLoadProtectedFields ) {
			return;
		}

		$this->__childFieldsInfo = $this->_getChildFieldsInfo();

		foreach ( $this->__childFieldsInfo as $fieldName => $fieldTypes ) {

			// ignore fields without a type
			if ( ! $fieldTypes ) {
				continue;
			}

			$defaultValue = null;

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
				case 'array[]':
				case 'string[]':
				case 'int[]':
				case 'float[]':
					$defaultValue = [];
					break;
			}

			// ignore fields with a custom type (null by default)
			if ( is_null( $defaultValue ) ) {
				continue;
			}

			$this->{$fieldName} = $defaultValue;

		}

	}

	final protected function _load(): void {
		$this->__isLoaded = true;
	}

	public function getArgs(): array {

		$args = [
			'_external' => [],
			'_isLoaded' => $this->__isLoaded,
		];

		foreach ( $this->__childFieldsInfo as $fieldName => $fieldTypes ) {

			$argName  = ltrim( $fieldName, '_' );
			$argValue = $this->{$fieldName};

			if ( $argValue instanceof CONTROLLER ) {

				$argValue                      = $argValue->getTemplateArgs();
				$args['_external'][ $argName ] = [];

			}

			$args[ $argName ] = $argValue;

		}

		return $args;
	}

}
