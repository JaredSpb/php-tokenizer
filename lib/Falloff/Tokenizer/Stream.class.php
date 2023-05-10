<?php

namespace Falloff\Tokenizer;

class Stream{

	use HasRules;

	protected int $offset = 0;
	protected array $offsets = [];

	protected string $data;
	protected Token $current_token;
	protected ?\Closure $callback;

	function __construct( string $string ){
		$this->data = $string;
	}

	function skip( array $types ){

		do{

			$token = $this->nextToken();

		} while( in_array($token->type, $types) );

		return $token;

	}

	function rewind( int $amount = 1 ){
		$tail = array_splice($this->offsets, count( $this->offsets ) - $amount );
		$this->offset = $tail[0];
	}

	function nextToken(){

		if( $this->offset >= strlen( $this->data ) )
			return false;

		$this->offsets[] = $this->offset;

		foreach ($this->rules as $rule) {

			[$name, $re] = $rule;
			
			$match = null;
			if( preg_match($re, $this->data, $matches, PREG_OFFSET_CAPTURE, $this->offset) ){

				$token = new Token(
					$name,
					$matches[0][0],
					$matches[0][1],
				);

				// Advancing offset
				$this->offset += strlen($matches[0][0]);

				if( !empty( $this->callback ) )
					($this->callback)( $token );

				return $token;
			}
		}

		// No rule can handle the output, throwing
		// Still the exception could be proceed as a token,
		// this allows building recoverable process

		preg_match("/\G./u", $this->data, $matches, PREG_OFFSET_CAPTURE, $this->offset);

		$e = new UnknownTokenException("No rules could match the character `{$matches[0][0]}` at offset `{$this->offset}` ");
		$e->offset = $this->offset;
		$e->value = $matches[0][0];

		$this->offset += strlen($matches[0][0]);

		throw $e;

	}

	function tail(){
		return substr($this->data, $this->offset);
	}

	function onTokenRequest( callable $fn ){
		$this->callback = \Closure::fromCallable( $fn );
	}

	function __invoke(){
		return $this->nextToken();
	}

}

