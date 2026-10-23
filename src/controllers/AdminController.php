<?php namespace crocodicstudio\crudbooster\controllers;

use CRUDBooster;
use crocodicstudio\crudbooster\security\AdminAuthenticationService;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class AdminController extends CBController
{
    private AdminAuthenticationService $authentication;

    public function __construct(
        ?AdminAuthenticationService $authentication = null
    ) {
        $this->authentication = $authentication ?: app(AdminAuthenticationService::class);
    }

    public function getIndex()
    {
        $data = [];
        $data['page_title'] = '<strong>Dashboard</strong>';

        return view('crudbooster::home', $data);
    }

    public function getLockscreen()
    {

        if (! CRUDBooster::myId()) {
            Session::flush();

            return redirect()->route('getLogin')->with('message', cbLang('alert_session_expired'));
        }

        $this->authentication->lockScreen();

        return view('crudbooster::lockscreen');
    }

    public function postUnlockScreen()
    {
        $password = request('password');

        if ($this->authentication->unlock($password)) {
            return redirect(CRUDBooster::adminPath());
        }

        echo "<script>alert('".cbLang('alert_password_wrong')."');history.go(-1);</script>";
    }

    public function getLogin()
    {

        if (CRUDBooster::myId()) {
            return redirect(CRUDBooster::adminPath());
        }

        return view('crudbooster::login');
    }

    public function postLogin()
    {

        $validator = Validator::make(Request::all(), [
            'email' => 'required|email|exists:'.config('crudbooster.USER_TABLE'),
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            $message = $validator->errors()->all();

            return redirect()->back()->with(['message' => implode(', ', $message), 'message_type' => 'danger']);
        }

        if ($this->authentication->login(Request::input('email'), Request::input('password'))) {
            return redirect(CRUDBooster::adminPath());
        }

        return redirect()->route('getLogin')->with('message', cbLang('alert_password_wrong'));
    }

    public function getForgot()
    {
        if (CRUDBooster::myId()) {
            return redirect(CRUDBooster::adminPath());
        }

        return view('crudbooster::forgot');
    }

    public function postForgot()
    {
        $validator = Validator::make(Request::all(), [
            'email' => 'required|email|exists:'.config('crudbooster.USER_TABLE'),
        ]);

        if ($validator->fails()) {
            $message = $validator->errors()->all();

            return redirect()->back()->with(['message' => implode(', ', $message), 'message_type' => 'danger']);
        }

        $this->authentication->sendPasswordReset(Request::input('email'));

        return redirect()->route('getLogin')->with('message', cbLang("message_forgot_password"));
    }

    public function getLogout()
    {

        $this->authentication->logout();

        return redirect()->route('getLogin')->with('message', cbLang("message_after_logout"));
    }
}
