<?php
class Helpdesk_model extends CI_Model {
    function __construct() {
        parent::__construct();
    }

    private function _api_call($json_data)
	{
		$HELPDESK_API_URL = "http://192.168.3.139/webservices/rest.php";
		$HELPDESK_API_VERSION = "1.3";
		$HELPDESK_USER = "admin";
		$HELPDESK_PASSWORD = "qazplm";

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
		return json_decode($result,true);
	}

        //#########################################################################################

	public function get_tiket_status($ref)
	{
		$json_data = '{
				"operation": "core/get",
				"class": "UserRequest",
				"key": "SELECT UserRequest WHERE ref     \''.$ref.'\'",
				"output_fields": "operational_status, status"
 			      }';
		$resp = $this->_api_call($json_data);
		if(count($resp['objects'])>0)
		{
			$t = reset($resp['objects']);
			return $t['fields'];
		}
		else
		{
			return false;
		}
	}

	public function assign_tiket($ref_or_id,$team_id=5772,$agent_id=5275)
	{
		$key = str_replace('R-','',$ref_or_id);
		$intkey = (int) $key;
		$json_data = '{
			"operation": "core/apply_stimulus",
			"comment": "Autocreate Tiket PMAN",
			"class": "UserRequest",
			"key": '.$intkey.',
			"stimulus": "ev_assign",
			"output_fields": "status",
			"fields":
			{
			   "team_id": '.$team_id.',
			   "agent_id": '.$agent_id.'
			}
		 }';
			//  echo $json_data;exit;
		 $resp = $this->_api_call($json_data);
		 $resp_message = $resp['message'];
		//  print_r($resp);exit;
		 return $resp;
	}

    public function update_private_log($username,$server,$time,$ip,$ref,$tujuan='???')
    {
		$nama = ""; // $this->_get_person_by_username($username,$server);
		$log_message = 'Terdapat aktivitas login <br>ke server '.$server.' <br>oleh '.$nama.' <br>dari '.$ip.' pada '.$time.' <br>untuk: '.$tujuan;
		$json_data = '{
		   "operation": "core/update",
		   "comment":"root access logger integration",
		   "class": "Ticket",
		   "key": {"ref":"'.$ref.'"},
		   "output_fields": "ref",
		   "fields":{"private_log":"'.$log_message.'"}
		}';
		$resp = $this->_api_call($json_data);
		$resp_message = $resp['message'];

		$itopprd = $this->load->database('itopprd',true);
		$username = str_replace("'","",$username);
		$ins = array(
			'sysadmin_user' => $username,
			'sysadmin_remote_from_ip' => $ip,
			'sysadmin_access_to_ip' => $server,
			'ticket_ref' => $ref,
			'log_message' => $log_message,
			'resp_message' => json_encode($resp)
		);
		$itopprd->insert('rootlogger_log',$ins);
		return json_encode($resp);
    }

	public function get_person($nid)
	{
		$json_data = '{"operation": "core/get",
			"class": "Person",
			"key": "SELECT Person WHERE employee_number LIKE \''.$nid.'\'",
			"output_fields": "id,org_id"
			 }';
			//  echo $json_data;exit;
		 $resp = $this->_api_call($json_data);
		 $resp_message = $resp['message'];
		 return $resp;
	}

    public function new_userrequest($desc,$caller_id=1867,$org_id=2,$debug="no")
    {
		// error_reporting(E_ALL);
		$service_id = 23;
		$servicesubcategory_id = 582;
		$json_data = '{"operation":"core/create",
			"comment":"ess",
			"class":"UserRequest",
			"output_fields":"ref",
			"fields":{"org_id":"'.$org_id.'",
				"caller_id":"'.$caller_id.'",
				"origin":"portal",
				"title":"Pengajuan Perubahan Data",
				"description":'.$desc.',
				"impact":"3",
				"urgency":"3",
				"service_id":"'.$service_id.'","servicesubcategory_id":"'.$servicesubcategory_id.'"}
			}';
			if($debug=="debug")
			{
			 echo "<textarea>$json_data</textarea>";exit;
			}
		 $resp = $this->_api_call($json_data);
		 $resp_message = $resp['message'];
		 return $resp;
    }

	public function search_userrequest($nid,$service_id,$servicesubcategory_id)
	{
		$service_id = (int) $service_id;
		$servicesubcategory_id = (int) $servicesubcategory_id;
		$json_data = '{"operation": "core/get",
			"class": "UserRequest",
			"key": "SELECT UserRequest AS r JOIN Person AS p ON r.caller_id=p.id WHERE service_id='.$service_id.' AND servicesubcategory_id='.$servicesubcategory_id.' AND p.employee_number LIKE \''.$nid.'\'",
			"output_fields": "ref,title,start_date,resolution_date,close_date,status,team_id_friendlyname,agent_id_friendlyname"
			 }';
		 $resp = $this->_api_call($json_data);
		 $resp_message = $resp['message'];
		 return $resp;
	}

	public function upload_attachment($tiket_id,$org_id,$base64file,$filename,$mimetype)
	{
		$tiket_id = (int) $tiket_id;
		$org_id = (int) $org_id;
		$json_data = '{
			"operation" : "core/create",
			"comment" : "Automatic creation of attachment blah blah...",
			"class" : "Attachment",
			"output_fields" : "id, friendlyname",
			"fields" : {
			"item_class" : "UserRequest",
			"item_id" : '.$tiket_id.',
			"item_org_id" : '.$org_id.',
			"contents" : {
			"data" : "'.$base64file.'",
			"filename" : "'.$filename.'",
			"mimetype" : "'.$mimetype.'"
			}
			}
			}';
			//  echo "<textarea>$json_data</textarea>";
		 $resp = $this->_api_call($json_data);
		 $resp_message = $resp['message'];
		 return $resp;
	}

    public function search_userrequest_sampah(){
        $itop24prd_2018 = $this->load->database('itop24prd_2018',true);
        $sql = "select r.ref,r.description
                from view_userrequest r
                where service_name='Layanan SDM'
                and (replace(replace(r.description,'\r',''),'\n','') like '%<p>dengan ini melaporkan pengajuan data sebagai berikut,</p><br>'
                or replace(replace(r.description,'\r',''),'\n','') like '%<p>dengan ini melaporkan pengajuan data sebagai berikut,</p><p> </p>')
                and r.status in ('assigned', 'new')";
        return $itop24prd_2018->query($sql);

    }

    public function resolve_tiket_sampah($ref)
	{
		$key = str_replace('R-','',$ref);
		$intkey = (int) $key;
		$json_data = '{
			"operation": "core/update",
			"comment": "AUTORESOLVE TIKET PMAN",
			"class": "UserRequest",
			"key": '.$intkey.',
			"output_fields": "status",
			"fields":
			{
			   "status": "resolved"
			}
		 }';
			//  echo $json_data;exit;
		 $resp = $this->_api_call($json_data);
		 $resp_message = $resp['message'];
		//  print_r($resp);exit;
		 return $resp;
	}

}
