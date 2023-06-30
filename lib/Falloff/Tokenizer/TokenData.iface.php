<?php

namespace Falloff\Tokenizer;

interface TokenData{
	function __set( string $what, $value ) : void;
	function __get( string $value );

	function setOffset( int $offset ) : void;
	function setValue( ?string $value ) : void;
	function setType( ?string $type ) : void;
}

