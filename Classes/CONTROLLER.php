<?php

namespace LightSource\BemBlocks;

/**
 * Class CONTROLLER
 * @package LightSource\BemBlocks
 */
abstract class CONTROLLER {

	//////// static fields

	/**
	 * @var array
	 */
	private static $_Classes = [];

	//////// fields

	/**
	 * @var MODEL
	 */
	private $_model;

	//////// constructor

	/**
	 * CONTROLLER constructor.
	 *
	 * @param MODEL $model
	 */
	public function __construct( $model ) {
		$this->_model = $model;
	}

	//////// static methods

	/**
	 * @param string $directory
	 * @param string $namespace
	 *
	 * @return void
	 */
	final private static function _LoadDirectory( $directory, $namespace ) {

		// exclude ., ..
		$fs = array_diff( scandir( $directory ), [ '.', '..' ] );

		$controllerFilePreg = '/' . Settings::Instance()->getControllerSuffix() . '.php$/';

		$phpFileNames = HELPER::ArrayFilter( $fs, function ( $f ) use ( $controllerFilePreg ) {
			return ( 1 === preg_match( $controllerFilePreg, $f ) );
		}, false );

		$subDirectoryNames = HELPER::ArrayFilter( $fs, function ( $f ) {
			return false === strpos( $f, '.' );
		}, false );

		foreach ( $phpFileNames as $phpFileName ) {

			$phpFile   = implode( DIRECTORY_SEPARATOR, [ $directory, $phpFileName ] );
			$phpClass  = implode( '\\', [ $namespace, str_replace( '.php', '', $phpFileName ), ] );
			$debugArgs = [
				'directory' => $directory,
				'namespace' => $namespace,
				'phpFile'   => $phpFile,
				'phpClass'  => $phpClass,
			];

			require_once $phpFile;

			if ( ! class_exists( $phpClass, false ) ||
			     ! is_subclass_of( $phpClass, self::class ) ) {

				Settings::Instance()->callErrorCallback( [
					'message' => "Class doesn't exist or doesn't child",
					'args'    => $debugArgs,
				] );

				continue;
			}

			self::$_Classes[] = $phpClass;

		}

		foreach ( $subDirectoryNames as $subDirectoryName ) {

			$subDirectory = implode( DIRECTORY_SEPARATOR, [ $directory, $subDirectoryName ] );
			$subNamespace = implode( '\\', [ $namespace, $subDirectoryName ] );

			self::_LoadDirectory( $subDirectory, $subNamespace );

		}


	}

	final private static function _LoadAll(): void {

		$directory = Settings::Instance()->getBlocksDirPath();
		$namespace = Settings::Instance()->getBlocksDirNamespace();

		// exclude ., ..
		$fs = array_diff( scandir( $directory ), [ '.', '..', ] );

		$subDirectoryNames = HELPER::ArrayFilter( $fs, function ( $f ) {
			return false === strpos( $f, '.' );
		}, false );

		foreach ( $subDirectoryNames as $subDirectoryName ) {

			$subDirectory = implode( DIRECTORY_SEPARATOR, [ $directory, $subDirectoryName ] );
			$subNamespace = implode( '\\', [ $namespace, $subDirectoryName ] );

			self::_LoadDirectory( $subDirectory, $subNamespace );

		}

	}

	final private static function _ArrayMergeRecursive( array $args1, array $args2 ): array {

		foreach ( $args2 as $key => $value ) {

			if ( intval( $key ) === $key ) {

				$args1[] = $value;

				continue;
			}

			// recursive sub-merge for internal arrays
			if ( is_array( $value ) &&
			     key_exists( $key, $args1 ) &&
			     is_array( $args1[ $key ] ) ) {
				$value = self::_ArrayMergeRecursive( $args1[ $key ], $value );
			}

			$args1[ $key ] = $value;

		}

		return $args1;
	}

	/**
	 * @return void Can be used for a block resources registration , e.g. wordpress hooks, etc..
	 */
	protected static function _Init() {

	}

	/**
	 * @return void
	 */
	final public static function InitAll() {

		self::_LoadAll();

		foreach ( self::$_Classes as $blockClass ) {
			call_user_func( [ $blockClass, '_Init' ] );
		}

	}

	/**
	 * @param string $class Can provide another than static::class (e.g. parent::class)
	 *
	 * @return string Path to a twig template (relative to Settings->_blocksDirPath)
	 */
	public static function GetTwigTemplate( string $class = '' ) {

		$class            = ! $class ?
			static::class :
			$class;
		$controllerSuffix = Settings::Instance()->getControllerSuffix();

		/**
		 * Prepare the fullClassName :
		 * 1. getting namespace without a root part, so will match to a twig path
		 * 2. used static for child support
		 * 3. getting without a controller suffix
		 */

		$fullClassName = str_replace( Settings::Instance()->getBlocksDirNamespace() . '\\', '', $class );
		$fullClassName = substr( $fullClassName, 0, mb_strlen( $fullClassName ) - mb_strlen( $controllerSuffix ) );

		$shortName = explode( '\\', $fullClassName );
		$shortName = $shortName[ count( $shortName ) - 1 ];

		// get a twig template name

		$shortNameParts    = preg_split( '/(?=[A-Z])/', $shortName, - 1, PREG_SPLIT_NO_EMPTY );
		$newShortNameParts = [];
		foreach ( $shortNameParts as $shortNamePart ) {
			$newShortNameParts[] = strtolower( $shortNamePart );
		}
		$twigTemplateName = implode( '-', $newShortNameParts );
		$twigTemplateName = str_replace( '_', '-', $twigTemplateName ) . Settings::Instance()->getTwigExtension();

		// get a twig template path

		$twigTemplatePath = explode( '\\', $fullClassName );
		$twigTemplatePath = array_slice( $twigTemplatePath, 0, count( $twigTemplatePath ) - 1 );
		$twigTemplatePath = implode( DIRECTORY_SEPARATOR, $twigTemplatePath );

		return $twigTemplatePath . DIRECTORY_SEPARATOR . $twigTemplateName;
	}

	//////// getters

	/**
	 * @return array
	 */
	public function getTemplateArgs() {
		// using static for child support
		return array_merge( [
			'_template' => static::GetTwigTemplate(),
		], $this->_model->getArgs() );
	}

	/**
	 * @return mixed
	 * Can be overridden for IDE support
	 */
	public function getModel() {
		return $this->_model;
	}

	//////// methods

	/**
	 * @param array $args
	 * @param bool $isPrint
	 *
	 * @return string
	 */
	final public function render( $args = [], $isPrint = false ) {

		$args = self::_ArrayMergeRecursive( $this->getTemplateArgs(), $args );

		// using static for child support
		return Html::Instance()->render( static::GetTwigTemplate(), $args, $isPrint );
	}

}
