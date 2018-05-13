<?php

namespace Codepunk\UserVerification;

use App\Models\User;
use Codepunk\UserVerification\Support\Facades\Verification;
use Illuminate\Foundation\Auth\RedirectsUsers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

trait VerifiesUsers
{
    use RedirectsUsers;

    // TODO CODEPUNK
    /*
    public function showResendForm(Request $request, $token = null)
    {
        return view('auth.verification.resend')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }
    */

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        if (!$user->verified) {
            auth()->logout();
            $request->flashOnly('email');
            return redirect()
                ->back()
                ->with('warning', trans('verification.unverified'))
                ->with('resend', true);
        }
        return null;
    }

    /**
     * Verify the given user's account.
     *
     * @param Request $request
     * @param string  $token
     * @return Response|mixed
     */
    public function verify(Request $request, $token) {
        // Here we will attempt to verify the user's account. If it is successful we will
        // update the verified attribute on an actual user model and persist it to the
        // database. If it is unsuccessful, parse the error and return the response.
        $response = $this->broker()->verify(
            $token, /*$this->credentials($request, $token), TODO CODEPUNK Not needed? */
            function ($user) {
                $this->verifyUser($user);
            }
        );

        // If this user was successfully verified, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $response == Verification::VERIFIED
            ? $this->sendVerificationResponse($response)
            : $this->sendVerificationFailedResponse($request, $response);
    }

//    TODO CODEPUNK Not needed?
//    /**
//     * Get the verification credentials from the request.
//     *
//     * @param  \Illuminate\Http\Request  $request
//     * @param  string  $token
//     * @return array
//     */
//    protected function credentials(/** @noinspection PhpUnusedParameterInspection */ Request $request, $token)
//    {
//        return array($token);
//    }

    /**
     * Verify the given user's account.
     *
     * @param  User  $user
     * @return void
     */
    protected function verifyUser($user)
    {
        $user->setAttribute(
            'verified', true
        )->save();
    }

    /**
     * Get the response for a successful account verification.
     *
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendVerificationResponse($response)
    {
        return redirect($this->redirectPath())
            ->with('status', trans($response));
    }

    /**
     * Get the response for a failed account verification.
     *
     * @param  \Illuminate\Http\Request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendVerificationFailedResponse(
        /** @noinspection PhpUnusedParameterInspection */ Request $request,
                                                          $response)
    {
        return redirect($this->redirectTo())
            ->with('warning', trans($response));
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return \Codepunk\UserVerification\Contracts\VerificationBroker
     */
    public function broker()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return Verification::broker();
    }

    /**
     * Get the guard to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }

    /**
     * Get the post register / login redirect path.
     *
     * @return string
     */
    protected function redirectTo() {
        return 'login';
    }
}