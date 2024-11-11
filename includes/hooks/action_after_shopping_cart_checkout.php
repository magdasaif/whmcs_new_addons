<?php
//this file will be located in whmcs includes/hooks folders
use WHMCS\Database\Capsule;

if (!defined('WHMCS'))
die('You cannot access this file directly.');

//=================================================================
add_hook('AfterShoppingCartCheckout', 1, function($vars) {
    // handle actions after add order step
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    /*
        OrderID	        int	    The Order ID
        OrderNumber	    int	    The randomly generated order number
        ServiceIDs	    array	An array of Service IDs created by the order
        AddonIDs	    array	An array of Addon IDs created by the order
        DomainIDs	    array	An array of Domain IDs created by the order
        RenewalIDs	    array	An array of Domain Renewal IDs created by the order
        PaymentMethod	string	The payment gateway selected
        InvoiceID	    int	    The Invoice ID
        TotalDue	    float	The total amount due
    */
    /*
        response
        {"OrderID":54,"OrderNumber":"4605195931","ServiceIDs":[49],"DomainIDs":[],"AddonIDs":[],"UpgradeIDs":[],"RenewalIDs":[],"PaymentMethod":"paypal","InvoiceID":101,"TotalDue":"2.99","Products":[49],"Domains":[],"Addons":[],"Renewals":[],"ServiceRenewals":[],"AddonRenewals":[]}
    */
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    Capsule::table('tblerrorlog')->insert([
        'severity'  => 'test-hook',
        'message'   => 'AfterShoppingCartCheckout, order -->'.$vars['OrderID'],
        'details'   => json_encode($vars)
    ]) ;
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    //
    // we will fetch package_id from host whose id in response ServiceIDs
    // we will fetch project_id is group id from product whose id =package_id
    $order_id       = $vars['OrderID'];
    $tenant_id      = $vars['ServiceIDs'][0];//host_id

    $host_data      = Capsule::table('tblhosting')->where('id',$tenant_id)->first();
    $package_id     = $host_data->packageid;//product_id

    $package_data   = Capsule::table('tblproducts')->where('id',$package_id)->first();
    $project_id     = $package_data->gid;//group_id

    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    // //depend on product server type , determine deployment type 
    // //TODO or store it in product_extra_details
    // $product_server_type=$package_data->servertype;
    // if($product_server_type=='cpanel'){
    //     $deployment_project_type='project';
    // }elseif($product_server_type=='superadminmodule'){
    //     $deployment_project_type='product';
    // }else{
    //     $deployment_project_type='product';
    // }

    $deployment_project_type=fetchDeploymentType($package_data);
     //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    // Capsule::table('tblerrorlog')->insert([
    //     'severity'  => 'test-hook',
    //     'message'   => 'fetchDeploymentType',
    //     'details'  => json_encode($deployment_project_type)
    // ]) ;
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

    //handle db from project_services table for selected project
    $project_dbs            = Capsule::table('project_services')->where('project_id',$project_id)->pluck('db_path','name')->toArray();   //store as array
    $tenancy_db_details     = json_encode($project_dbs);
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    Capsule::table('tenant_extra_details')->insert([
        'tenant_id'                 => $tenant_id,
        'order_id'                  => $order_id,
        'project_id'                => $project_id,
        'package_id'                => $package_id,
        'deployment_project_type'   => $deployment_project_type,
        // 'tenancy_db_names'          => $db_names,
        'tenancy_db_details'        => $tenancy_db_details,
        // 'deployment_details'        => (handleDeploymentVariables($host_data,$project_id)),
    ]) ;
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    //TODO , here we need to check or handle custom fields
    fetchProjectAgentData($tenant_id,$project_id,$package_id,'',false);//tenant domian not set yet , so when accept order we will update dashboard url with the help of domain 
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    //start to send this data to external endpoint
    $postData = [
        'details' => handleOrderObject($vars),
        // Include any other necessary parameters
    ];
    sendPostDataToErp('https://n8n.murabba.dev/webhook/new-order',$postData,'order_created');
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
});
//=================================================================
function handleOrderObject($order){
    // {"OrderID":54,"OrderNumber":"4605195931","ServiceIDs":[49],"DomainIDs":[],"AddonIDs":[],"UpgradeIDs":[],"RenewalIDs":[],"PaymentMethod":"paypal","InvoiceID":101,"TotalDue":"2.99","Products":[49],"Domains":[],"Addons":[],"Renewals":[],"ServiceRenewals":[],"AddonRenewals":[]}

    $tenant_id      = $order['ServiceIDs'][0];//host_id
    
    $host_data      = Capsule::table('tblhosting')->where('id',$tenant_id)->first();
    $package_id     = $host_data->packageid;//product_id
    
    $package_data   = Capsule::table('tblproducts')->where('id',$package_id)->first();
    $project_id     = $package_data->gid;//group_id

    $client_data    = Capsule::table('tblclients')->where('id',$host_data->userid)->first();

    //******************************************************************* */
    $order_details=[
        'order_details'=>[
            'order_id'       => $order['OrderID'],
            'order_number'   => $order['OrderNumber'],
            'payment_method' => $order['PaymentMethod'],
        ]
    ];
    //******************************************************************* */    
    $host_details=[
        'host_details'=>[
            'host_id'       => $tenant_id,
            'domain'        => $host_data->domain,
            'domain_status' => $host_data->domainstatus,
        ]
    ];
    //******************************************************************* */
    $product_details=[
        'product_details'=>[
            'product_id'    => $host_data->packageid,
            'product_name'  => $package_data->name,
        ]
    ];
    //******************************************************************* */
    $invoice_details=[];
    if($order['InvoiceID']!=0){
        
        $invoice_data    = Capsule::table('tblinvoices')->where('id',$order['InvoiceID'])->first();

        $invoice_details=[
            'invoice_details'=>[
                'invoice_id'     => $order['InvoiceID'],
                'date'           => $invoice_data->date,
                'duedate'        => $invoice_data->duedate,
                'subtotal'       => $invoice_data->subtotal,
                'credit'         => $invoice_data->credit,
                'tax'            => $invoice_data->tax,
                'tax2'           => $invoice_data->tax2,
                'total'          => $invoice_data->total,
                'taxrate'        => $invoice_data->taxrate,
                'taxrate2'       => $invoice_data->taxrate2,
                'paymentmethod'  => $invoice_data->paymentmethod,
                'paymethodid'    => $invoice_data->paymethodid,
                'notes'          => $invoice_data->notes,
                'created_at'     => $invoice_data->created_at,
            ]
        ];
    }
    //******************************************************************* */
    $client_details=[
        'client_details'=>[
            'client_id'         => $client_data->id,
            'firstname'         => $client_data->firstname,
            'lastname'          => $client_data->lastname,
            'companyname'       => $client_data->companyname,
            'email'             => $client_data->email,
            'address1'          => $client_data->address1,
            'city'              => $client_data->city,
            'state'             => $client_data->state,
            'postcode'          => $client_data->postcode,
            'country'           => $client_data->country,
            'phonenumber'       => $client_data->phonenumber,
            'status'            => $client_data->status,
            'defaultgateway'    => $client_data->defaultgateway,
            'emailoptout'       => $client_data->emailoptout,
            'allow_sso'         => $client_data->allow_sso,
        ]
    ];
    //******************************************************************* */
    return array_merge($order_details,$host_details,$product_details,$invoice_details,$client_details);
}
//=================================================================
// function projectServiceDetails($order_id){
//     $host_data      = Capsule::table('tblhosting')->where('orderid',$order_id)->first();
//     $package_id     = $host_data->packageid;//product_id

