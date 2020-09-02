<?php

namespace LightSource\BemBlocks;

/**
 * Class Settings
 * @package LightSource\BemBlocks
 */
final class Settings {


	//////// static fields


	/**
	 * @var self|null
	 */
	private static $_Instance = null;


	//////// fields


	/**
	 * @var string
	 */
	private $_blocksDirPath;
	/**
	 * @var string
	 */
	private $_blocksDirNamespace;
	/**
	 * @var string
	 */
	private $_twigArgs;
	/**
	 * @var string
	 */
	private $_twigExtension;
	/**
	 * @var callable|null Will call with an $errors array
	 */
	private $_errorCallback;


	//////// construct


	/**
	 * Settings constructor.
	 */
	public function __construct() {

		$this->_blocksDirPath      = '';
		$this->_blocksDirNamespace = '';
		$this->_twigArgs           = [
			// will generate exception if a var doesn't exist instead of replace to NULL
			'strict_variables' => true,
			// disable autoescape to prevent break data
			'autoescape'       => false,
		];
		$this->_twigExtension      = '.twig';
		$this->_errorCallback      = null;

	}

	/**
	 * @return self
	 */
	public static function Instance() {

		if ( is_null( self::$_Instance ) ) {
			self::$_Instance = new self();
		}

		return self::$_Instance;
	}


	//////// setters


	/**
	 * @param string $blocksDirPath
	 *
	 * @return void
	 */
	public function setBlocksDirPath( $blocksDirPath ) {
		$this->_blocksDirPath = $blocksDirPath;
	}

	/**
	 * @param string $blocksDirNamespace
	 *
	 * @return void
	 */
	public function setBlocksDirNamespace( $blocksDirNamespace ) {
		$this->_blocksDirNamespace = $blocksDirNamespace;
	}

	/**
	 * @param string $twigArgs
	 *
	 * @return void
	 */
	public function setTwigArgs( $twigArgs ) {
		$this->_twigArgs = array_merge( $this->_twigArgs, $twigArgs );
	}

	/**
	 * @param callable $errorCallback
	 *
	 * @return void
	 */
	public function setErrorCallback( $errorCallback ) {
		$this->_errorCallback = $errorCallback;
	}

	/**
	 * @param string $twigExtension
	 *
	 * @return void
	 */
	public function setTwigExtension( $twigExtension ) {
		$this->_twigExtension = $twigExtension;
	}


	//////// getters


	/**
	 * @return string
	 */
	public function getBlocksDirPath() {
		return $this->_blocksDirPath;
	}

	/**
	 * @return string
	 */
	public function getBlocksDirNamespace() {
		return $this->_blocksDirNamespace;
	}

	/**
	 * @return string
	 */
	public function getTwigArgs() {
		return $this->_twigArgs;
	}

	/**
	 * @return string
	 */
	public function getTwigExtension() {
		return $this->_twigExtension;
	}


	//////// methods


	/**
	 * @param array $errors
	 *
	 * @return void
	 */
	public function callErrorCallback( $errors ) {

		if ( ! is_callable( $this->_errorCallback ) ) {
			return;
		}

		call_user_func_array( $this->_errorCallback, [ $errors, ] );

	}

}
