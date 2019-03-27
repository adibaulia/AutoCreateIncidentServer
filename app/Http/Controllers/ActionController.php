<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use phpseclib\Net\SSH2;
use App\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redis;

class ActionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }
    var $data;

    public function run(Request $req)
    {
        
        Redis::set($req->name,$req->name);
        $redis= Redis::get($req->name);
        // $this->extractData($req);

        // $incident = new IncidentController('');
        

         return response()->json($redis);
    }

    public function test(Request $req)
    {
        // $c = $req->cmd==''?'dir':$req->cmd;

        // system($c, $retval);
        // echo $retval;

        $host = '192.168.56.23';
        $port = '22';
        $username = 'root';
        $password = '123';

        //echo $this->restartServer($host, $username, $password);
        echo "test";
        dd($req);

    }
    
    public function runScript()
    {

    }

    public function insertDbLog()
    {

    }



    public function extractData(Request $req)
    {
        /*
        data parameter that required this app from PRTG

        %sensorid = ID number of the sensor that triggered the event
        %sensor / %name = name of the sensor that triggered the event (including sensor type)
        %status	= old sensor status and current sensor status
        %deviceid = ID number of the device in which the event was triggered
        %device / %server = name of the device in which the event was triggered
        %down =	time the item was down
        %lastcheck = when was the sensor's last scan
        %lastdown =	when was the sensor down for the last time
        %downtime =	accumulated downtime
        %datetime =	event's date and time, in user's timezone
        %message / %lastmessage = which message did the sensor send the last time
        %probe = probe under which the event was triggered
        %probeid = ID number of the probe under which the event was triggered
        %serviceurl = Service URL configured for the device under which the event was triggered
        %priority / %prio =	sensor priority setting
        %history =	history of sensor events

        */

       //$comment, $org_id, $agent_id, $origin, $title, $desc, $impact, $urgency, $service_id, $servicesubcategory_id, $public_log, $event_id



        $data = $req;
        return $req;
    }

    public function check_condition()
    {

    }

    public function generateToken(Request $req)
    {
        if($req->name==null){
               return response()->json(['Message' => 'fill the name first']);
        } else

            $user = new User();
        $user->name = $req->name;
        $token= Str::random(30);
        $user->token = $token;
        $user->save();
        return response()->json(['token' => $token]);
    }

    //
}
