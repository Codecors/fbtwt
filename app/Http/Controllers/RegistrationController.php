<?php

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    use App\Http\Requests;
    use App\Http\Controllers\Controller;
    use OAuth;
    use Session;
    use Twitter;

    /**
     * Class RegistrationController
     * @package App\Http\Controllers
     */
    class RegistrationController extends Controller
    {

        /**
         * @return \Illuminate\View\View
         */
        public function getLogin()
        {
            return view('auth.login');
        }


        /**
         * @return mixed
         */
        public function facebookLogin()
        {
            return OAuth::authorize('facebook');
        }


        /**
         * @return \Illuminate\Http\RedirectResponse
         */
        public function facebookRedirect()
        {
            // Automatically log in existing users
            // or create a new user if necessary.
            OAuth::login('facebook', function ($user, $details) {
                $user->name   = $details->full_name;
                $user->avatar = $details->avatar;
                $user->save();
            });

            // Current user is now available via Auth facade
            $user = auth()->user();
            if ($user->wasRecentlyCreated == true) {
                Session::put('user_is_new', 1);
            }

            return redirect()->intended('/home');
        }


        /**
         * @return mixed
         */
        public function twitterLogin()
        {
            $sign_in_twitter = true;
            $force_login     = false;

            Twitter::reconfig(['token' => '', 'secret' => '']);
            $token = Twitter::getRequestToken(route('twitter.redirect'));

            if (isset( $token['oauth_token_secret'] )) {
                $url = Twitter::getAuthorizeURL($token, $sign_in_twitter, $force_login);

                Session::put('oauth_state', 'start');
                Session::put('oauth_request_token', $token['oauth_token']);
                Session::put('oauth_request_token_secret', $token['oauth_token_secret']);

                return Redirect::to($url);
            }

            return Redirect::route('twitter.error');
        }


        /**
         * @return \Illuminate\Http\RedirectResponse
         */
        public function twitterRedirect()
        {
            if (Session::has('oauth_request_token')) {
                $request_token = [
                    'token'  => Session::get('oauth_request_token'),
                    'secret' => Session::get('oauth_request_token_secret'),
                ];

                Twitter::reconfig($request_token);

                $oauth_verifier = false;

                if (Input::has('oauth_verifier')) {
                    $oauth_verifier = Input::get('oauth_verifier');
                }

                // getAccessToken() will reset the token for you
                $token = Twitter::getAccessToken($oauth_verifier);

                if ( ! isset( $token['oauth_token_secret'] )) {
                    return Redirect::route('twitter.login')->with('flash_error', 'We could not log you in on Twitter.');
                }

                $credentials = Twitter::getCredentials();

                if (is_object($credentials) && ! isset( $credentials->error )) {
                    // $credentials contains the Twitter user object with all the info about the user.
                    // Add here your own user logic, store profiles, create new users on your tables...you name it!
                    // Typically you'll want to store at least, user id, name and access tokens
                    // if you want to be able to call the API on behalf of your users.

                    // This is also the moment to log in your users if you're using Laravel's Auth class
                    // Auth::login($user) should do the trick.

                    Session::put('access_token', $token);

                    return Redirect::to('/')->with('flash_notice', 'Congrats! You\'ve successfully signed in!');
                }

                return Redirect::route('twitter.error')->with('flash_error',
                    'Crab! Something went wrong while signing you up!');

                // Current user is now available via Auth facade
                $user = auth()->user();
                if ($user->wasRecentlyCreated == true) {
                    Session::put('user_is_new', 1);
                }

                return redirect()->intended('/home');
            }
        }
    }
