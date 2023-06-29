<?php

namespace Falloff\Tokenizer;

class Stream{

	use HasRules;

	protected int $offset = 0;
	protected array $offsets = [];

	protected bool $eof = false;

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
		$this->eof = false;
	}

	function nextToken(){

		if( $this->eof )
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
				$this->offset += mb_strlen($matches[0][0]);

				if( $this->offset >= mb_strlen( $this->data ) )
					$this->eof = true;

				if( !empty( $this->callback ) ){
					$rv = ($this->callback)( $token );
					if( 
						is_object($rv) 
						and in_array(TokenData::class, class_implements($rv))
					){
						$token = $rv;
					}
				}

				return $token;
			}
		}

		// No rule can handle the output, throwing
		// Still the exception could be proceed as a token,
		// this allows building recoverable process

		preg_match("/\G./u", $this->data, $matches, PREG_OFFSET_CAPTURE, $this->offset);

		$e = new UnknownTokenException("No rules could match the character `{$matches[0][0]}` at offset `{$this->offset}` ");
		$e->setOffset($this->offset);
		$e->setValue($matches[0][0]);

		$this->offset += mb_strlen($matches[0][0]);

		if( $this->offset >= mb_strlen( $this->data ) )
			$this->eof = true;

		throw $e;

	}

	function tail(){
		return substr($this->data, $this->offset);
	}

	function onTokenRequest( callable $fn ){
		$this->callback = \Closure::fromCallable( $fn );
	}

	function __get($name){
		if( $name == 'eof' )
			return $this->eof;

		throw new \Exception("`$name` property does not exist for " . __CLASS__ . " instance");
	}


	function __invoke(){
		return $this->nextToken();
	}

}

