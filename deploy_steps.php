<?php
//this file will be located in whmcs includes/hooks folders
use WHMCS\Database\Capsule;

if (!defined('WHMCS'))
die('You cannot access this file directly.');

//::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    $orderid='69';
    //third way to handle dbs
    //=======================================================================
    //here we will use external repo to handle pipeline using .yml files
    //so , we need to handle tenant deployment data
    $file_content   = handleDeploymentVariables($orderid)['content'];
    $username       = handleDeploymentVariables($orderid)['username'];
    //=======================================================================
    //then , put thid data in variable.yml file 
    $file_data=handleVaraibleFile($file_content,$username);
    //=======================================================================
    //finally , we need to push this file to deployment repo
   return handlePushToRepo($file_data['user_branch'],$file_data['file']);
//::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
//=================================================================
function projectServiceDetails($order_id){
    $host_data      = Capsule::table('tblhosting')->where('orderid',$order_id)->first();
    $package_id     = $host_data->packageid;//product_id

    $package_data   = Capsule::table('tblproducts')->where('id',$package_id)->first();
    $project_id     = $package_data->gid;//group_id

    $project_data    = Capsule::table('tblproductgroups')->where('id',$project_id)->first();
   
    //handle db from project_services table for selected project
    $project_dbs = Capsule::table('project_services')->where('project_id',$project_id)->pluck('db_path','name')->toArray();   //store as array

    return ['project_data'=>$project_data,'project_dbs'=>$project_dbs];
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
        $db_name=str_replace('-', '_', $app_name).'_'.str_replace('-', '_', $host_data->username).'_'.str_replace(' ', '_', $service_name).'db';
        $db_username=str_replace('-', '_', $app_name).'_'.str_replace('-', '_', $host_data->username).'_'.str_replace(' ', '_', $service_name).'usr';
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
function saveTenantDeploymentData($tenant_id,$data)  {
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    // here we need to store tenant dbs with tenant
    Capsule::table('tenant_extra_details')->where('tenant_id',$tenant_id)->update([
        'deployment_details'    => $data,
    ]) ;
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    return 'done';
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
    //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    $base_dir='/home/'.$user.'/public_html/'.$app_name;//$project_extra_details->base_dir;//this will be project root directory //TODO , will added in table, not added yet
    $livelink_dir='backend';
    $mail_domain='murabba.dev';
    $deploy_domain=$livelink_dir.$server_details['host_domain']; // backend.${APPNAME}.${MAINDOMAIN} // $CI_PROJECT_PATH_SLUG.$CI_COMMIT_REF_SLUG.$CI_ENVIRONMENT_SLUG
    // $deploy_path='/var/www/'.$deploy_domain;
    $deploy_path='/home/'.$user.'/public_html/'.$app_name.'/backendreleases';
    $deploy_user=$user;
    $deploy_group=$user;
    //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

    $data ='USER='.$user.'\r\n';               // murabba                                           //TODO ,murabba or tenant user name ?? 
    $data.='APPNAME='.$app_name.'\r\n';        // modulizeCiCd
    $data.='BASE_DIR='.$base_dir.'\r\n';       // /home/${USER}/public_html/${APPNAME}
    $data.='COMPOSER=/opt/cpanel/composer/bin/composer\r\n';// /opt/cpanel/composer/bin/composer
  
    //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    //TODO, may be more than db , we will loop for all db and handle its configuration
    for ($i=0;$i<count($dbs);$i++) {
        //$db_name=str_replace('-', '_', $project_data->name).'_'.str_replace('-', '_', $host_data->username).'_'.str_replace(' ', '_', $service_name);
        
        $db_db          = $dbs['db'];           //$dbs.'db';
        $db_user        = $dbs['db_username'];  //$dbs.'usr';
        $db_password    = $dbs['db_password'];

        $db_variables ='';
        $db_variables.='DBDATABASE'.$i.'='.$db_db.'\r\n';// ${USER}_${APPNAME}db  //${app_name}_${user}_${service_name}
        $db_variables.='DBHOST'.$i.'='.$server_details['server_ip'].'\r\n';// localhost
        $db_variables.='DBPORT'.$i.'=3306\r\n';// 3306
        $db_variables.='DBUSER'.$i.'='.$db_user.'\r\n';// ${USER}_${APPNAME}usr
        $db_variables.='DBPASSWORD'.$i.'='.$db_password.'\r\n';// S191tu^PPARW

        $data.=$db_variables;
    }

    // $data.='DBDATABASE='.$app_name.'\r\n';// ${USER}_${APPNAME}db
    // $data.='DBHOST='.$server_details['server_ip'].'\r\n';// localhost
    // $data.='DBPASSWORD='.$app_name.'\r\n';// S191tu^PPARW
    // $data.='DBPORT='.$app_name.'\r\n';// 3306
    // $data.='DBUSER='.$app_name.'\r\n';// ${USER}_${APPNAME}usr
    //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    
    // $data.='DEPLOY_DOMAIN='.$server_details['host_domain'].'\r\n';// backend.${APPNAME}.${MAINDOMAIN}
    $data.='ENV_FILE='.$base_dir.'/.env\r\n';// ${BASE_DIR}/.env
    $data.='HTACCESS_FILE='.$base_dir.'/htaccess.txt\r\n';// ${BASE_DIR}/htaccess.txt
    $data.='KEEP=1\r\n';// 1
    $data.='LIVELINK_DIR='.$livelink_dir.'\r\n';// backend
    $data.='LIVE_PATH='.$base_dir.'/'.$livelink_dir.'\r\n';// ${BASE_DIR}/${LIVELINK_DIR}
    
    //TODO , mail configuration data //****
    $data.='MAILENCRYPTION=tls\r\n';// tls
    $data.='MAILFROMADDRESS='.$app_name.'@'.$mail_domain.'\r\n';// ${APPNAME}@${MAINDOMAIN}
    $data.='MAILFROMNAME='.$app_name.'\r\n';// ${APPNAME}
    $data.='MAILHOST=mail.'.$mail_domain.'\r\n';// mail.${MAINDOMAIN}
    $data.='MAILMAILER=smtp\r\n';// smtp
    $data.='MAILPASSWORD=j2Vt17bhgGzy\r\n';// j2Vt17bhgGzy
    $data.='MAILPORT=587\r\n';// 587
    $data.='MAILUSERNAME='.$app_name.'@'.$mail_domain.'\r\n';// ${APPNAME}@${MAINDOMAIN}
    
    $data.='NPM_VERSION=18\r\n';// 18 //****
    $data.='PHPVER=/usr/local/bin/ea-php82\r\n';// /usr/local/bin/ea-php82//****
    $data.='RELEASES_DIR='.$base_dir.'/backendreleases\r\n';// ${BASE_DIR}/backendreleases
    $data.='STORAGE='.$base_dir.'/storage\r\n';// ${BASE_DIR}/storage
    $data.='SWAGGERHOST=https://'.$livelink_dir.$app_name.$mail_domain.'api\r\n';// https://${LIVELINK_DIR}.${APPNAME}.${MAINDOMAIN}/api //****
    $data.='NORMALUSER='.$user.'\r\n';// ${USER}
    $data.='OWNER='.$user.'\r\n';// ${USER}
    $data.='GROUP='.$user.'\r\n';// ${USER}
    
    //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    // $data.='GIT_USER=$CI_COMMIT_AUTHOR_NAME\r\n';// $CI_COMMIT_AUTHOR_NAME                         //TODO ?????????
    // $data.='GIT_EMAIL=$CI_COMMIT_AUTHOR_EMAIL\r\n';// $CI_COMMIT_AUTHOR_EMAIL                      //TODO ?????????
    // $data.='COMMIT_TIME=$CI_COMMIT_TIMESTAMP\r\n';// $CI_COMMIT_TIMESTAMP                          //TODO ?????????
    //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    
    $data.='DEPLOY_HOST='.$server_details['server_ip'].'\r\n';// 66.7.221.85
    $data.='DEPLOY_DOMAIN='.$deploy_domain.'\r\n';
    $data.='DEPLOY_PATH='.$deploy_path.'\r\n';// /var/www/$DEPLOY_DOMAIN                           //TODO ???????
    $data.='DEPLOY_USER='.$deploy_user.'\r\n';// deploy                                            //TODO ???????
    $data.='DEPLOY_GROUP='.$deploy_group.'\r\n';// deploy
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
   
    $data.='FIRST_RUN=false\r\n';//false

    //****************************************************************************
    //here we need to store deploy data with tenant
    saveTenantDeploymentData($host_data->id,$data);
    //****************************************************************************

    // return $data;
    return ['content'=>$data,'username'=>$user];
}
//==========================================================================
function handleVaraibleFile($yamlContent,$user){
    //===================================================================
    $root_path      = $_SERVER['DOCUMENT_ROOT'];
    $repoPath       = $root_path.'/deployment_test';
    chdir($repoPath);
    //===================================================================
    executeGitCommand("git checkout dev");
    executeGitCommand("git checkout -b".$user);
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
        exec($command, $output, $exitCode);

        // Log the output for debugging
        Capsule::table('tblerrorlog')->insert([
            'severity' => 'Git command output',
            'message'  => $command,
            // 'details'  => implode(PHP_EOL, $output)
            'details'  => $exitCode .'-'. json_encode($output)

        ]);

        // Check if the command encountered an error
        if ($exitCode !== 0) {
            throw new \Exception("Git command failed with exit code: $exitCode");
        }

        // Return the output as an array of lines
        return $output;
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
##=========================== push .yml to delpoy repo ==================
function handlePushToRepo($user_branch,$file) {
    //===================================================================
    $root_path      = $_SERVER['DOCUMENT_ROOT'];
    $repoPath       = $root_path.'/deployment_test';
    chdir($repoPath);
    //===================================================================
    executeGitCommand("git checkout ".$user_branch);
    executeGitCommand("git add ",$file);
    executeGitCommand('git commit -m "Push new branch with new content"');
    executeGitCommand("git push --set-upstream origin ".$user_branch);
    //===================================================================
    return 'done';
}
