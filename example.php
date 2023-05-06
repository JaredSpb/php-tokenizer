<?php
/**
* @package Falloff/Tokenizer
*/
include(__DIR__.'/vendor/autoload.php');


/**
* @example Base usage
*/

$rules = [ 
	'NON_SPACE_STRING' => '/\\G[^\\s]+/u',
	'ANY_CHARACTER' => '/\\G./u'
];

$string = 'abc 1qz';
$stream = ( new Falloff\Tokenizer\Factory( $rules ) )->getStream( $string );
while( $token = $stream->nextToken() ){
	print "Token has type `{$token->type}` and its value is `{$token->value}` at offset `{$token->offset}`\n";
}

// The output is:
# Token has type `NON_SPACE_STRING` and its value is `abc` at offset `0`
# Token has type `ANY_CHARACTER` and its value is ` ` at offset `3`
# Token has type `NON_SPACE_STRING` and its value is `b` at offset `4`

print "\n";


/**
* @example Advanced usage
*/

$rules = [ 
	'NON_SPACE_CHARACTER' => '/\\G[^\\s]/u',
];

$string = 'a b 1qz';
$stream = ( new Falloff\Tokenizer\Factory( $rules ) )->getStream( $string );

$stream->prependRule('DIGIT', '/\\G\d/u');

// Prepending rules, so these will be matched before the 'NON_SPACE_CHARACTER'
$stream->prependRules([
	'Q_CHAR' => '/\\Gq/u',
	'Z_CHAR' => '/\\Gz/u',
]);

$stream->appendRule( 'ANY_CHARACTER', '/\\G./u');

// Stream might be invoked like it was a function
while( $token = $stream() ){
	print "Token has type `{$token->type}` and its value is `{$token->value}` at offset `{$token->offset}`\n";
}

// The output is:
#Token has type `NON_SPACE_CHARACTER` and its value is `a` at offset `0`
#Token has type `ANY_CHARACTER` and its value is ` ` at offset `1`
#Token has type `NON_SPACE_CHARACTER` and its value is `b` at offset `2`
#Token has type `ANY_CHARACTER` and its value is ` ` at offset `3`
#Token has type `DIGIT` and its value is `1` at offset `4`
#Token has type `Q_CHAR` and its value is `q` at offset `5`
#Token has type `Z_CHAR` and its value is `z` at offset `6`

print "\n";

/**
* @example Processing unknown tokens
*/


$string = 'aba';
$stream = ( new Falloff\Tokenizer\Factory(['A_SYMBOL' => '/\\Ga/']) )->getStream( $string );
try{
	$token = $stream(); // Fetching 'a' symbol
	print "Got a regular token of type `{$token->type}` with `{$token->value}` payload at offset {$token->offset}\n";

	$stream(); // This will fail
} catch( Falloff\Tokenizer\UnknownTokenException $e ){

	print "Got an UnknownTokenException providing token of type NULL with `{$e->value}` payload at offset {$e->offset}\n";

}

$token = $stream(); // Fetching 'a' symbol
print "Got a regular token of type `{$token->type}` with `{$token->value}` payload at offset {$token->offset}\n";
