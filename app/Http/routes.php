<?php

    Route::get('/', [
        'as' => 'home',
        function () {
            return view('welcome');
        },
    ]);

    Route::get('login', ['as' => 'login', 'uses' => 'RegistrationController@getLogin']);

    Route::get('login-facebook', ['as' => 'facebook.login', 'uses' => 'RegistrationController@facebookLogin']);
    Route::get('login-twitter', ['as' => 'twitter.login', 'uses' => 'RegistrationController@twitterLogin']);

    Route::get('redirect-facebook', ['as' => 'facebook.redirect', 'uses' => 'RegistrationController@facebookRedirect']);
    Route::get('redirect-twitter', ['as' => 'twitter.redirect', 'uses' => 'RegistrationController@twitterRedirect']);