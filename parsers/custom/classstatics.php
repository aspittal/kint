<?php
class Kint_Parsers_ClassStatics extends kintParser
{
	protected function _parse( & $variable )
	{
		if ( !is_object( $variable ) ) return false;

		if (!defined('PHP_VERSION_ID')) {
			$version = explode('.', PHP_VERSION);
  
			define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
		} 
  
		if (version_compare(PHP_VERSION, '5.3.0') <= 0) {
			// We can't support reflection, so bail out.
			return false;
		} 

		$extendedValue = array();

		$reflection = new ReflectionClass( $variable );
		// first show static values
		foreach ( $reflection->getProperties( ReflectionProperty::IS_STATIC ) as $property ) {
			if ( $property->isPrivate() ) {
				$property->setAccessible( true );
				$access = "private";
			} elseif ( $property->isProtected() ) {
				$property->setAccessible( true );
				$access = "protected";
			} else {
				$access = 'public';
			}
			$access .= " static";

			$_      = $property->getValue();
			$output = kintParser::factory( $_, '$' . $property->getName() );

			$output->_access   = $access;
			$output->_operator = '::';
			$extendedValue[]   = $output;
		}

		foreach ( $reflection->getConstants() as $constant => $val ) {
			$output = kintParser::factory( $val, $constant );

			$output->_access   = 'constant';
			$output->_operator = '::';
			$extendedValue[]   = $output;
		}

		if ( empty( $extendedValue ) ) return false;

		$this->_value = $extendedValue;
		$this->_type  = 'Static class properties';
		$this->_size  = count( $extendedValue );
	}
}