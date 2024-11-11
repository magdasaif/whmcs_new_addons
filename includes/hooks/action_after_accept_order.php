<?php
//this file will be located in whmcs includes/hooks folders
use WHMCS\Database\Capsule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

if (!defined('WHMCS'))
die('You cannot access this file directly.');

//=================================================================
add_hook('AcceptOrder', 1, function($vars) {
    // handle actions after accept order done
    $orderid = $vars['orderid'];
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    Capsule::table('tblerrorlog')->insert(['severity'=> 'hook-test','message'=>'order accepted'.$orderid]) ;
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
        //first way to handle dbs
        // //using curl (post)
        //     // Create a new cURL resource
        //     $ch = curl_init();
        
        //     // Set the URL
        //     curl_setopt($ch, CURLOPT_URL, 'https://superadmin.saas.murabba.dev/handleDBAndMigrationAfterAcceptWhmcsOrder/'.$orderid);
        
        //     // Set the request method to POST,default is GET
        //     curl_setopt($ch, CURLOPT_POST, true);


        //     // Set the request parameters
        //     $postData = [
        //         'orderId' => $orderId,
        //         // Include any other necessary parameters
        //     ];
        //     // curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        
        //     // Set options for receiving the response
        //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        //     // Execute the request
        //     $response = curl_exec($ch);
                
        //     Capsule::table('tblerrorlog')->insert(['severity' => 'hook-http-client', 'details' => $response]);

        //     // Check for errors
        //     if ($response === false) {
        //         // Request failed
        //         $error = curl_error($ch);
        //         Capsule::table('tblerrorlog')->insert(['severity' => 'hook-test-http-client-request-error', 'details' => $error]);
        //     } else {
        //         // Request was successful
        //         $responseData = json_decode($response, true);
        //         Capsule::table('tblerrorlog')->insert(['severity' => 'hook-test-http-client-request-success', 'details' => $responseData]);
        //     }
        
        //     // Close the cURL resource
        //     curl_close($ch);
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
        //secand way to handle dbs
        //here we will use external trait or class
        //handleTenantDB('order_id',$orderid);
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
        //third way to handle dbs
        //=======================================================================
        //here we will use external repo to handle pipeline using .yml files
        //so , we need to handle tenant deployment data
        $deploymeny_data= handleDeploymentVariables($orderid);
        $file_content   = $deploymeny_data['content'];
        $username       = $deploymeny_data['username'];
        $admin_content  = $deploymeny_data['admin_content'];
        //=======================================================================
        //then , put this data in variable.yml file 
        $file_data      = handleVaraibleFile($file_content,$username);
        //=======================================================================
        $admins_content = handleAdminData($admin_content);//'hello admins';
        handleAdminSeederFile($admins_content,$username);
        //=======================================================================
        //finally , we need to push this file to deployment repo
        // handlePushToRepo($file_data['user_branch'],$file_data['file']);
        handlePushToRepo($file_data['user_branch']);
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
});
//=================================================================
function projectServiceDetails($order_id){
    $host_data      = Capsule::table('tblhosting')->where('orderid',$order_id)->first();
    $package_id     = $host_data->packageid;//product_id

    $package_data   = Capsule::table('tblproducts')->where('id',$package_id)->first();
    $project_id     = $package_data->gid;//group_id

    $project_data    = Capsule::table('tblproductgroups')->where('id',$project_id)->first();
   
    //handle db from project_services table for selected project
    $project_dbs = Capsule::table('project_services')->where('project_id',$project_id)->pluck('db_path','name')->toArray();   //store as array

    return ['project_data'=>$project_data,'project_dbs'=>$project_dbs,'package_data'=>$package_data];
}
//=================================================================
function fetchDeploymentType($package_data){
    $deployment_project_type='product';
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    //check if found deployment type for package in package_extra_details table , take it 
    //not found , fetch its value depend on server type with this product
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    $package_extra_details=Capsule::table('package_extra_details')->where('package_id',$package_data->id)->first();
    if(isset($package_extra_details)){
        $deployment_project_type=$package_extra_details->deployment_type;
    }else{
        //depend on product server type , determine deployment type 
        $product_server_type=$package_data->servertype;
        if($product_server_type=='cpanel'){
            $deployment_project_type='project';
        }elseif($product_server_type=='superadminmodule'){
            $deployment_project_type='product';
        }
    }
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    return $deployment_project_type;
}
//==========================================================================
function handleTenantDB($orderid){
    //**************************************************************************** */
    //we need to read tenant project services and its migration paths from tenancy_db_details
    $host_data      = Capsule::table('tblhosting')->where('orderid',$orderid)->first();
    $order_details  = (object)fetchServerDetails($host_data);

    $project_data           = projectServiceDetails($orderid)['project_data'];
    $project_service_dbs    = projectServiceDetails($orderid)['project_dbs'];

    foreach($project_service_dbs as $service_name=>$db_path){
        
        //*************************************************************************************************************** */
        //handle db data for each tenant
        $db_name=str_replace('-', '_', $project_data->name).'_'.str_replace('-', '_', $host_data->username).'_'.str_replace(' ', '_', $service_name);
        $db_username=generateCpanelUsername();
        $db_password=generateStrongPassword();

        $dbs[]=['db'=>$db_name,'db_username'=>$db_username,'db_password'=>$db_password];
        //*************************************************************************************************************** */
        // $dbs[]=$db_name;

        $details_for_db = [
            'order_details' =>  $order_details,
            'db_name'       =>  $db_name,
            'service_path'  =>  $db_path,
            'project_name'  =>  $project_data->name,
        ];

        //==================================================================
        // $job = (new HandleDb($details_for_db));
        // dispatch($job);
        //==================================================================
        // dispatchJob('HandleDb',$details_for_db);
        //==================================================================
        createDBAndConnection($db_name,$db_path,$project_data->name,$order_details);
        //==================================================================
    }
 
    //****************************************************************************
    //here we need to store tenant dbs with tenant
    connectTenantWithCreatedDbs($host_data->id,$dbs);
    //****************************************************************************
}
//==========================================================================
function connectTenantWithCreatedDbs($tenant_id,$dbs)  {
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    // here we need to store tenant dbs with tenant
    Capsule::table('tenant_extra_details')->where('tenant_id',$tenant_id)->update([
        'tenancy_db_names'          => json_encode($dbs),
    ]) ;
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    return 'done';
}    
//==========================================================================
function dispatchJob($job,$data){
    $job = (new $job($data));
    dispatch($job);
}
//==========================================================================
function fetchServerDetails($host_data){
    $host_username  = $host_data->username;
    $host_password  = $host_data->password;
    $host_domain    = $host_data->domain;
    $server_id      = $host_data->server;

    $server_data        = Capsule::table('tblservers')->where('id',$server_id)->first();
    $server_ip          = $server_data->name;       //"66.7.221.85",
    $server_name        = $server_data->name;       //"host.murabba.dev",
    $server_hostname    = $server_data->hostname;   //"host.murabba.dev",
    $server_assignedips = $server_data->assignedips;//"66.7.221.85",   
    
    $data=[
        'server_ip'         => $server_ip,
        'server_name'       => $server_name,
        'server_hostname'   => $server_hostname,
        'server_assignedips'=> $server_assignedips,
        'host_username'     => $host_username,
        'host_password'     => $host_password,
        'host_domain'       => $host_domain,
    ];

    return $data;
}
//==========================================================================
//this function used with HandleDb job
function createDBAndConnection($db_name,$db_path,$project_name,$order_details) {   
    // return $db_path;
    // return DB::getDefaultConnection();   

    //===============================================
    //change connection 
    // Config::set('database.connections.mysql.database','test18-2');
    // DB::statement("GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost';");
    // DB::statement("CREATE DATABASE IF NOT EXISTS `test_database`;");
    Log::info($order_details);

    Log::info(Config::get('database.connections.tenant'));

    $this->setConfigConnectionValue('tenant','host',($order_details->server_ip)??'66.7.221.85',1);
    Log::info(Config::get('database.connections.tenant'));

    //create db if not found
    DB::connection('tenant')->statement("CREATE DATABASE IF NOT EXISTS ".$db_name);
    
    //we must drop any db connection before set new values
    DB::purge('tenant');
    //===============================================
    //set created db as tenant db connection
    $this->setConfigConnectionValue('tenant','database',$db_name,0);
    // $this->setConfigConnectionValue('tenant','host',$order_details->server_ip,0);
    // $this->setConfigConnectionValue('tenant','username',$order_details->host_username,0);
    // $this->setConfigConnectionValue('tenant','password',$order_details->host_password,0);
    //===============================================
    //reconnect tenant connection,set it as defult
    DB::connection('tenant');
    DB::setDefaultConnection('tenant');
    //===============================================
    /*
        Isolating Migration Execution
        If you are deploying your application across multiple servers and running migrations as part of your deployment process, you likely do not want two servers attempting to migrate the database at the same time. To avoid this, you may use the isolated option when invoking the migrate command.

        When the isolated option is provided, Laravel will acquire an atomic lock using your application's cache driver before attempting to run your migrations. All other attempts to run the migrate command while that lock is held will not execute; however, the command will still exit with a successful exit status code:

        php artisan migrate --isolated


        To utilize this feature, your application must be using the memcached, redis, dynamodb, database, file, or array cache driver as your application's default cache driver. In addition, all servers must be communicating with the same central cache server.
    */

    // execute migrate command with service db path
    Artisan::call('migrate',
    [
        '--path'       =>  $db_path, //'database/migrations/visitor';
        '--database'   => 'tenant',  //connection name
        '--force'      =>  true,    //علشان ينفذ الميجريت بدون ما يسأل 
    ]);
    //===============================================
    //$seeder_path='Database\\Seeders\\eradonline\\DatabaseSeeder';
    // $seeder_path='Database\\Seeders\\'.$project_name.'\\DatabaseSeeder';
    // Artisan::call('db:seed',
    // [
    //     '--class'       =>  $seeder_path,
    // ]);
    //===============================================
    Log::info('------------------------ done -----------------------');

    //reconnect mysql connection,set it as defult
    DB::connection('mysql');
    DB::setDefaultConnection('mysql');

    return $db_name;
}
//==========================================================================
function generateStrongPassword($length = 9, $add_dashes = false, $available_sets = 'luds'){
    $sets = array();
    if(strpos($available_sets, 'l') !== false)
        $sets[] = 'abcdefghjkmnpqrstuvwxyz';
    if(strpos($available_sets, 'u') !== false)
        $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
    if(strpos($available_sets, 'd') !== false)
        $sets[] = '23456789';
    if(strpos($available_sets, 's') !== false)
        $sets[] = '!@#$%&*?';

    $all = '';
    $password = '';
    foreach($sets as $set)
    {
        $password .= $set[array_rand(str_split($set))];
        $all .= $set;
    }

    $all = str_split($all);
    for($i = 0; $i < $length - count($sets); $i++)
        $password .= $all[array_rand($all)];

    $password = str_shuffle($password);

    if(!$add_dashes)
        return $password;

    $dash_len = floor(sqrt($length));
    $dash_str = '';
    while(strlen($password) > $dash_len)
    {
        $dash_str .= substr($password, 0, $dash_len) . '-';
        $password = substr($password, $dash_len);
    }
    $dash_str .= $password;
    return $dash_str;
}
//==========================================================================
function generateCpanelUsername(){
    /*
        Basic restrictions
            cPanel & WHM applies the following rules when you create or modify a cPanel or WHM username:

            Usernames may only use lowercase letters (a–z) and digits (0–9).

            Usernames cannot contain more than 16 characters.
            Usernames cannot begin with a digit (0–9) or the string test.
            Usernames cannot end with the string assword.
        Special cases
            You cannot create a username with the hyphen character (-), but you can change an account’s name to use a hyphen when you transfer that account to another system.
            To allow usernames over eight characters in length, set the LONGUSERS: 1 environment variable in the /var/cpanel/whm/nvdata/root.yaml file.
            If you plan to use MySQL® or PostgreSQL® as a database engine, the first eight characters must be unique on the system.
    */
    
    $validCharacters = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $username = '';
    $maxLength = 16;

    do {
        // Generate a random username
        $username = substr(str_shuffle($validCharacters), 0, $maxLength);
        
        // Check if the username starts with a digit or the string "test"
        $startsWithDigit = preg_match('/^[0-9]/', $username);
        $startsWithTest = (strtolower(substr($username, 0, 4)) === 'test');

        // Check if the username ends with the string "assword"
        $endsWithPassword = (strtolower(substr($username, -7)) === 'assword');

        // Check if the username contains any special characters
        $containsSpecialChars = preg_match('/[^a-z0-9]/', $username);

        // Repeat the loop if any of the validations fail
    } while ($startsWithDigit || $startsWithTest || $endsWithPassword || $containsSpecialChars);

    return $username;
}
//==========================================================================
function fetchprojectDetails($project_id){
    return $project_data    = Capsule::table('tblproductgroups')->where('id',$project_id)->first();
}
//==========================================================================
function fetchprojectExtraDetails($project_id){
    return $project_data    = Capsule::table('project_extra_details')->where('id',$project_id)->first();
}
//==========================================================================
function fetchHostData($order_id){
    return $host_data    = Capsule::table('tblhosting')->where('orderid',$order_id)->first();
}
//==========================================================================
function fetchProjectId($host_data){
    $package_id     = $host_data->packageid;//product_id
    
    $package_data   = Capsule::table('tblproducts')->where('id',$package_id)->first();
    $project_id     = $package_data->gid;//group_id

    return $project_id;
}
//==========================================================================
function tenantDbs($project_service_dbs,$app_name,$host_data){
    $dbs=[];
    foreach($project_service_dbs as $service_name=>$db_path){
        //*************************************************************************************************************** */
        //handle db data for each tenant
        // $db_name=str_replace('-', '_', $app_name).'_'.str_replace('-', '_', $host_data->username).'_'.str_replace(' ', '_', $service_name).'db';
        // $db_username=str_replace('-', '_', $app_name).'_'.str_replace('-', '_', $host_data->username).'_'.str_replace(' ', '_', $service_name).'usr';
        $db_name=str_replace('-', '_', $host_data->username).'_'.str_replace('-', '_', $app_name).'_'.str_replace(' ', '_', $service_name).'db';
        $db_username=str_replace('-', '_', $host_data->username).'usr';
        // $db_username=generateCpanelUsername();
        $db_password=generateStrongPassword();

        $dbs[]=['db'=>$db_name,'db_username'=>$db_username,'db_password'=>$db_password];
        //*************************************************************************************************************** */
        // $dbs[]=$db_name;
    }

    //****************************************************************************
    //here we need to store tenant dbs with tenant
    connectTenantWithCreatedDbs($host_data->id,$dbs);
    //****************************************************************************
    return $dbs;
}
//==========================================================================
function saveTenantDeploymentData($tenant_id,$data,$package_id,$project_id,$dashboard_url)  {
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    // here we need to store tenant dbs with tenant
    Capsule::table('tenant_extra_details')->where('tenant_id',$tenant_id)->update([
        'deployment_details'    => $data,
        'deploy_count'          => 1
    ]) ;
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    // //old code, when project have only one usernamr,password
    // // here we need to store tenant dashboard url, password,username as custom feild if not set 
    // //check if found value for custome fieldname for dashboaed data or no
    // handleTenantCustomFeilds('Dashboard Url',$package_id,$tenant_id,$dashboard_url);
    // handleTenantCustomFeilds('Dashboard Username',$package_id,$tenant_id,retrieveDashboardData($tenant_id,$project_id,$package_id)['dashboard_username']);
    // handleTenantCustomFeilds('Dashboard Password',$package_id,$tenant_id,retrieveDashboardData($tenant_id,$project_id,$package_id)['dashboard_password']);
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    return 'done';
}
//==========================================================================
function handleTenantCustomFeilds($field_name,$package_id,$tenant_id,$value,$showorder=''){
    // $return_data=[];
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    $check_fieldname=Capsule::table('tblcustomfields')->where(['fieldname'=>$field_name,'type'=>'product','relid'=>$package_id])->first();
    if(!isset($check_fieldname)){
        //check found value or no
        $id=Capsule::table('tblcustomfields')->insertGetId(['fieldname'=>$field_name,'type'=>'product','relid'=>$package_id,'fieldtype'=>'text','showorder'=>$showorder]);
    }else{
        $id=$check_fieldname->id;
    }
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    $fieldvalue=Capsule::table('tblcustomfieldsvalues')->where(['fieldid' => $id,'relid' => $tenant_id])->where('value','!=','')->first();

    if(!isset($fieldvalue)){
        $fieldvalue=Capsule::table('tblcustomfieldsvalues')->updateOrInsert(
            [
                'fieldid'=> $id,
                'relid'  => $tenant_id,
            ],
            [
                'fieldid'=> $id,
                'relid'  => $tenant_id,
                'value'  => $value
            ]
        );
        $custom_value=$value;;
    }else{
        $custom_value=$fieldvalue->value;
    }
    //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: 
    // $return_data[$field_name]=$custom_value;
    // return $return_data;
    //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: 
    return [$field_name => $custom_value];
}
//==========================================================================
function retrieveDashboardData($tenant_id,$project_id,$package_id){

    $custom_username=retrieveCustomFeildValue('dashboard Username',$package_id,$tenant_id);
    $custom_password=retrieveCustomFeildValue('dashboard Password',$package_id,$tenant_id);
    
    //here fetch username,password from project extra details table if no custom feild set for them
    $username = ($custom_username!=null)?$custom_username:fetchprojectExtraDetails($project_id)->dashboard_username;
    $password = ($custom_password!=null)?$custom_password:fetchprojectExtraDetails($project_id)->dashboard_password;

    return ['dashboard_username'=>$username,'dashboard_password'=>$password];
}
//==========================================================================
function retrieveCustomFeildValue($feild_name,$package_id,$tenant_id){
    $value=false;
    //check if found value for custome fieldname for dashboaed data or no
    $fieldname_detail_for_username=Capsule::table('tblcustomfields')->where(['fieldname'=>$feild_name,'type'=>'product','relid'=>$package_id])->first();
    if(isset($fieldname_detail_for_username)){
        //check found value or no
        $fieldvalue_detail_for_username=Capsule::table('tblcustomfieldsvalues')->where(['fieldid'=>$fieldname_detail_for_username->id,'relid'=>$tenant_id])->first();
        if(isset($fieldvalue_detail_for_username)){
            $value=$fieldvalue_detail_for_username->value;
        }
    }
    return $value;
}
//==========================================================================
 function handleDeploymentVariables($order_id){
    //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    //start to fetch correct data
    $host_data              = fetchHostData($order_id);
    $server_details         = fetchServerDetails($host_data);
    $project_id             = fetchProjectId($host_data);
    $project_details        = fetchprojectDetails($project_id);
    $project_extra_details  = fetchprojectExtraDetails($project_id);
    $project_service_dbs    = projectServiceDetails($order_id)['project_dbs'];
    $app_name               = $project_details->name;//this will be project name
    $user                   = $server_details['host_username'];
    $dbs                    = tenantDbs($project_service_dbs,$app_name,$host_data);
    $package_data           = projectServiceDetails($order_id)['package_data'];
    $deploy_type            = fetchDeploymentType($package_data);

    $deploy_password        = HostDetails($host_data->id)['password'];//fetch from local api to fetch password without encryption

    //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

    $base_dir               = '/home/'.$user.'/public_html/'.$app_name;//$project_extra_details->base_dir;//this will be project root directory //TODO , will added in table, not added yet
    $livelink_dir           = 'backend';
    $mail_domain            = 'murabba.dev';
    $deploy_domain          = $livelink_dir.'.'.$server_details['host_domain']; // backend.${APPNAME}.${MAINDOMAIN} // $CI_PROJECT_PATH_SLUG.$CI_COMMIT_REF_SLUG.$CI_ENVIRONMENT_SLUG
    // $deploy_path         = '/var/www/'.$deploy_domain;
    $deploy_path            = '/home/'.$user.'/public_html/'.$app_name.'/backendreleases';
    $deploy_user            = $user;
    $deploy_group           = $user;
    //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

    $data ='USER='.$user.'\r\n';               //host/cpanel/tenant username  
    $data.='APPNAME='.$app_name.'\r\n';        // project name
    $data.='BASE_DIR='.$base_dir.'\r\n';       // /home/${USER}/public_html/${APPNAME}
    $data.='COMPOSER=/opt/cpanel/composer/bin/composer\r\n';// /opt/cpanel/composer/bin/composer
    //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
  
    // $dashboard_details=retrieveDashboardData($host_data->id,$project_id,$package_data->id);
    // $dashboard_username=$dashboard_details['dashboard_username'];
    // $dashboard_password=$dashboard_details['dashboard_password'];
    // // $dashboard_url='www.'.$deploy_domain.(($project_extra_details->dashboard_path)??'/dashboard');
    $dashboard_url='www.'.$server_details['host_domain'].(($project_extra_details->dashboard_path)??'/dashboard');
    //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    
    $data.='\r\n############################ dashboard details ######################################\r\n';
    $data.='tenant_id='.$host_data->id.'\r\n';       
    $data.='project_id='.$project_id.'\r\n';       
    $data.='package_id='.$package_data->id.'\r\n';       
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //TODO ,we need to handle object for each role
    //TODO , retrieve all agent details for tenant project
    $dashboard_agent_data   = fetchProjectAgentData($host_data->id,$project_id,$package_data->id,$server_details['host_domain'],true);
    foreach($dashboard_agent_data as $agent_data){
        foreach($agent_data as $key=>$value){
            // echo 'key : '. $key.' <br>';
            // echo 'value : '. $value.' <br>';
            // echo '-------------------------<br>';
            $data.= $key.'='.$value.'\r\n';  
        }
    }
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    $data.='\r\n############################ database count ######################################\r\n';
    $data.='DBCOUNT='.count($dbs).'\r\n';
    //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    $i=0;
    foreach ($dbs as $db) {
       //$db_name=str_replace('-', '_', $project_data->name).'_'.str_replace('-', '_', $host_data->username).'_'.str_replace(' ', '_', $service_name);
        
        $db_db          = $db['db'];           //$db.'db';
        $db_user        = $db['db_username'];  //$db.'usr';
        $db_password    = $db['db_password'];

        $db_variables ='';
        $db_variables.='\r\n######################### database number '.$i.' #################################\r\n';
        $db_variables.='DBPORT'.$i.'=3306\r\n';// 3306
        $db_variables.='DBHOST'.$i.'='.(($server_details['server_ip'])??'66.7.221.85').'\r\n';// localhost
        $db_variables.='DBDATABASE'.$i.'='.$db_db.'\r\n';// ${USER}_${APPNAME}db  //${app_name}_${user}_${service_name}
        $db_variables.='DBUSER'.$i.'='.$db_user.'\r\n';// ${USER}_${APPNAME}usr
        $db_variables.='DBPASSWORD'.$i.'='.$db_password.'\r\n';// S191tu^PPARW
        $db_variables.='\r\n#################################################################################\r\n';

        $data.=$db_variables;
        $i++;
    }

    // $data.='DBDATABASE='.$app_name.'\r\n';// ${USER}_${APPNAME}db
    // $data.='DBHOST='.$server_details['server_ip'].'\r\n';// localhost
    // $data.='DBPASSWORD='.$app_name.'\r\n';// S191tu^PPARW
    // $data.='DBPORT='.$app_name.'\r\n';// 3306
    // $data.='DBUSER='.$app_name.'\r\n';// ${USER}_${APPNAME}usr
    //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    
    // $data.='DEPLOY_DOMAIN='.$server_details['host_domain'].'\r\n';// backend.${APPNAME}.${MAINDOMAIN}
    $data.='ENV_FILE='.$base_dir.'/.env\r\n';               // ${BASE_DIR}/.env
    $data.='HTACCESS_FILE='.$base_dir.'/htaccess.txt\r\n';  // ${BASE_DIR}/htaccess.txt
    $data.='KEEP=1\r\n';                                    // 1
    $data.='LIVELINK_DIR='.$livelink_dir.'\r\n';            // backend
    $data.='LIVE_PATH='.$base_dir.'/'.$livelink_dir.'\r\n'; // ${BASE_DIR}/${LIVELINK_DIR}
    $data.='\r\n#################################################################################\r\n';


    $data.='MAILENCRYPTION=';(($project_extra_details)?$data.=$project_extra_details->mail_encryption:$data.='tls').'\r\n';// tls
    $data.='MAILFROMADDRESS=';(($project_extra_details)?$data.=$project_extra_details->mail_from_address:$data.=$app_name.'@'.$mail_domain).'\r\n';
    $data.='MAILFROMNAME=';(($project_extra_details)?$data.=$project_extra_details->mail_from_name:$data.=$app_name).'\r\n';
    $data.='MAILHOST=';(($project_extra_details)?$data.=$project_extra_details->mail_host:$data.='mail.'.$mail_domain).'\r\n';
    $data.='MAILMAILER=';(($project_extra_details)?$data.=$project_extra_details->mail_driver:$data.='smtp').'\r\n';
    $data.='MAILPASSWORD=';(($project_extra_details)?$data.=$project_extra_details->mail_password:$data).'\r\n';
    $data.='MAILPORT=';(($project_extra_details)?$data.=$project_extra_details->mail_port:$data.='587').'\r\n';
    $data.='MAILUSERNAME=';(($project_extra_details)?$data.=$project_extra_details->mail_username:$data.=$app_name.'@'.$mail_domain).'\r\n';
    $data.='\r\n#################################################################################\r\n';
    
    $data.='NPM_VERSION=';(($project_extra_details)?$data.=$project_extra_details->npm_version:$data.='18').'\r\n';
    $data.='PHPVER=';(($project_extra_details)?$data.=$project_extra_details->php_version:$data.='/usr/local/bin/ea-php82').'\r\n';
    $data.='RELEASES_DIR='.$base_dir.'/backendreleases\r\n';// ${BASE_DIR}/backendreleases
    $data.='STORAGE='.$base_dir.'/storage\r\n';// ${BASE_DIR}/storage
    $data.='SWAGGERHOST=';(($project_extra_details)?$data.=$project_extra_details->swagger_link:$data.='https://'.$livelink_dir.$app_name.$mail_domain.'api').'\r\n';
    $data.='NORMALUSER='.$user.'\r\n';// ${USER}
    $data.='OWNER='.$user.'\r\n';// ${USER}
    $data.='GROUP='.$user.'\r\n';// ${USER}
    $data.='\r\n#################################################################################\r\n';

    //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    // $data.='GIT_USER=$CI_COMMIT_AUTHOR_NAME\r\n';// $CI_COMMIT_AUTHOR_NAME                         //TODO ?????????
    // $data.='GIT_EMAIL=$CI_COMMIT_AUTHOR_EMAIL\r\n';// $CI_COMMIT_AUTHOR_EMAIL                      //TODO ?????????
    // $data.='COMMIT_TIME=$CI_COMMIT_TIMESTAMP\r\n';// $CI_COMMIT_TIMESTAMP                          //TODO ?????????
    //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    $data.='DEPLOYMENT_TYPE='.$deploy_type.'\r\n';       // project or product
    $data.='DEPLOY_HOST='.(($server_details['server_ip'])??'66.7.221.85').'\r\n';// 66.7.221.85
    $data.='DEPLOY_DOMAIN='.$deploy_domain.'\r\n';
    $data.='DEPLOY_PATH='.$deploy_path.'\r\n';// /var/www/$DEPLOY_DOMAIN                           //TODO ???????
    $data.='DEPLOY_USER='.$deploy_user.'\r\n';// deploy                                            //TODO ???????
    $data.='DEPLOY_GROUP='.$deploy_group.'\r\n';// deploy
    $data.='DEPLOY_PASSWORD='.$deploy_password.'\r\n';// host password (cpanel password)
    $data.='DEPLOY_MODE=0755\r\n';// 0755
    $data.='DEPLOY_OWNER='.$deploy_user.':'.$deploy_group.'\r\n';// $DEPLOY_USER:$DEPLOY_GROUP
    $data.='DEPLOY_RELEASE='.$deploy_path.'/releases/$CI_COMMIT_SHORT_SHA\r\n';// $DEPLOY_PATH/releases/$CI_COMMIT_SHORT_SHA //TODO ?????????
    $data.='DEPLOY_CURRENT='.$deploy_path.'/current\r\n';// $DEPLOY_PATH/current
    $data.='DEPLOY_SHARED='.$deploy_path.'/shared\r\n';// $DEPLOY_PATH/shared
    $data.='DEPLOY_STORAGE='.$deploy_path.'/storage\r\n';// $DEPLOY_PATH/storage
    $data.='DEPLOY_PUBLIC='.$deploy_path.'/public\r\n';// $DEPLOY_PATH/public
    $data.='DEPLOY_LOGS='.$deploy_path.'/logs\r\n';// $DEPLOY_PATH/logs
    $data.='DEPLOY_ENV='.$deploy_path.'/.env\r\n';// $DEPLOY_PATH/.env
    $data.='DEPLOY_NPM='.$deploy_path.'/node_modules\r\n';// $DEPLOY_PATH/node_modules
    $data.='DEPLOY_COMPOSER='.$deploy_path.'/vendor\r\n';// $DEPLOY_PATH/vendor
    $data.='DEPLOY_PHP='.$deploy_path.'/php\r\n';// $DEPLOY_PATH/php
    $data.='DEPLOY_PHP_VERSION=8.2\r\n';// 8.2
    $data.='\r\n#################################################################################\r\n';

    $data.='FIRST_RUN=false\r\n';//false
    $data.='\r\n#################################################################################\r\n';

    //****************************************************************************
    //here we need to store deploy data with tenant
    saveTenantDeploymentData($host_data->id,$data,$package_data->id,$project_id,$dashboard_url);
    //****************************************************************************

    // return $data;
    return ['content'=>$data,'username'=>$user,'admin_content'=>$dashboard_agent_data];
}
##============================== check Exist Directoty ==================
function checkExistDirectoty($directory) {
    // if (!File::exists($directory))
    if (!file_exists($directory))
    {
        try {
            #if not exist create new folder for all model it  and allow permission 
            // File::makeDirectory($directory, 0755, true, true);
            mkdir($directory, 0777, true);

        } catch (\Exception $e) {
            Capsule::table('tblerrorlog')->insert(['severity' => 'file error', 'message'=>"Could not create directory:". $directory,'details' => $e->getMessage()]);

            // $this->error("Could not create directory: $directory");
            // $this->info("Please check that the parent directory exists and is writable.");
            return;
        }
    }
}    
##============================== check Exist File =======================
function checkFileExist($file_path) {
    try {
        #if not exist create new folder for all model it  and allow permission 
        if (!file_exists($file_path)) {
            touch($file_path);
        }
    } catch (\Exception $e) {
        Capsule::table('tblerrorlog')->insert(['severity' => 'file error', 'message'=>"Could not create file:". $file_path,'details' => $e->getMessage()]);

        // $this->error("Could not create file: $file_path");
        // $this->info("Please check that the parent directory exists and is writable.");
        return;
    }
    
}
##=========================== Execte git commands =======================
function executeGitCommand($command){
    $output = null;
    $exitCode = null;

    try {
        // Execute the Git command
        exec($command . ' 2>&1', $output, $exitCode);

        // Log the output for debugging
        Capsule::table('tblerrorlog')->insert([
            'severity' => 'Git command output',
            'message'  => $command,
            // 'details'  => implode(PHP_EOL, $output)
            'details'  => $exitCode .'-'. json_encode($output)

        ]);

        // Check if the command encountered an error
        if ($exitCode !== 0) {
            var_dump($output);
            throw new \Exception("Git command failed with exit code: $exitCode and output: $output");
        }

        // Return the output as an array of lines
        // return $output;
        var_dump($output);
    } catch (\Exception $e) {
        // Log the exception
        Capsule::table('tblerrorlog')->insert([
            'severity' => 'Git command error',
            'message' => $command,
            'details' => $e->getMessage()
        ]);

        // Handle the error as needed
        // For example, you can throw a new exception, log an error, or return a specific value
        throw $e;
    }
}
//==========================================================================
function handleAdminData($dashboard_agent_data){
    /*
        Array ( 
            [0] => Array ( [admin_dashboard_url] => www./dashboard ) 
            [1] => Array ( [admin_dashboard_username] => admin@eradco.com ) 
            [2] => Array ( [admin_dashboard_password] => R62aAyUp7q4cPxrX ) 
            [3] => Array ( [editor_dashboard_url] => www./dashboard ) 
            [4] => Array ( [editor_dashboard_username] => editor@eradco.com ) 
            [5] => Array ( [editor_dashboard_password] => dataEditor ) 
            [6] => Array ( [supervisor_dashboard_url] => www./dashboard )   
            [7] => Array ( [supervisor_dashboard_username] => supervisor@eradco.com ) 
            [8] => Array ( [supervisor_dashboard_password] => ordersSupervisor ) 
            [9] => Array ( [inventory_admin_dashboard_url] => www./dashboard ) 
            [10] => Array ( [inventory_admin_dashboard_username] => inventory_admin@eradco.com ) 
            [11] => Array ( [inventory_admin_dashboard_password] => inventory_admin )
        )
    */
    $csv_content='email,password,role\r\n';
    $role=$email=$password='';
    foreach($dashboard_agent_data as $agent_data){
        foreach($agent_data as $key=>$value){
            //if key contain url , skip this iteration 
            if (!strpos($key, 'dashboard_url') !== false){//not contain url
                if(strpos($key, 'dashboard_username') !== false){//found
                    $email=$value;
                    $role=explode('_dashboard',$key)[0];
                    // $password=$agent_data[0][$role.'_dashboard_password'];//'ppppppppp';
                    // $csv_content.=$email.','.$password.','.$role.'\r\n';
                }
                if(strpos($key, $role.'_dashboard_password') !== false){//found
                    $password = $value;

                    if($email!='' && $password!='' && $role!=''){
                        $csv_content.=$email.','.$password.','.$role.'\r\n';
                    }

                    $role=$email='';
                }
            }
        }
    }
    return $csv_content;
}
//==========================================================================
function handleAdminSeederFile($content,$user){
    //===================================================================
    $file_directory = '/home/murabba/public_html/whmcs.murabba.dev/deployment_test/'.$user;
    $file_name      = 'admin.csv';
    $file_path      = $file_directory.'/'.$file_name;
    //===================================================================
    checkExistDirectoty($file_directory);
    checkFileExist($file_path);
    //===================================================================
    $content = str_replace('\r\n', "\n", $content);
    file_put_contents($file_path, $content);
    //===================================================================
    return 'done';
}
//==========================================================================
function handleVaraibleFile($yamlContent,$user){
    //===================================================================
    $root_path      = '/home/murabba/public_html/whmcs.murabba.dev';
    $repoPath       = $root_path.'/deployment_test';
    echo $repoPath;
    chdir($repoPath);
    //    return executeGitCommand("pwd");
    //===================================================================
        // // executeGitCommand("git stash ");
        // execute('set HOME="/home/murabba/public_html"',$repoPath);
        // execute('git config  user.email "magdasaif3@gmail.com"',$repoPath);
        // execute('git config  user.name "magdasaif"',$repoPath);
        // execute("git branch",$repoPath);
        
        // execute("git reset --hard",$repoPath);
        
        //     // execute("git add .",$repoPath);
        //     // execute('git commit -m "save file befor checkout to dev"');
        
        
        // // execute("git remote update");
        // // execute("git fetch");
        // // execute("git checkout dev");


        // // execute("git checkout main");
        // execute("git checkout -b ".$user);
        // // // execute("git checkout ".$user);
        
        // // execute("git checkout -b ".$user." --track <remote>/".$user);
    //===================================================================
    //creat new empty brnach
    echo execute("git switch --orphan $user",$repoPath);
    //===================================================================
    $file_directory = $repoPath.'/'.$user;
    $file_name      = 'variable.yml';
    $file_path      = $file_directory.'/'.$file_name;
    //===================================================================
    checkExistDirectoty($file_directory);
    checkFileExist($file_path);
    //===================================================================
    $yamlContent = str_replace('\r\n', "\n", $yamlContent);
    file_put_contents($file_path, $yamlContent);
    //===================================================================
    return ['user_branch'=>$user,'file'=>$file_path];
}
##=========================== push .yml to delpoy repo ==================
// function handlePushToRepo($user_branch,$file) {
function handlePushToRepo($user_branch) {
    //===================================================================
    $root_path      = '/home/murabba/public_html/whmcs.murabba.dev';//$_SERVER['DOCUMENT_ROOT'];
    $repoPath       = $root_path.'/deployment_test';
    chdir($repoPath);
    //===================================================================
    execute("git add .",$repoPath);//error: insufficient permission for adding an object to repository database .git/objects error: branch30-5_0/variable.yml: failed to insert into database error: unable to index file 'branch30-5_0/variable.yml' fatal: adding files failed
    execute("git commit -am 'save from new fun from branch $user_branch'",$repoPath);
    execute("git push origin $user_branch",$repoPath);
    //===================================================================
    // // $repoPath= '/home/murabba/public_html/whmcs.murabba.dev/deployment_test';
    // // return $this->execute("git branch",$repoPath);
    // // execute("git stash ");
    // //set HOME , git config will be done only one
    // execute("set HOME=/home/murabba/public_html",$repoPath);
    // execute("git checkout ".$user_branch,$repoPath);
    // // execute("git status",$repoPath);
    // execute("git add .",$repoPath);
    // execute('git config  user.email "magdasaif3@gmail.com"',$repoPath);
    // execute('git config  user.name "magdasaif"',$repoPath);
    // execute("git commit -m 'save new file from new fun'",$repoPath);
    // execute("git push --set-upstream origin ".$user_branch);
    //===================================================================
    // // executeGitCommand("git stash ");
    // // executeGitCommand("git config --add safe.directory '*'");
    // executeGitCommand("git checkout ".$user_branch);
    // executeGitCommand("echo 'error_log' >> .gitignore");
    // executeGitCommand("git add .");
    // executeGitCommand("git commit");
    // executeGitCommand("git push --set-upstream origin ".$user_branch);
    //===================================================================
    return 'done';
}
//==========================================================================
function execute($cmd, $workdir = null) {

    /**
     * Executes a command and reurns an array with exit code, stdout and stderr content
     * @param string $cmd - Command to execute
     * @param string|null $workdir - Default working directory
     * @return string[] - Array with keys: 'code' - exit code, 'out' - stdout, 'err' - stderr
     */
     
    if (is_null($workdir)) {
        $workdir = __DIR__;
    }
    echo '<br>-----------------------dir------------------<br>';
    echo $workdir;
    echo '<br>---------------------------------------------<br>';
    echo '<br>-----------------------command------------------<br>';
    echo $cmd;
    echo '<br>---------------------------------------------<br>';
    $descriptorspec = array(
    0 => array("pipe", "r"),  // stdin
    1 => array("pipe", "w"),  // stdout
    2 => array("pipe", "w"),  // stderr
    );

    $process = proc_open($cmd, $descriptorspec, $pipes, $workdir, null);

    $stdout = stream_get_contents($pipes[1]);
    fclose($pipes[1]);

    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[2]);

    $data= [
        'code' => proc_close($process),
        'out' => trim($stdout),
        'err' => trim($stderr),
    ];

    Capsule::table('tblerrorlog')->insert(['severity'=> 'exec log','message'=>$cmd,'details' =>json_encode($data)]) ;

    print_r($data);
    echo '<br>----------------------------------------------<br>';

}
//==========================================================================
function HostDetails($service_id){
    $command = 'GetClientsProducts';
    $postData = array(
        'serviceid' => $service_id,
    );
    
    $results = localAPI($command, $postData);
    // print_r($results);

    return $results['products']['product'][0];
    // return $results['products']['product'][0]['password'];
}
//==========================================================================
//retrieve list of dashboard_url dashboard_username_dashboard_password for package
function fetchProjectAgentData($tenant_id,$project_id,$package_id,$domain,$set_url_value){
    $custom_value=[];
    $agents=Capsule::table('projects_agents')->where('project_id',$project_id)->get();
    foreach ($agents as $agent) {
        //check if found value for custome fieldname for dashboaed data or no
        if($set_url_value==true){
            $custom_value[]=handleTenantCustomFeilds($agent->role.'_dashboard_url',$package_id,$tenant_id,'www.'.$domain.($agent->dashboard_path)??'/');
        }
        $custom_value[]=handleTenantCustomFeilds($agent->role.'_dashboard_username',$package_id,$tenant_id,$agent->username,'on');
        $custom_value[]=handleTenantCustomFeilds($agent->role.'_dashboard_password',$package_id,$tenant_id,$agent->password,'on');
    }
    return $custom_value;
}