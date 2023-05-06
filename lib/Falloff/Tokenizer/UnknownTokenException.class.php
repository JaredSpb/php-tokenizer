<?php

namespace Falloff\Tokenizer;

class UnknownTokenException extends \Exception{

	use HasTokenData;

	function __construct(string $message = "", int $code = 0, ?Throwable $previous = null){
		parent::__construct( $message, $code, $previous );
	}


}



