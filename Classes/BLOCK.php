<?php

namespace LightSource\BemBlocks;

/**
 * Class BLOCK
 * @package LightSource\BemBlocks
 */
abstract class BLOCK {


	//////// static fields


	/**
	 * @var array
	 */
	private static $_Classes = [];


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

		$phpFileNames = HELPER::ArrayFilter( $fs, function ( $f ) {
			return ( false !== strpos( $f, '.php' ) &&
			         'index.php' !== $f );
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

			if ( ! class_exists( $phpClass, false ) ) {


				Settings::Instance()->callErrorCallback( [
					'message' => "Class file doesn't correct",
					'args'    => $debugArgs,
				] );

				continue;
			}

			if ( ! is_subclass_of( $phpClass, self::class ) ) {

				Settings::Instance()->callErrorCallback( [
					'message' => "Class doesn't child'",
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

	/**
	 * @return void
	 */
	final private static function _LoadAll() {

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


	//////// getters


	/**
	 * @return string Path to a twig template (relative to Settings->_blocksDirPath)
	 */
	final public static function GetTwigTemplate() {

		// getting namespace without a root part, so will match to a twig path
		// used static for child support
		$fullClassName = str_replace( Settings::Instance()->getBlocksDirNamespace() . '\\', '', static::class );

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

	/**
	 * @return array
	 */
	public function getTemplateArgs() {
		return [];
	}


	//////// methods


	/**
	 * @param array $args
	 *
	 * @return string
	 */
	final public function render( $args = [] ) {

		$args = array_merge( $this->getTemplateArgs(), $args );

		return Html::Instance()->render( self::GetTwigTemplate(), $args );
	}

}

