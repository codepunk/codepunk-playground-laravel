<?php

namespace Codepunk\UserVerification;

use Codepunk\UserVerification\Support\Facades\Verification;
use Illuminate\Http\Request;

trait SendsVerificationEmails
{
    /**
     * The user has been registered.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  mixed $user
     * @return mixed
     */
    protected function registered(/** @noinspection PhpUnusedParameterInspection */
        Request $request,
        $user)
    {
        auth()->logout();
        return $this->sendVerificationLinkEmail($request);
    }

    /* TODO CODEPUNK
    public function showLinkRequestForm()
    {
        return view('auth.verifications.email');
    }
    */

    /**
     * Send a verification link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendVerificationLinkEmail(Request $request)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->validate($request, ['email' => 'required|email']);
        // We will send the verification link email to this user. Once having attempted
        // to send the link, we will then examine the response to see the message we
        // need to show to this user. Finally, we will send out a proper response.
        $response = $this->broker()->sendVerificationLink(
            $request->only('email')
        );
        return $response == Verification::VERIFICATION_LINK_SENT
            ? $this->sendVerificationLinkResponse($request, $response)
            : $this->sendVerificationLinkFailedResponse($request, $response);
    }

    /**
     * Get the response for a successful verification link.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendVerificationLinkResponse(Request $request, $response)
    {
        $request->flashOnly('email');
        return redirect('login')->with('status', trans($response));
    }

    /**
     * Get the response for a failed verification link.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendVerificationLinkFailedResponse(/** @noinspection PhpUnusedParameterInspection */
        Request $request,
        $response)
    {
        // TODO CODEPUNK Need to figure out exactly how to handle this error
        return back()->withErrors(
            ['email' => trans($response)]
        );
    }

    /**
     * Get the broker to be used during verification.
     *
     * @return \Codepunk\UserVerification\Contracts\VerificationBroker
     */
    public function broker()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return Verification::broker();
    }
}