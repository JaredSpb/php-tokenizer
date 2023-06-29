<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use Falloff\Tokenizer\{
	Factory,
	UnknownTokenException,
	Token,
};


class TokenizerTest extends TestCase
{
	protected $base_rules = [
		'NON_SPACE_CHARACTER' => '/\\G[^\\s]/u',
		'ANY_CHARACTER' => '/\\G./u'
	];

    function testBasic(): void{

		$data = 'a b';		
		$stream = (new Factory($this->base_rules))->getStream( $data );

		$proposed_result = [
			['NON_SPACE_CHARACTER', 'a', 0],
			['ANY_CHARACTER', ' ', 1],
			['NON_SPACE_CHARACTER', 'b', 2],
		];

		// With this flag we'll test invoking the stream directly
		$invoke = false;

		while( $token = ( $invoke ? $stream() : $stream->nextToken() ) ){
			$this->assertTrue( count( $proposed_result ) > 0 );

			$check  = array_shift( $proposed_result );
			$this->assertEquals( $check[0], $token->type );
			$this->assertEquals( $check[1], $token->value );
			$this->assertEquals( $check[2], $token->offset );

			$invoke = true;
		}

		$this->assertTrue( $stream->nextToken() === false );		

    }

    public function testDynamicModification(): void {

		$data = 'a b12';
		$factory = new Factory($this->base_rules);

		// First run get the stream and mutates it
		// Second mutates the factory and gets the stream after
		foreach( range(0, 1) as $q ){
			
			if( $q === 0 ){
				$stream = $factory->getStream( $data );
				$stream->prependRule('DIGIT', '/\\G\d/');
				$stream->prependRules(['THE_TWO' => '/\\G2/']);
			} else {
				$factory->prependRule('DIGIT', '/\\G\d/');
				$factory->prependRules(['THE_TWO' => '/\\G2/']);
				$stream = $factory->getStream( $data );
			}

			$proposed_result = [
				['NON_SPACE_CHARACTER', 'a', 0],
				['ANY_CHARACTER', ' ', 1],
				['NON_SPACE_CHARACTER', 'b', 2],
				['DIGIT', '1', 3],
				['THE_TWO', '2', 4],
			];

			while( $token = $stream->nextToken() ){
				$this->assertTrue( count( $proposed_result ) > 0 );

				$check = array_shift( $proposed_result );
				$this->assertEquals( $check[0], $token->type );
				$this->assertEquals( $check[1], $token->value );
				$this->assertEquals( $check[2], $token->offset );
			}

			$this->assertTrue( $stream->nextToken() === false );
		}

    }

    public function testProcessTokenAlikeException(): void {
		
		$data = 'aba';
		$stream = (new Factory(['A_CHARACTER' => '/\\Ga/']))->getStream($data);

		try{
			$token = $stream();
			$token = $stream();
			$this->fail("`UnknownTokenException` was expected");
		} catch( UnknownTokenException $e ){
			$this->assertNUll( $e->type  );
			$this->assertEquals( $e->value, 'b' );
			$this->assertEquals( $e->offset, 1 );
		}

		$token = $stream();

		$this->assertEquals( $token->type, 'A_CHARACTER' );
		$this->assertEquals( $token->value, 'a' );
		$this->assertEquals( $token->offset, 2 );

		

    }

    public function testTail(): void {

		$data = 'abcde';
		$stream = (new Factory(['A_CHARACTER' => '/\\Ga/']))->getStream($data);
		$stream();

		$this->assertEquals( $stream->tail(), 'bcde' );

    }

    public function testNotify(): void {

    	$data = 'abcde';
    	$arr = str_split($data);

		$stream = (new Factory(['CHARACTER' => '/\\G./']))->getStream($data);

		$stream->onTokenRequest( function( Token|UnknownTokenException $token ) use( $arr ) {

			$this->assertEquals( $token->value, $arr[ $token->offset ] );
			
		} );

		while($stream()){};

    }

    public function testChangeToken(): void {

    	$data = 'abcde';

		$stream = (new Factory(['CHARACTER' => '/\\G./']))->getStream($data);

		$stream->onTokenRequest( function( Token|UnknownTokenException $token ){
			return new Token('Q_SYMBOL','q', $token->offset);
		} );

		$this->assertEquals( $stream()->value, 'q' );


    }

    public function testThrows(): void {

    	$data = 'b';
		$stream = (new Factory(['CHARACTER' => '/\\Ga/']))->getStream($data);
		$this->expectException(UnknownTokenException::class);

		$stream();


    }

}

