<?php

return [

    /*
    |--------------------------------------------------------------------------
    | User Verification Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default user verification options for
    | your application. You may change these defaults as required
    | but as is they are a perfect start for most applications.
    |
    */

    'defaults' => [
        'verifications' => 'users',
    ],

    /*
	|--------------------------------------------------------------------------
	| Verifying Accounts
	|--------------------------------------------------------------------------
	|
	| You may specify multiple verification configurations if you have more
	| than one user table/model in the application, and you want to have
	| separate verification settings based on the specific user types.
	|
	| The expire time is the number of minutes that the reset token should be
	| considered valid. This security feature keeps tokens short-lived so
	| they have less time to be guessed. You may change this as needed.
	|
	*/

    'verifications' => [
        'users' => [
            'provider' => 'users',
            'table' => 'verification_requests',
            'expire' => 60,
        ],
    ],
];