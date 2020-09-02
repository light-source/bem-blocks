<?php

namespace LightSource\BemBlocks;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

use Exception;

/**
 * Class Html
 * @package LightSource\BemBlocks
 */
final class Html {


	//////// static fields


	/**
	 * @var self|null
	 */
	private static $_Instance = null;


	//////// fields


	/**
	 * @var FilesystemLoader|null
	 */
	private $_twigLoader;
	/**
	 * @var Environment|null
	 */
	private $_twigEnvironment;


	//////// construct


	/**
	 * Html constructor.
	 */
	private function __construct() {

		$this->_twigLoader      = null;
		$this->_twigEnvironment = null;

		$this->_init();

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


	//////// methods


	/**
	 * @return void
	 */
	private function _init() {

		$settings = Settings::Instance();

		try {

			$this->_twigLoader = new FilesystemLoader( $settings->getBlocksDirPath() );
			$this->_twigEnvironment = new Environment( $this->_twigLoader, $settings->getTwigArgs() );

		} catch ( Exception $ex ) {

			$this->_twigLoader      = null;
			$this->_twigEnvironment = null;

			Settings::Instance()->callErrorCallback( [
				'exception' => $ex,
			] );


		}

	}

	/**
	 * @param string $template Relative path to a file (relative from self::$PathToHtml)
	 * @param array $args [ key => value ] Args for template
	 * @param bool $isPrint
	 *
	 * @return string Rendered html
	 */
	public function render( $template, $args = [], $isPrint = false ) {

		$html = '';

		// twig doesn't loaded

		if ( is_null( $this->_twigEnvironment ) ) {
			return $html;
		}

		try {
			// will generate ean exception if a template doesn't exist OR broken
			// also if a var doesn't exist (if using a 'strict_variables' flag, see Twig_Environment->__construct)
			$html .= $this->_twigEnvironment->render( $template, $args );
		} catch ( Exception $ex ) {

			$html = '';

			Settings::Instance()->callErrorCallback( [
				'exception' => $ex,
				'template'  => $template,
				'args'      => $args,
			] );

		}

		if ( $isPrint ) {
			echo $html;
		}

		return $html;

	}

}
