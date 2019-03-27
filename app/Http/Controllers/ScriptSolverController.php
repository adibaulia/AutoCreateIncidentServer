<?php

namespace App\Http\Controllers;

class ScripSolverController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct($prtg_data)
    {
        $this->middleware('auth');
    }

    public function initialCheck()
    {

    }

    public function checkKB()
    {

    }

    public function restartServer($host, $username, $password)
    {
        $ssh = new SSH2($host);
        if (!$ssh->login($username, $password)) {
            exit('Login Failed');
        }else
             $ssh->exec('reboot');
        return "Success!";
    }


}