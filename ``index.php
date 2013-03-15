<?php
  $app_id = "253204234752974";
  $app_secret = "9b12db97de75600be77e679e5d7ef7a6"; 
  $my_url = "https://royalepromotions.ca/facebook/imageupload/";
     
  // known valid access token stored in a database 
  $access_token = "AAADmSbR3084BADx0ylYSxbvfEfBR9focIkTQdq4laJOn3EPgL3ZBu1HwZA1OSUESWeRaeS2GSzqfZBwPH1ZAPK5YJ1ZCAJDpWrYtNueZAnhgZDZD";

  $code = $_REQUEST["code"];
    
  // If we get a code, it means that we have re-authed the user 
  //and can get a valid access_token. 
  if (isset($code)) {
    $token_url="https://graph.facebook.com/oauth/access_token?client_id="
      . $app_id . "&redirect_uri=" . urlencode($my_url) 
      . "&client_secret=" . $app_secret 
      . "&code=" . $code . "&display=popup";
    $response = file_get_contents($token_url);
    $params = null;
    parse_str($response, $params);
    $access_token = $params['access_token'];
  }

        
  // Attempt to query the graph:
  $graph_url = "https://graph.facebook.com/me?"
    . "access_token=" . $access_token;
  $response = curl_get_file_contents($graph_url);
  $decoded_response = json_decode($response);
    
  //Check for errors 
  if ($decoded_response->error) {
  // check to see if this is an oAuth error:
    if ($decoded_response->error->type== "OAuthException") {
      // Retrieving a valid access token. 
      $dialog_url= "https://www.facebook.com/dialog/oauth?"
        . "client_id=" . $app_id 
        . "&redirect_uri=" . urlencode($my_url);
      echo("<script> top.location.href='" . $dialog_url 
      . "'</script>");
    }
    else {
      echo "other error has happened";
    }
  } 
  else {
  // success
    echo("success" . $decoded_response->name);
	echo "<br>";
    echo($access_token);
	
	$params = array(
		'access_token' => $access_token
	);
	 	
	$accounts = $facebook->api('/me/accounts', 'GET', $params);
	 
	echo "<h3>Accounts</h3>";
	print_r($accounts);
  }

  // note this wrapper function exists in order to circumvent PHP’s 
  //strict obeying of HTTP error codes.  In this case, Facebook 
  //returns error code 400 which PHP obeys and wipes out 
  //the response.
  function curl_get_file_contents($URL) {
    $c = curl_init();
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($c, CURLOPT_URL, $URL);
    $contents = curl_exec($c);
    $err  = curl_getinfo($c,CURLINFO_HTTP_CODE);
    curl_close($c);
    if ($contents) return $contents;
    else return FALSE;
  }
?>