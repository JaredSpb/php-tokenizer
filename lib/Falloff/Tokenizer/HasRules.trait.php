<?php

namespace Falloff\Tokenizer;

trait HasRules {
	protected array $rules;

	function appendRules( array $rules ){

		foreach( $rules as $name => $rule ){
			$this->appendRule( $name, $rule );
		}
	}

	function prependRules( array $rules ){
		foreach( $rules as $name => $rule ){
			$this->prependRule( $name, $rule );
		}
	}

	function prependRule( string $name, string $re ){
		array_unshift($this->rules, [$name, $re]);
	}

	function appendRule( string $name, string $re ){
		$this->rules[] = [ $name, $re ];
	}
}

	