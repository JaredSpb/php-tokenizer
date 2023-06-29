# php-tokenizer
A simple RE driven tokenizer

This package provides a simple standalone
regular expressions powered tokenizer.

The example usage:

```php
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
# Token has type `NON_SPACE_STRING` and its value is `1qz` at offset `4`
```

*Note:* the regexps used MUST start with the `\G` assertion.

*Note:* data is interpretted as UTF-8 so regexps are recommended to be provided
with a `u` setting.

Rules might be added on the fly to the factory or either the stream itself. Adding 
rules to the factory will not affect the streams instantinated already.

```php
$rules = [ 
    'NON_SPACE_STRING' => '/\\G[^\\s]+/u',
    'ANY_CHARACTER' => '/\\G./u'
];

$string = 'a b 1 qz';
$stream = ( new Falloff\Tokenizer\Factory( $rules ) )->getStream( $stream );

// This rule will never trigger, coz 'NON_SPACE_STRING' will be macthed earlier
$stream->appendRule('DIGIT', '/\\G\d/u');

// Prepending rules, so these will be matched before the 'NON_SPACE_STRING'
$stream->prependRules([
    'Q_CHAR' => '/\\Gq/u',
    'Z_CHAR' => '/\\Gz/u',
]);

// Stream might be invoked like it was a function
while( $token = $stream() ){
    print "Token has type `{$token->type}` and its value is `{$token->value}` at offset `{$token->offset}`\n";
}

// The output is:
# Token has type `NON_SPACE_STRING` and its value is `a` at offset `0`
# Token has type `ANY_CHARACTER` and its value is ` ` at offset `1`
# Token has type `NON_SPACE_STRING` and its value is `b` at offset `2`
# Token has type `ANY_CHARACTER` and its value is ` ` at offset `3`
# Token has type `NON_SPACE_STRING` and its value is `1` at offset `4`
# Token has type `ANY_CHARACTER` and its value is ` ` at offset `5`
# Token has type `Q_CHAR` and its value is `q` at offset `6`
# Token has type `Z_CHAR` and its value is `z` at offset `7`
```

When no rules matched for the next chunk of input stream, the `UnknownTokenException` is thrown. This exception is a token itself.
It has its type set to `NULL` but yet allows accessing the `value` and `offset` properties.

When stream ends, the call for next token will return `false`. The `eof` property may be checked to 
retrieve the stream state without asking for next token:

```php
if( $stream->eof ){
    print "Got all the tokens we had there";
} else{
    $token = $stream();
}
```

The remaining substring may be retrieved at any moment with the `tail` method:

```php
print "The untokenized substring currently is: " . $stream->tail();
```

Stream can have an attached callback that is triggered every time token is requested from the tokenizer:

```php
use \Falloff\Tokenizer\{UnknownTokenException,Token};

$stream->onTokenRequest(function( UnknownTokenException|Token $token ){
    print $token->type . ' token retrieved from the stream';
});

```

If this callback returns a `Token` instance, this instance will be returned to the initial caller:




