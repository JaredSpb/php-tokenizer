<?php
namespace Falloff\Tokenizer;

class Factory{
	use HasRules;

	function __construct( $rules ){
		$this->appendRules( $rules );
	}
	function getStream( $string ){
		$stream = new Stream( $string );
		foreach( $this->rules as $rule ){
			$stream->appendRule( $rule[0], $rule[1] );
		}
		return $stream;
	}

}