//     $package_data   = Capsule::table('tblproducts')->where('id',$package_id)->first();
//     $project_id     = $package_data->gid;//group_id

//     $project_data    = Capsule::table('tblproductgroups')->where('id',$project_id)->first();
   
//     //handle db from project_services table for selected project
//     $project_dbs = Capsule::table('project_services')->where('project_id',$project_id)->pluck('db_path','name')->toArray();   //store as array

//     return ['project_data'=>$project_data,'project_dbs'=>$project_dbs];
// }
// //==========================================================================
// function fetchServerDetails($host_data){
//     $host_username  = $host_data->username;
//     $host_password  = $host_data->password;
//     $host_domain    = $host_data->domain;
//     $server_id      = $host_data->server;

//     $server_data        = Capsule::table('tblservers')->where('id',$server_id)->first();
//     $server_ip          = $server_data->name;       //"66.7.221.85",
//     $server_name        = $server_data->name;       //"host.murabba.dev",
//     $server_hostname    = $server_data->hostname;   //"host.murabba.dev",
//     $server_assignedips = $server_data->assignedips;//"66.7.221.85",   
    
//     $data=[
//         'server_ip'         => $server_ip,
//         'server_name'       => $server_name,
//         'server_hostname'   => $server_hostname,
//         'server_assignedips'=> $server_assignedips,
//         'host_username'     => $host_username,
//         'host_password'     => $host_password,
//         'host_domain'       => $host_domain,
//     ];

