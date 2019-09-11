<?php

namespace PendoNL\LaravelExactOnline\Http\Controllers;

use App\User;
use Illuminate\Routing\Controller;
use PendoNL\LaravelExactOnline\LaravelExactOnline;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class LaravelExactOnlineController extends Controller
{
    /**
     * Connect Exact app
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function appConnect() {
        return view('laravelexactonline::connect');
    }

    /**
     * Authorize to Exact
     * Sends an oAuth request to the Exact App to get tokens
     */
    public function appAuthorize() {
        $connection = app()->make('Exact\Connection');
        return ["url" => $connection->getAuthUrl()];
    }

    /**
     * Exact Callback
     * Saves the authorisation and refresh tokens
     */
    public function appCallback() {


//        $id = Crypt::decryptString(request()->get('user'));
        Auth::shouldUse('web');
        Auth::loginUsingId(request()->get('user'));

        $config = LaravelExactOnline::loadConfig();

        $config->authorisationCode = request()->get('code');
        LaravelExactOnline::storeConfig($config);

        $connection = app()->make('Exact\Connection');

        return redirect("easykas://return");
    }
}
