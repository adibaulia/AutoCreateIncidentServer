<?php

namespace App\Http\Controllers;

class IncidentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    var $data;
    public function __construct($prtg_data)
    {
        $this->middleware('auth');
        global $data;
        $data=$prtg_data;
    }

    public function show(){
        global $data;
        return $data;
    }

    public function testCreate()
    {
        global $data;
        echo "oke";
        $comment = "Changes Call from other Apps";
        $org_id = "2";
        $origin = "phone";
        $title = "Incident Ticket Server Test";
        $desc = "BLABLALABALBAL test";
        $impact = "2";
        $urgency = "1";
        $service_id = "1";
        $servicesubcategory_id = "304";
        $public_log = "This is public log";
        $event_id = "E123";
        $this->createIncident($comment, $org_id, $origin, $title, $desc, $impact, $urgency, $service_id, $servicesubcategory_id, $public_log, $event_id);
    }

    public function getDescData()
    {
        
    }


    public function createTicket($comment, $org_id, $agent_id, $origin, $title, $desc, $impact,
                                    $urgency, $service_id, $servicesubcategory_id, $public_log, $event_id)
    {
                    $json_data = '{
                    "operation": "core/create",
                    "comment": "'.$comment.'",
                    "class": "Incident",
                    "output_fields": "id, friendlyname",
                    "fields": {
                        "org_id": "'.$org_id.'",
                        "caller_id": "5417",
                        "agent_id": '.$agent_id.'
                        "origin": "'.$origin.'",
                        "title": "'.$title." ".$event_id.'",
                        "description": "'.$desc.'",
                        "impact": "'.$impact.'",
                        "urgency": "'.$urgency.'",
                        "service_id": "'.$service_id.'",
                        "servicesubcategory_id": "'.$servicesubcategory_id.'",
                        "public_log": "'.$public_log.'"
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