//     return $data;
// }
// //==========================================================================
// function fetchprojectDetails($project_id){
//     return $project_data    = Capsule::table('tblproductgroups')->where('id',$project_id)->first();
// }
// //==========================================================================
// function fetchprojectExtraDetails($project_id){
//     return $project_data    = Capsule::table('project_extra_details')->where('id',$project_id)->first();
// }
// //==========================================================================
// function handleDeploymentVariables($host_data,$project_id){

//     //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
//     //start to fetch correct data
//     $server_details         = fetchServerDetails($host_data);
//     $project_details        = fetchprojectDetails($project_id);
//     $project_extra_details  = fetchprojectExtraDetails($project_id);
    
//     $app_name=$project_details->name;//this will be project name
//     // $user='murabba';
//     $user=$server_details['host_username'];

//     $base_dir='/home//'.$user.'/public_html//'.$app_name;//$project_extra_details->base_dir;//this will be project root directory //TODO , will added in table, not added yet
//     $livelink_dir='backend';
//     $mail_domain='murabba.dev';
//     $deploy_domain=$server_details['host_domain']; // backend.${APPNAME}.${MAINDOMAIN} // $CI_PROJECT_PATH_SLUG.$CI_COMMIT_REF_SLUG.$CI_ENVIRONMENT_SLUG
//     $deploy_path='/var/www/'.$deploy_domain;
//     $deploy_user='deploy';
//     $deploy_group='deploy';
//     //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

//     $data ='USER='.$user.'#';               // murabba                                           //TODO ,murabba or tenant user name ??
//     $data.='APPNAME='.$app_name.'#';        // modulizeCiCd
//     $data.='BASE_DIR='.$base_dir.'#';       // /home/${USER}/public_html/${APPNAME}
//     $data.='COMPOSER=/opt/cpanel/composer/bin/composer#';// /opt/cpanel/composer/bin/composer
  
//     //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
//     //TODO, may be more than db , we will loop for all db and handle its configuration
//     $dbs=[];
//     for ($i=0;$i<count($dbs);$i++) {
//         //$db_name=str_replace('-', '_', $project_data->name).'_'.str_replace('-', '_', $host_data->username).'_'.str_replace(' ', '_', $service_name);
//         $db_db=$dbs.'db';
//         $db_user=$dbs.'usr';

//         $db_variables ='';
//         $db_variables.='DBDATABASE'.$i.'='.$db_db.'#';// ${USER}_${APPNAME}db  //${app_name}_${user}_${service_name}
//         $db_variables.='DBHOST'.$i.'='.$server_details['server_ip'].'#';// localhost
//         $db_variables.='DBPORT'.$i.'=3306#';// 3306
//         $db_variables.='DBUSER'.$i.'='.$db_user.'#';// ${USER}_${APPNAME}usr
//         $db_variables.='DBPASSWORD'.$i.'='.generateStrongPassword().'#';// S191tu^PPARW


//         $data.=$db_variables;
//     }
//     // $data.='DBDATABASE='.$app_name.'#';// ${USER}_${APPNAME}db
//     // $data.='DBHOST='.$server_details['server_ip'].'#';// localhost
//     // $data.='DBPASSWORD='.$app_name.'#';// S191tu^PPARW
//     // $data.='DBPORT='.$app_name.'#';// 3306
//     // $data.='DBUSER='.$app_name.'#';// ${USER}_${APPNAME}usr
//     //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    
//     // $data.='DEPLOY_DOMAIN='.$server_details['host_domain'].'#';// backend.${APPNAME}.${MAINDOMAIN}
//     $data.='ENV_FILE='.$base_dir.'/.env#';// ${BASE_DIR}/.env
//     $data.='HTACCESS_FILE='.$base_dir.'/htaccess.txt#';// ${BASE_DIR}/htaccess.txt
//     $data.='KEEP=1#';// 1
//     $data.='LIVELINK_DIR='.$livelink_dir.'#';// backend
//     $data.='LIVE_PATH='.$base_dir.'/'.$livelink_dir.'#';// ${BASE_DIR}/${LIVELINK_DIR}
    
