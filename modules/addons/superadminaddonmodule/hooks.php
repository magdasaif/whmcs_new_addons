<?php
use WHMCS\Database\Capsule;

/**
 * WHMCS SDK Sample Addon Module Hooks File
 *
 * Hooks allow you to tie into events that occur within the WHMCS application.
 *
 * This allows you to execute your own code in addition to, or sometimes even
 * instead of that which WHMCS executes by default.
 *
 * @see https://developers.whmcs.com/hooks/
 *
 * @copyright Copyright (c) WHMCS Limited 2017
 * @license http://www.whmcs.com/license/ WHMCS Eula
 */

// Require any libraries needed for the module to function.
// require_once __DIR__ . '/path/to/library/loader.php';
//
// Also, perform any initialization required by the service's library.

/**
 * Register a hook with WHMCS.
 *
 * This sample demonstrates triggering a service call when a change is made to
 * a client profile within WHMCS.
 *
 * For more information, please refer to https://developers.whmcs.com/hooks/
 *
 * add_hook(string $hookPointName, int $priority, string|array|Closure $function)
 */
add_hook('ClientEdit', 1, function(array $params) {
    try {
        // Call the service's function, using the values provided by WHMCS in
        // `$params`.
    } catch (Exception $e) {
        // Consider logging or reporting the error.
    }
});

//==================================================================================
//test new hooks
// add_hook('AcceptOrder', 1, function($vars) {
//     // Perform hook code here...
//     $orderid = $vars['orderid'];
//     Capsule::table('tblerrorlog')->insert(['severity'=> 'hook-test-addon','message'=>'order accepted'.$orderid,'details'=>'hook for accept order related to superadmin addon module done']) ;
// });


add_hook('AfterModuleCreate', 1, function($vars){
    
        $moduleParameters = $vars['params'];
        
        $serviceid=$moduleParameters['serviceid'];
        $serverid=$moduleParameters['serverid'];

        // Perform hook code here...
        
        
        Capsule::table('tblerrorlog')->insert(['severity'=> 'addon-test-params','message'=>'after module created','details'=>json_encode($moduleParameters)]) ;
        Capsule::table('tblerrorlog')->insert(['severity'=> 'addon-test-params','message'=>'after module created','details'=>'serverid from response is --> '.$serviceid]) ;
        
        $server_data= Capsule::table('tblservers')->where('id',$serverid)->first();
        Capsule::table('tblerrorlog')->insert(['severity'=> 'addon-test-params','message'=>'fetch module type','details'=>'server type is --> '.$server_data->type]) ;
        
        if($server_data->type=='cpanel'){
            //do curl connection with super admin to do extra details

            //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
            //using curl (post)
                // Create a new cURL resource
                $ch = curl_init();
            
                // Set the URL
                curl_setopt($ch, CURLOPT_URL, 'https://superadmin.saas.murabba.dev/handleDBAndMigrationAfterAfterWhmcsModuleCreate/'.$serviceid);
            
                // Set the request method to POST,default is GET
                curl_setopt($ch, CURLOPT_POST, true);


                // Set the request parameters
                $postData = [
                    'serviceid' => $serviceid,
                    // Include any other necessary parameters
                ];
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            
                // Set options for receiving the response
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
                // Execute the request
                $response = curl_exec($ch);
                    
                Capsule::table('tblerrorlog')->insert(['severity' => 'hook-http-client', 'details' => $response]);

                // Check for errors
                if ($response === false) {
                    // Request failed
                    $error = curl_error($ch);
                    Capsule::table('tblerrorlog')->insert(['severity' => 'hook-test-http-client-request-error', 'details' => $error]);
                } else {
                    // Request was successful
                    $responseData = json_decode($response, true);
                    Capsule::table('tblerrorlog')->insert(['severity' => 'hook-test-http-client-request-success', 'details' => $responseData]);
                }
            
                // Close the cURL resource
                curl_close($ch);
            //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
        }

    }
);
