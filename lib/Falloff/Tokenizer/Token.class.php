<?php

namespace Falloff\Tokenizer;

class Token implements TokenData{

	use HasTokenData;


	function __construct( string $type, string $value, int $offset ){
		$this->type = $type;
		$this->value = $value;
		$this->offset = $offset;
	}



}
