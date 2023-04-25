<?php

require 'rest_client.php';
$rest = new CR\tools\rest("https://rest.cleverreach.com/v2");
$rest->throwExceptions = true;	//default
echo "<pre>";
 
/**
	- Basic Information - 
	
	GET - will retrieve data
	POST - for creating new data
	PUT - Update/replace existing data
	DELETE - delete existing data

	see: https://en.wikipedia.org/wiki/Representational_state_transfer for more information
*/

echo "### Login - will retrieve Token ###\n";
try {
	/*
	try to login and receive token!
	on error script execution will be cancled
	*/
	$token = $rest->post('/login', 
		array(
			"client_id"=>'161044',
			"login"=>'appdent',
			"password"=>'appdent'
		)
	);
        
       
	//no error, lets use the key
	$rest->setAuthMode("bearer", $token);
        
        
	// var_dump($token);

       // echo "<br/>";

} catch (\Exception $e){
	var_dump( (string) $e );
	var_dump($rest->error);
	exit;
}


echo "### Return basic client information ###\n";
// var_dump($rest->get("/clients"));



$order[] = [ "order_id" => "167",
            "product_id" => "SN9876543",
            "product" => "Batman - The Movie (DVD)",
            "price" => 9.99,
            "currency" => "EUR",
            "amount" => 1
            ];


$receiver = array(
		"email"			=> "test167@andresina.net",
		"registered"		=> time(),
		"activated"		=> time(),
		"source"		=> "ATL - robot",  
                'deactivated'           => 0,
                'attributes'            => ["category" => "1"],
		"global_attributes"	=> [
			"vorname" => "Andreas",
			"nachname" => "Fischer",
			"anrede" => 0
			],
		"orders" => $order

	);



$result = $rest->get("/groups/523414/receivers/test167@andresina.net");
    

if (isset($result->error)) {
        
 $result = $rest->post("/groups/523414/receivers", $receiver);
    
} else {
    
 $result = $rest->put("/groups/523414/receivers/test167@andresina.net", $receiver);  
}


print_r($result,0);

echo "</pre>";
