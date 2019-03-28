<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IncidentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    var $data;
    public function __construct(Request $input_data)
    {
        $this->middleware('auth');
        global $data;
        $data= $input_data;
    }

    public function show(){
        global $data;
        return $data;
    }

    public function create()
    {
        global $data;
        // /dd($data->service_id);
        $comment = "REPORTER BOT";
        $org_id = "2";
        $origin = "monitoring";
        $title = strtoupper($data->message." Ip Server = ".$data->host);
        $desc = $data->message." Ip Server = ".$data->host." ".$data->device.
                " ".$data->name." ".$data->status." ".$data->down." ".
                "This desc created by REPORTERBOT";
        $impact = "2";
        $urgency = "1";
        $service_id = $data->service_id;
        $servicesubcategory_id = $data->servicesubcategory_id;
        $public_log = "MENCOBA";
        $event_id = "I".$data->service_id.$data->host;
        $incident_category= "operational";
        $incident_subcategory= "server";
        $this->createTicket($comment, $org_id, $origin, $title, $desc, $impact, $urgency, $service_id, 
        $servicesubcategory_id, $public_log, $event_id, $incident_category, $incident_subcategory);
    }

    public function getDescData()
    {
        
    }


    public function createTicket($comment, $org_id, $origin, $title, $desc, $impact, $urgency, 
            $service_id, $servicesubcategory_id, $public_log, $event_id, $incident_category, $incident_subcategory)
    {
                    $json_data = '{
                    "operation": "core/create",
                    "comment": "'.$comment.'",
                    "class": "Incident",
                    "output_fields": "id, friendlyname",
                    "fields": {
                        "org_id": "'.$org_id.'",
                        "caller_id": "5417",
                        "origin": "'.$origin.'",
                        "title": "'.$title."     ".$event_id.'",
                        "description": "'.$desc.'",
                        "impact": "'.$impact.'",
                        "urgency": "'.$urgency.'",
                        "service_id": "'.$service_id.'",
                        "servicesubcategory_id": "'.$servicesubcategory_id.'",
                        "public_log": "'.$public_log. '"
                        }
                    }
                    ';
        return $this->callApi($json_data);
    }

    public function getTicket($event_id)
	{
		$json_data = '{
				"operation": "core/get",
				"class": "Incident",
				"key": "SELECT Incident WHERE title LIKE   \'%'.$event_id.'\'",
				"output_fields": "id, friendlyname, title, ref, service_id, servicesubcategory_id, description, agent_id, impact, urgency"
 			      }';
        $resp= $this->callApi($json_data);
        $t = reset($resp['objects']);
		return $t['fields'];

    }

    public function updateTicket($status = null, $agent_id=null, $comment) //not complete
    {
        $json_data = '{
				"operation": "core/update",
                "class": "Incident",
                "comment": "Change the status",
				"key": "SELECT Incident WHERE title LIKE   \'%E123\'",
                "output_fields": "id, friendlyname",
                "fields":{
                    "status":"closed"
                }
 			      }';
		return $this->callApi($json_data);
    }






    private function callApi($json_data)
	{
		$HELPDESK_API_URL = "http://helpdev24.ptpjb.com/webservices/rest.php";
		$HELPDESK_API_VERSION = "1.3";
		$HELPDESK_USER = "autocreateincidentbot";
		$HELPDESK_PASSWORD = "qwerty12345";
       // dd($json_data);
		$payload = array(
			'auth_user' => $HELPDESK_USER,
			'auth_pwd' => $HELPDESK_PASSWORD,
			'version' => $HELPDESK_API_VERSION,
			'json_data' => $json_data
		);

		$postdata = http_build_query($payload);
		$opts = array('http' =>
					array(
						'header' => "Content-Type: application/x-www-form-urlencoded\r\n".
									"Content-Length: ".strlen($postdata)."\r\n".
									"User-Agent:MyAgent/1.0\r\n",
						'method'  => 'POST',
						'content' => $postdata
					)
				);
		$context  = stream_context_create($opts);
		$result = file_get_contents($HELPDESK_API_URL, false, $context);
        //return $result;
        return json_decode($result,true);
	}

    //
}
