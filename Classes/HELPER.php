<?php

namespace LightSource\BemBlocks;

/**
 * Class HELPER
 * @package LightSource\BemBlocks
 */
abstract class HELPER {


	//////// static methods


	/**
	 * Working like std, but added special to remind about default keys saving,
	 * it's can break some code if it wait work with first-[0] element
	 *
	 * @param array $array
	 * @param callable $callback
	 * @param bool $isSaveKeys
	 *
	 * @return array
	 */
	final public static function ArrayFilter( $array, $callback, $isSaveKeys ) {

		$arrayResult = array_filter( $array, $callback );

		return $isSaveKeys ?
			$arrayResult :
			array_values( $arrayResult );
	}

}
