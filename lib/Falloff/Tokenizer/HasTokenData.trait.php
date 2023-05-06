<?php

namespace Falloff\Tokenizer;

trait HasTokenData {

	protected ?string $type = null;
	protected string $value;
	protected int $offset;

	function __set( $what, $value ){

		if( in_array($what, ['type','value','offset']) ){

			if( $what != 'type' and isset( $this->$what ) )
				throw new \Exception("The `$what` is already set for this token-alike object");

			$this->$what = $value;
			return;
		}

		$iam = self::class;
		throw new \Exception("Cannot set non-existing field `{$what}` for token of type `{$this->type}` for the class `{$iam}`");
	}


	function __get( $what ){
		if( in_array($what, ['type','value','offset']) )
			return $this->$what;

		throw new \Exception("No such field {$what} for token of type {$this->type}");
	}

}

	