//     //TODO , mail configuration data
//     $data.='MAILENCRYPTION='.$app_name.'#';// tls
//     $data.='MAILFROMADDRESS='.$app_name.'@'.$mail_domain.'#';// ${APPNAME}@${MAINDOMAIN}
//     $data.='MAILFROMNAME='.$app_name.'#';// ${APPNAME}
//     $data.='MAILHOST=mail.'.$mail_domain.'#';// mail.${MAINDOMAIN}
//     $data.='MAILMAILER='.$app_name.'#';// smtp
//     $data.='MAILPASSWORD='.$app_name.'#';// j2Vt17bhgGzy
//     $data.='MAILPORT='.$app_name.'#';// 587
//     $data.='MAILUSERNAME='.$app_name.'@'.$mail_domain.'#';// ${APPNAME}@${MAINDOMAIN}
    
//     $data.='NPM_VERSION=18#';// 18
//     $data.='PHPVER=/usr/local/bin/ea-php82#';// /usr/local/bin/ea-php82
//     $data.='RELEASES_DIR='.$base_dir.'/backendreleases#';// ${BASE_DIR}/backendreleases
//     $data.='STORAGE='.$base_dir.'/storage#';// ${BASE_DIR}/storage
//     $data.='SWAGGERHOST=https://'.$livelink_dir.$app_name.$mail_domain.'api#';// https://${LIVELINK_DIR}.${APPNAME}.${MAINDOMAIN}/api
//     $data.='NORMALUSER='.$user.'#';// ${USER}
//     $data.='OWNER='.$user.'#';// ${USER}
//     $data.='GROUP='.$user.'#';// ${USER}
    
//     //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
//     $data.='GIT_USER=$CI_COMMIT_AUTHOR_NAME#';// $CI_COMMIT_AUTHOR_NAME                         //TODO ?????????
//     $data.='GIT_EMAIL=$CI_COMMIT_AUTHOR_EMAIL#';// $CI_COMMIT_AUTHOR_EMAIL                      //TODO ?????????
//     $data.='COMMIT_TIME=$CI_COMMIT_TIMESTAMP#';// $CI_COMMIT_TIMESTAMP                          //TODO ?????????
//     //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    
//     $data.='DEPLOY_HOST='.$server_details['server_ip'].'#';// 66.7.221.85
//     $data.='DEPLOY_DOMAIN='.$deploy_domain.'#';
//     $data.='DEPLOY_PATH='.$deploy_path.'#';// /var/www/$DEPLOY_DOMAIN                           //TODO ???????
//     $data.='DEPLOY_USER='.$deploy_user.'#';// deploy                                            //TODO ???????
//     $data.='DEPLOY_GROUP='.$deploy_group.'#';// deploy
//     $data.='DEPLOY_MODE=0755#';// 0755
//     $data.='DEPLOY_OWNER='.$deploy_user.':'.$deploy_group.'#';// $DEPLOY_USER:$DEPLOY_GROUP
//     $data.='DEPLOY_RELEASE='.$deploy_path.'/releases/$CI_COMMIT_SHORT_SHA#';// $DEPLOY_PATH/releases/$CI_COMMIT_SHORT_SHA //TODO ?????????
//     $data.='DEPLOY_CURRENT='.$deploy_path.'/current#';// $DEPLOY_PATH/current
//     $data.='DEPLOY_SHARED='.$deploy_path.'/shared#';// $DEPLOY_PATH/shared
//     $data.='DEPLOY_STORAGE='.$deploy_path.'/storage#';// $DEPLOY_PATH/storage
//     $data.='DEPLOY_PUBLIC='.$deploy_path.'/public#';// $DEPLOY_PATH/public
//     $data.='DEPLOY_LOGS='.$deploy_path.'/logs#';// $DEPLOY_PATH/logs
//     $data.='DEPLOY_ENV='.$deploy_path.'/.env#';// $DEPLOY_PATH/.env
//     $data.='DEPLOY_NPM='.$deploy_path.'/node_modules#';// $DEPLOY_PATH/node_modules
//     $data.='DEPLOY_COMPOSER='.$deploy_path.'/vendor#';// $DEPLOY_PATH/vendor
//     $data.='DEPLOY_PHP='.$deploy_path.'/php#';// $DEPLOY_PATH/php
//     $data.='DEPLOY_PHP_VERSION=8.2#';// 8.2
   
//     $data.='FIRST_RUN=false#';//false

//     return $data;
// }