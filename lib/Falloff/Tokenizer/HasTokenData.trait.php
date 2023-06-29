<?php

namespace Falloff\Tokenizer;

trait HasTokenData {

	protected readonly ?string $type;
	protected readonly string $value;
	protected readonly int $offset;

	function __set( string $what, $value ) : void {

		if( in_array($what, ['type','value','offset']) ){

			if( $what != 'type' and isset( $this->$what ) )
				throw new \Exception("The `$what` is already set for this token-alike object");

			$this->$what = $value;
			return;
		}

		$iam = self::class;
		throw new \Exception("Cannot set non-existing field `{$what}` for token of type `{$this->type}` for the class `{$iam}`");
	}


	function __get( string $what ) {
		if( in_array($what, ['type','value','offset']) )
			return $this->$what;

		throw new \Exception("No such field {$what} for token of type {$this->type}");
	}

	function setOffset( int $offset ) : void {
		$this->offset = $offset;
	}
	function setValue( string $value ) : void {
		$this->value = $value;
	}
	function setType( ?string $type ) : void {
		$this->type = $type;
	}

}

	


