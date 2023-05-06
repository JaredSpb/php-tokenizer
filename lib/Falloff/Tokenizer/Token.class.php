<?php

namespace Falloff\Tokenizer;

class Token{

	use HasTokenData;


	function __construct( string $type, string $value, int $offset ){
		$this->type = $type;
		$this->value = $value;
		$this->offset = $offset;
	}



}
