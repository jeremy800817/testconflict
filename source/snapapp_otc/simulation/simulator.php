<?php 
	
	require "../../vendor/autoload.php";
	
	Use \GuzzleHttp\Client;
	Use \GuzzleHttp\Request;

	/**
	* This is a class for all the logic of the simulator, such as generating html ,do API posting
	* and logging for each post request.
	*
	*
	*/

	class Simulator {

		/**
     	* This is constructor to initialize the api variable and handle the post request.
		*
     	*/

		public  function __construct($api_method) {
		    $this->api_method = $api_method;
		    $this->api_key = API_KEY;
		    $this->api_url = API_URL;
		    $this->api_response = '';

		    if('POST' === $_SERVER['REQUEST_METHOD']) $this->doPost();
		}

		/**
     	* This is method to generate select box for API list
		*
     	*/
		public function getApiLstSelectBox() {
			$output ="<select name='method'>";
			foreach($this->api_method as $key => $value) {
				$action = (isset($_GET['method']) && $key==$_GET['method']) ? 'selected' :'';
				$output .="<option value='{$key}' {$action}>{$value['name']}</option>";
			}
			$output .='</select>';
			$output .="	<button  name='action' value='submit'>Show Parameter</button>";
			return $output;
		}
		
		/**
     	* This is method to generate selected API parameter form list
		*
     	*/

		public function getParamHTML() {
			if(!isset($_GET['method'])) return "No Parameter Yet!";
			if(!isset($this->api_method[$_GET['method']])) return "No Parameter Yet!";
			
			$output ="<b>Url</b> : {$this->api_url} <p></p>";
			$params = $this->api_method[$_GET['method']];
			foreach($params as $key => $value) {
				//Name is not a parameter to submit
				if('name' == $key) continue; 
				$output .= '<b>' .$key .'</b> : ';

				//If is array, generate select box. Else will generate text box
				if(is_array($value)) {
					$output .="<select name='{$key}'>";
					foreach($value as $item) {
						$output .="<option value='{$item}'>{$item}</option>";
					}
					$output .="</select>";
				}else {
					$output .="<input type='text' name='{$key}' value='{$value}'>";
				}
				$output .="<p></p>";
			}
			$output .="<button  name='post' value='submit'>Submit POST</button>";
			return $output;
		}

		/**
     	* This is method to get history from history file.
		*
     	*/

		public function getHistory() {
			$history = @file_get_contents("simulation-log.txt")  ? : 'No history found in this moment';
			$history = str_replace("\n", "<br>", $history);
			return $history;
		}

		/**
     	* This is method to process the post request
		*
     	*/

		public function doPost() {
			$param_name_list = array_keys($this->api_method[$_GET['method']]);

			$post_data=[];
			foreach($param_name_list as $name) {
				//Name is not a parameter to submit
				if('name' == $name) continue; 
				$post_data[$name] = $_POST[$name];
			}



			$post_data['digest'] = $this->buildDigest($post_data);

			$client = new \GuzzleHttp\Client();
			$response = $client->post( $this->api_url, [
                'query' => $post_data
            ]);
            $returnData = $response->getBody() . '';

            $this->doLogging($post_data,$returnData);

            $this->api_response = $returnData;
		}


		public function getAPIResponse() {

			return $this->api_response ? : 'No API Response Yet!';

		}


		/**
     	* This is method to build digest with sha256
		*
     	*/

		private function buildDigest($params) {
	        $newParams = [];
		    foreach($params as $key => $value) {
		        $newParams[] = "$key=$value";
		    }


		    $text = join('&', $newParams) . "&key={$this->api_key}";
		    return hash('sha256', $text);
	    }

	    /**
     	* This is method to do logging for history purpose
		*
     	*/
	    private function doLogging($input='', $output ='') {
	    	$history = @file_get_contents("simulation-log.txt")  ? : '';

	    	$content = date('Y-m-d H:i:s');
	    	$content .="\n\n";
	    	$content .= "[Input] : \n";
	    	$content .= json_encode($input);
	    	$content .="\n\n";
	    	$content .="[Output] : \n";
	    	$content .= $output;
	    	$content .="\n\n";
	    	$content .= $history;
	    	file_put_contents("simulation-log.txt", $content);
	    }

	}


;?>