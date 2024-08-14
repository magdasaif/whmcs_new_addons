<?php

namespace WHMCS\Module\Addon\Superadminaddonmodule\Admin;
use WHMCS\Database\Capsule;
use Illuminate\Database\Capsule\Manager as CapsuleManager;
use PDO;
use WHMCS\Mail\Message\SendTransactionalEmail;
use WHMCS\Mail\Email;
use Carbon\Carbon;

/**
 * Sample Admin Area Controller
 */
class Controller {

    /**
     * Index action.
     *
     * @param array $vars Module configuration parameters
     *
     * @return string
     */
    public function index($vars){

        return 'this model for handling extra details related to project/group and tenant details.';
        // return $dashboard_agent_data   = $this->fetchProjectAgentData(70,2,14,'');
        
        // return $this->sendCustomMail();

        // return view('superadmin.tenant.index');
        print_r($this->retrieveDashboardData(70,2,14));

        return '----------------------------';
        return $this->HostDetails(60)['password'];
        //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
            // $workdir= '/home/murabba/public_html/whmcs.murabba.dev/deployment_test';
            // chdir($repoPath);
            // echo $this->execute("git branch",$workdir);
            // $branch="branch30-5_7";
            // //===================================================================
            // echo $this->execute("git switch --orphan $branch",$workdir);
            // //===================================================================
            // $file_directory = $workdir.'/'.$branch;
            // $file_name      = 'variable.yml';
            // $file_path      = $file_directory.'/'.$file_name;
            // //===================================================================
            // $this->checkExistDirectoty($file_directory);
            // $this->checkFileExist($file_path);
            // //===================================================================
            // $yamlContent="hello ".$branch;
            // //===================================================================
            // $yamlContent = str_replace('\r\n', "\n", $yamlContent);
            // file_put_contents($file_path, $yamlContent);
            // //===================================================================
            // // echo $this->execute('set HOME="/home/murabba/public_html"',$workdir);
            // // echo $this->execute('git config  user.email "magdasaif3@gmail.com"',$workdir);
            // // echo $this->execute('git config  user.name "magdasaif"',$workdir);

            
            // // echo $this->execute("git checkout dev",$workdir);
            // // echo $this->execute("git checkout -b $branch",$workdir);
            // echo $this->execute("git switch --orphan $branch",$workdir);
            // // echo $this->execute("git status",$workdir);
            
            // //==========================================================
            // // echo $this->execute("git config core.sharedRepository",$workdir);
            // // echo $this->execute("chmod -R ug+w .;",$workdir);
            // // $this->execute("cd .git");
            // // chdir($repoPath.'/.get');
            // // echo $this->execute("chmod -R 775 /home/murabba/public_html/whmcs.murabba.dev/deployment_test");
            // // echo $this->execute("chown -R murabba:348 /home/murabba/public_html/whmcs.murabba.dev/deployment_test");
            // echo $this->execute("git add .",$workdir);//error: insufficient permission for adding an object to repository database .git/objects error: branch30-5_0/variable.yml: failed to insert into database error: unable to index file 'branch30-5_0/variable.yml' fatal: adding files failed
            // //==========================================================

            // // echo $this->execute("git add .",$workdir);//error: insufficient permission for adding an object to repository database .git/objects error: branch30-5_0/variable.yml: failed to insert into database error: unable to index file 'branch30-5_0/variable.yml' fatal: adding files failed
            // echo $this->execute("git commit -am 'save from new fun from branch $branch'",$workdir);
            // echo $this->execute("git push origin $branch",$workdir);
            
            return '1';

        //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: 
    }
    //==========================================================================
    //retrieve list of dashboard_url dashboard_username_dashboard_password for package
    function fetchProjectAgentData($tenant_id,$project_id,$package_id,$domain){
        $custom_value=[];
        $agents=Capsule::table('projects_agents')->where('project_id',$project_id)->get();
        foreach ($agents as $agent) {
            //check if found value for custome fieldname for dashboaed data or no
            $custom_value[]=$this->handleTenantCustomFeilds($agent->role.'_dashboard_url',$package_id,$tenant_id,'www.'.$domain.($agent->dashboard_path)??'/');
            $custom_value[]=$this->handleTenantCustomFeilds($agent->role.'_dashboard_username',$package_id,$tenant_id,$agent->username);
            $custom_value[]=$this->handleTenantCustomFeilds($agent->role.'_dashboard_password',$package_id,$tenant_id,$agent->password);
        }
        return $custom_value;
    }
    //==========================================================================
    function handleTenantCustomFeilds($feild_name,$package_id,$tenant_id,$value){
        $fieldname=Capsule::table('tblcustomfields')->where(['fieldname'=>$feild_name,'type'=>'product','relid'=>$package_id])->first();
        if(!isset($fieldname)){
            //check found value or no
            $id=Capsule::table('tblcustomfields')->insertGetId(['fieldname'=>$feild_name,'type'=>'product','relid'=>$package_id,'fieldtype'=>'text','showinvoice'=>'on']);
        }else{
            $id=$fieldname->id;
        }
        

        $fieldvalue_detail_for_username=Capsule::table('tblcustomfieldsvalues')->updateOrInsert(
            [
                'fieldid'   => $id,
                'relid'     => $tenant_id,
            ],
            [
                'fieldid'=> $id,
                'relid'  => $tenant_id,
                'value'  => $value
            ]
        );
        return [$feild_name => $value];
    }
    //==========================================================================
    function fetchHostData($tenant_id){
        $host_data= Capsule::table('tblhosting')->where('id',$tenant_id)->first();
        return ['client_id'=>$host_data->userid,'domain'=>$host_data->domain];
    }
    //==========================================================================
    function fetchDataForMail($tenant_id,$project_id,$package_id,$domain)  {//TODO, we will fetch pipeline data for specific order
        //TODO ,we need tenant_id,project_id,package_id,domain
        $data=[];
        //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
        $html='
        <table style="border-collapse: collapse; width: 87.5168%; height: 49px; margin-left: auto; margin-right: auto;" border="1">
            <tbody>
        ';
        // $dashboard_agent_data   = $this->fetchProjectAgentData(78,2,6,'fdfdfdfdfdf.erad');
        $dashboard_agent_data   = $this->fetchProjectAgentData($tenant_id,$project_id,$package_id,$domain);
        foreach($dashboard_agent_data as $agent_data){
            foreach($agent_data as $key=>$value){
                $html.='
                <tr style="height: 18px;">
                    <td style="width: 20.823%; height: 18px;"><span style="color: #003366;"><strong>'.$key.'</strong></span></td>
                    <td style="width: 28.9349%; height: 18px;">'.$value.'</td>
                </tr>';       
            }
        }
        $html.='
            </tbody>
        </table>';
        //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
        $data['project_agents_html_details']=$html;

        return $data;
    }
    //==========================================================================
    function sendCustomMail()  {
        /*
            Request Parameters
            Parameter	    Type	Description	Required
            action	        string	“SendEmail”	Required
            messagename	    string	The name of the client email template to send	Optional
            id	            int	The related id for the type of email template. Eg this should be the client id for a general type email	Optional
            customtype	    string	The type of custom email template to send (‘general’, ‘product’, ‘domain’, ‘invoice’, ‘support’, ‘affiliate’)	Optional
            custommessage	string	The HTML message body to send for a custom email	Optional
            customsubject	string	The subject to send for a custom email	Optional
            customvars	    array	The custom variables to provide to the email template. Can be used for existing and custom emails.	Optional
        */

        //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
        //fetch mail data/content
        $tenant_id  = $_REQUEST['tenant_id'];
        $host_data  = $this->fetchHostData($tenant_id);
        $client_id  = $host_data['client_id'];
        $domain     = $host_data['domain'];
        $customvars = $this->fetchDataForMail($tenant_id,$_REQUEST['project_id'],$_REQUEST['package_id'],$domain);
        //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

        // Define parameters
        $command = 'SendEmail';
        $values = array(
            'messagename' => 'Pipeline Complete', //The name of the client email template to send	
            'id' => $client_id,//'2', //The related id for the type of email template. Eg this should be the client id for a general type email	
            
            // 'customvars' => base64_encode(serialize(array("dashboard_url"=>$customvars['dashboard_url'], "dashboard_username"=>$customvars['dashboard_username'],'dashboard_password'=>$customvars['dashboard_password']))),
            'customvars' => base64_encode(serialize(array("project_agents_html_details"=>$customvars['project_agents_html_details']))),
        );
        // $adminuser = 'AdminUsername';

        // Call the localAPI function
        $results = localAPI($command, $values);
        if ($results['result'] == 'success') {
            echo 'Message sent successfully!';
        } else {
            echo "An Error Occurred: " . $results['message'];
        }
        //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
        //send tenant data to needed project to store in project tenant table
        $this->sendTenantDataToNeededProject($tenant_id);
        //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    }
    //==========================================================================
    function sendTenantDataToNeededProject($tenant_id){
       $tenant_data         = Capsule::table('tblhosting')->where('id',$tenant_id)->first();
       $tenant_extra_data   = Capsule::table('tenant_extra_details')->where('tenant_id',$tenant_id)->first();
       $project_extra_data  = Capsule::table('project_extra_details')->where('project_id',$tenant_extra_data->project_id)->first();
       $server_details      = Capsule::table('tblservers')->where('id',$tenant_data->server)->first();

       // handle the request parameters
       $post_data=[
            'tenant_id'         => $tenant_id,//id of this host reqest
            'domain'            => $tenant_data->domain,//we can fetch string tenant_id from this domain
            'name'              => $tenant_data->username,
            'ipaddress'         => $server_details->ipaddress,
            'tenancy_db_names'  => $tenant_extra_data->tenancy_db_names,
            'tenancy_db_details'=> $tenant_extra_data->tenancy_db_details,
       ];

       //fetch project url
       $url=($project_extra_data->live_link).'/api/fetch_tenant_details/';

       Capsule::table('tblerrorlog')->insert(['severity' => 'curl request url', 'details' => $url]);

       //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
        //using curl
        // Create a new cURL resource
        $ch = curl_init();
    
        // Set the URL
        curl_setopt($ch, CURLOPT_URL, $url);
    
        // Set the request method to POST,default is GET
        curl_setopt($ch, CURLOPT_POST, true);
               
        // Set the request parameters
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
    
        // Set options for receiving the response
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        // Execute the request
        $response = curl_exec($ch);
               
        Capsule::table('tblerrorlog')->insert(['severity' => 'check curl request', 'details' => $response]);

        // Check for errors
        if ($response === false) {
            // Request failed
            $error = curl_error($ch);
            Capsule::table('tblerrorlog')->insert(['severity' => 'error through curl request ', 'details' => $error]);
        } else {
            // Request was successful
            $responseData = json_decode($response, true);
            Capsule::table('tblerrorlog')->insert(['severity' => 'curl request success', 'details' => $responseData]);
        }
    
        // Close the cURL resource
        curl_close($ch);
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
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
        $value=null;
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
    ##============================== service details ==================
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
                // Capsule::table('tblerrorlog')->insert(['severity' => 'file error', 'message'=>"Could not create directory:". $directory,'details' => $e->getMessage()]);

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
            // Capsule::table('tblerrorlog')->insert(['severity' => 'file error', 'message'=>"Could not create file:". $file_path,'details' => $e->getMessage()]);

            // $this->error("Could not create file: $file_path");
            // $this->info("Please check that the parent directory exists and is writable.");
            return;
        }
        
    }
    //=============================================================================
    /**
     * Executes a command and reurns an array with exit code, stdout and stderr content
     * @param string $cmd - Command to execute
     * @param string|null $workdir - Default working directory
     * @return string[] - Array with keys: 'code' - exit code, 'out' - stdout, 'err' - stderr
     */
    function execute($cmd, $workdir = null) {

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

        print_r($data);
        echo '<br>----------------------------------------------<br>';

    }
    //=============================================================================
    /*
    //==========================unused functions===================================
        public function show($vars)
        {
            // Get common module parameters
            $modulelink = $vars['modulelink']; // eg. superadminaddonmodules.php?module=superadminaddonmodule
            $version = $vars['version']; // eg. 1.0
            $LANG = $vars['_lang']; // an array of the currently loaded language variables

            // Get module configuration parameters
            $configTextField = $vars['Text Field Name'];
            $configPasswordField = $vars['Password Field Name'];
            $configCheckboxField = $vars['Checkbox Field Name'];
            $configDropdownField = $vars['Dropdown Field Name'];
            $configRadioField = $vars['Radio Field Name'];
            $configTextareaField = $vars['Textarea Field Name'];

            return <<<EOF

                    <h2>Show</h2>

                    <p>This is the <em>show</em> action output of the SuperAdmin Addon module.</p>

                    <p>The currently installed version is: <strong>{$version}</strong></p>

                    <p>
                        <a href="{$modulelink}" class="btn btn-info">
                            <i class="fa fa-arrow-left"></i>
                            Back to home
                        </a>
                    </p>
            EOF;
        }
        // =========================================================================
        //start to handle addon add form 
        public function create($vars){
            //=====================================================
            // Get module configuration parameters
            // $configPasswordField = $vars['Password Field Name'];
            // $configCheckboxField = $vars['Checkbox Field Name'];
            // $configTextareaField = $vars['Textarea Field Name'];
            
            $tenant_id                  = $vars['tenant_id'];
            $project_id                 = $vars['project_id'];
            $deployment_project_type    = $vars['deployment_project_type'];
        
            //=====================================================
            
            echo <<<EOF
            <form method="post" action="https://whmcs.murabba.dev/WMAA/addonmodules.php?module=superadminaddonmodule&action=store">
                <table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
                    <tbody>
                        <tr>
                            <td class="fieldlabel">Project/group</td>
                            <td class="fieldarea">
                                <select name="deployment_project_type" class="form-control select-inline">
                                    <option value="product">product</option>
                                    <option value="project">project</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="fieldlabel">Tenant</td>
                            <td class="fieldarea"><input type="text" name="tenant_id" class="form-control input-300"></td>
                        </tr>
                    </tbody>
                </table>
                <input type="submit" class="btn btn-success" value="Save " />
            </form>
            EOF;
        //=====================================================
        }    
        // =========================================================================
        //start to handle addon add form 
        public function store($vars){
            
            // echo $_REQUEST['action'].'------------------- <br>';
            // echo $_REQUEST['tenant_id'].'------------------- <br>';

            // echo $_SERVER['REQUEST_METHOD'].'------------------- <br>';

            // if ($_SERVER['REQUEST_METHOD'] === 'POST'){
                
            // }
            //=====================================================
            // Get module configuration parameters
            // $configPasswordField = $vars['Password Field Name'];
            // $configCheckboxField = $vars['Checkbox Field Name'];
            // $configTextareaField = $vars['Textarea Field Name'];
            
            $tenant_id                  = $_REQUEST['tenant_id'];
            $project_id                 = $_REQUEST['project_id'];
            $deployment_project_type    = $_REQUEST['deployment_project_type'];
            
            Capsule::table('tenant_details')->insert(['tenant_id'=>$tenant_id,'deployment_project_type'=>$deployment_project_type]);

            echo  'here insert db will be done';
            print_r($_REQUEST);
            
            // Redirect to a success page or display a success message
            header('Location: addonmodules.php?module=superadminaddonmodule&success=1');
            exit;
            
            return 'done';
            
            //=====================================================
        }
    */
    // =========================================================================
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    //:::::::::::::::::::::: start project service part:::::::::::::::::::::::::
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    // =========================================================================
    public function all_project_service(){
        return view('superadmin.project.services.index');
    }
    // =========================================================================
    public function project_service(){
        //display page for add or edit project service
        $groups     = Capsule::table('tblproductgroups')->get();
        $id         = $_REQUEST['id'];

        if(isset($id) && $id!=0){
            $service_details=Capsule::table('project_services')->where('id',$id)->first();
            $edit=1;
        }else{
            $edit=0;
        }
        return view('superadmin.project.services.form',['groups' => $groups,'edit'=>$edit,'service_details'=>($service_details)??'']);
    }
    // =========================================================================
    public function handle_project_service(){
        //this function to handle insert or update project service
        $id             = ($_REQUEST['id'])??0;
        $project_id     = $_REQUEST['project_id'];
        $name           = $_REQUEST['service_name'];
        $db_path        = $_REQUEST['db_path'];
        $active         = $_REQUEST['active'];
        $groups         = Capsule::table('tblproductgroups')->get();

        //=====================================================
        //handle valiation
        if(isset($id) && $id!=0){
            $found_count=Capsule::table('project_services')->where(['project_id'=>$project_id,'name'=>$name])->where('id','!=',$id)->count();
            $edit=1;
            $service_details=Capsule::table('project_services')->where('id',$id)->first();

        }else{
            $found_count=Capsule::table('project_services')->where(['project_id'=>$project_id,'name'=>$name])->count();
            $edit=0;
        }

        if($found_count>0){
            // return redirect()->back()->withErrors('found service with same name for same project');
            $this->storeInActiveLog('validation error ( Found service with same name ['.$name.'] for same project )');

            return view('superadmin.project.services.form',['error' => 'found service with same name for same project','groups'=>$groups,'success'=>0,'edit'=>$edit,'service_details'=>($service_details)??'']);            
        }
        //=====================================================
        if(isset($id) && $id!=0){

            Capsule::table('project_services')->where('id',$id)->update([
                'project_id'    => $project_id,
                'name'          => $name,
                'db_path'       => $db_path,
                'active'        => $active,
            ]);

            $this->storeInActiveLog('Service of ID '.$id.', Related to Project of ID '.$project_id.' has been updated');

        }else{

            $id=Capsule::table('project_services')->insertGetId([
                'project_id'    => $project_id,
                'name'          => $name,
                'db_path'       => $db_path,
                'active'        => $active,
            ]);

            $this->storeInActiveLog('new Service of ID '.$id.', Related to Project of ID '.$project_id.' has been added');
            
        }

        $url="https://whmcs.murabba.dev/WMAA/addonmodules.php?module=superadminaddonmodule&action=all_project_service&success=1";
        $statusCode=200;
        header('Location: ' . $url, true, $statusCode);
        exit;
        //*************************************************************************************************** */

        
        //=====================================================
    }
    // =========================================================================
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    //:::::::::::::::::::::: start project details part:::::::::::::::::::::::::
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    // =========================================================================
    public function projects_extra_details(){
        //=====================================================
        return view('superadmin.project.extra_details.index');
        //=====================================================
    }
    //=========================================================================
    public function edit_project_extra_details() {
        //=====================================================
        $id             = $_REQUEST['id']; //this is the id of project
        $project_name   = Capsule::table('tblproductgroups')->where('id',$id)->first()->name;
        $project_details= Capsule::table('project_extra_details')->where('project_id',$id)->first();
        //=====================================================
        $allow_logo_options=$this->generateSelect([1=>'allow',0=>'not allow'],$project_details,'allow_logo');
        $allow_color_options=$this->generateSelect([1=>'allow',0=>'not allow'],$project_details,'allow_color');
        //=====================================================
        return view('superadmin.project.extra_details.form',
            [
                'id'                    => $id,
                'project_details'       => ($project_details)??'',
                'project_name'          => $project_name,
                'allow_logo_options'    => $allow_logo_options,
                'allow_color_options'   => $allow_color_options
            ]
        );
        //=====================================================
    }
    // =========================================================================
    public function update_project_extra_details(){
        
        $id                 = $_REQUEST['id'];//project id

        // $project_id      = $_REQUEST['project_id'];
        $central_domain     = $_REQUEST['central_domain'];
        $dashboard_path     = $_REQUEST['dashboard_path'];
        $dashboard_password = $_REQUEST['dashboard_password'];
        $dashboard_username = $_REQUEST['dashboard_username'];
        $allow_logo         = $_REQUEST['allow_logo'];
        $allow_color        = $_REQUEST['allow_color'];


        $base_dir           = $_REQUEST['base_dir'];
        $npm_version        = $_REQUEST['npm_version'];
        $php_version        = $_REQUEST['php_version'];
        $swagger_link       = $_REQUEST['swagger_link'];
        $live_link          = $_REQUEST['live_link'];
        $mail_driver        = $_REQUEST['mail_driver'];
        $mail_host          = $_REQUEST['mail_host'];
        $mail_port          = $_REQUEST['mail_port'];
        $mail_username      = $_REQUEST['mail_username'];
        $mail_password      = $_REQUEST['mail_password'];
        $mail_encryption    = $_REQUEST['mail_encryption'];
        $mail_from_address  = $_REQUEST['mail_from_address'];
        $mail_from_name     = $_REQUEST['mail_from_name'];
        

        //=================================================================
        //check central_domain to be unique
        $errors='';
        $central_domain_count   = Capsule::table('project_extra_details')->where('central_domain',$central_domain)->where('project_id','!=',$id)->count();
        $swagger_link_count     = Capsule::table('project_extra_details')->where('swagger_link',$swagger_link)->where('project_id','!=',$id)->count();
        $live_link_count        = Capsule::table('project_extra_details')->where('live_link',$live_link)->where('project_id','!=',$id)->count();
        // $dashboard_path_count=Capsule::table('project_extra_details')->where('dashboard_path',$dashboard_path)->where('project_id','!=',$id)->count();
        
        if($central_domain_count>0){$errors.='central_domain must be unique ,'; }
        if($swagger_link_count>0){$errors.='swagger_link must be unique ,'; }
        if($live_link_count>0){$errors.='live_link must be unique ,'; }
        // if($dashboard_path_count>0){$errors.='dashboard_path must be unique ,'; }
        
        if($errors!=''){
            //=====================================================
            $project_name   = Capsule::table('tblproductgroups')->where('id',$id)->first()->name;
            $project_details= Capsule::table('project_extra_details')->where('project_id',$id)->first();
            //=====================================================
            $allow_logo_options=$this->generateSelect([1=>'allow',0=>'not allow'],$project_details,'allow_logo');
            $allow_color_options=$this->generateSelect([1=>'allow',0=>'not allow'],$project_details,'allow_color');
            //=====================================================
            $this->storeInActiveLog('validation error ( '.$errors.' )');
            //=====================================================
            return view('superadmin.project.extra_details.form',
                [
                    'id'                    => $id,
                    'project_details'       => ($project_details)??'',
                    'project_name'          => $project_name,
                    'allow_logo_options'    => $allow_logo_options,
                    'allow_color_options'   => $allow_color_options,
                    'error'                 => $errors,
                    'success'               => 0
                ]
            );
            //=====================================================
        }
        //=================================================================
        $result=Capsule::table('project_extra_details')->where(['project_id'=>$id])->count();
        if ($result>0) {
            // An update was performed
            $operation="updated";
        } else {
            // An insert was performed
            $operation="added";
        }
        //=================================================================

        Capsule::table('project_extra_details')->updateOrInsert([
            // 'project_id'        => $project_id,
            'project_id'        => $id,
        ],[
            // 'project_id'        => $project_id,
            'project_id'        => $id,
            'central_domain'    => $central_domain,
            'dashboard_path'    => $dashboard_path,
            'dashboard_username'=> $dashboard_path,
            'dashboard_password'=> $dashboard_password,
            'allow_logo'        => $allow_logo,
            'allow_color'       => $allow_color,
            'base_dir'          => $base_dir,
            'npm_version'       => $npm_version,
            'php_version'       => $php_version,
            'swagger_link'      => $swagger_link,
            'live_link'         => $live_link,
            'mail_driver'       => $mail_driver,
            'mail_host'         => $mail_host,
            'mail_port'         => $mail_port,
            'mail_username'     => $mail_username,
            'mail_password'     => $mail_password,
            'mail_encryption'   => $mail_encryption,
            'mail_from_address' => $mail_from_address,
            'mail_from_name'    => $mail_from_name,
        ]);

        $this->storeInActiveLog('Extra Details for Project of ID '.$id.' has been '.$operation);

        // return 'done';
        //*************************************************************************************************** */
        // Redirect to a success page or display a success message
        // header('Location: addonmodules.php?module=superadminaddonmodule&action=all_project_service&success=1');
        // exit;
        //*************************************************************************************************** */

        $url="https://whmcs.murabba.dev/WMAA/addonmodules.php?module=superadminaddonmodule&action=projects_extra_details&success=1";
        $statusCode=200;
        header('Location: ' . $url, true, $statusCode);
        exit;
        //*************************************************************************************************** */

        
        //=====================================================
    }
    // =========================================================================
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    //:::::::::::::::::::::: start product details part:::::::::::::::::::::::::
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    // =========================================================================
    public function products_extra_details(){
        //=====================================================
        return view('superadmin.package.extra_details.index');
        //=====================================================
    }
    // =========================================================================
    public function edit_product_extra_details(){
        //=====================================================
        $id             = $_REQUEST['id']; //this is the id of product/package
        $product_name   = Capsule::table('tblproducts')->where('id',$id)->first()->name;
        $product_details= Capsule::table('package_extra_details')->where('package_id',$id)->first();
        //=====================================================
        $deployment_type=$this->generateSelect(['product'=>'Product','project'=>'Project'],$product_details,'deployment_type');
        //=====================================================
        return view('superadmin.package.extra_details.form',
            [
                'id'                    => $id,
                'product_details'       => ($product_details)??'',
                'product_name'          => $product_name,
                'deployment_type'       => $deployment_type,
            ]
        );
        //=====================================================
    }
    // =========================================================================
    public function update_product_extra_details(){

        //=================================================================
        $id                 = $_REQUEST['id'];//product/package id
        $deployment_type    = $_REQUEST['deployment_type'];
        //=================================================================
        //check deployment_type to be product or project
        $errors='';
        if($deployment_type!=='product'&&$deployment_type!=='project'){
            $errors.='deployment_type must be one of those value [product,project]';
        }
 
        if($errors!=''){
            //=====================================================
            $product_name   = Capsule::table('tblproducts')->where('id',$id)->first()->name;
            $product_details= Capsule::table('package_extra_details')->where('package_id',$id)->first();
            //=====================================================
            $deployment_type_options=$this->generateSelect(['product'=>'Product','project'=>'Project'],$product_details,'deployment_type');
            //=====================================================
            $this->storeInActiveLog('validation error ( '.$errors.' )');
            //=====================================================
            return view('superadmin.package.extra_details.form',
                [
                    'id'                    => $id,
                    'product_details'       => ($product_details)??'',
                    'product_name'          => $product_name,
                    'deployment_type'       => $deployment_type_options,
                    'error'                 => $errors,
                    'success'               => 0
                ]
            );
            //=====================================================
        }
        //=================================================================
        $result=Capsule::table('package_extra_details')->where(['package_id'=>$id])->count();
        if ($result>0) {
            // An update was performed
            $operation="updated";
        } else {
            // An insert was performed
            $operation="added";
        }
        //=================================================================

        Capsule::table('package_extra_details')->updateOrInsert([
            'package_id'        => $id,
        ],[
            'package_id'        => $id,
            'deployment_type'   => $deployment_type,
        ]);

        $this->storeInActiveLog('Extra Details for Product/Package of ID '.$id.' has been '.$operation);

        // return 'done';
        //*************************************************************************************************** */

        $url="https://whmcs.murabba.dev/WMAA/addonmodules.php?module=superadminaddonmodule&action=products_extra_details&success=1";
        $statusCode=200;
        header('Location: ' . $url, true, $statusCode);
        exit;
        //*************************************************************************************************** */

        
        //=====================================================
    }
    // =========================================================================
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    //:::::::::::::::::::::: start project agents part :::::::::::::::::::::::::
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    // =========================================================================
    public function project_agents(){
        //=====================================================
        $project_id=$_REQUEST['id'];
        $project_name=Capsule::table('tblproductgroups')->where('id',$project_id)->first()->name;
        $agents=Capsule::table('projects_agents')->where('project_id',$project_id)->get();
        //=====================================================
        return view('superadmin.project.agents.index',['project_name'=>$project_name,'project_id'=>$project_id,'agents'=>$agents]);
        //=====================================================
    }
    // =========================================================================
    public function create_project_agents(){
        //=====================================================
        $project_name=Capsule::table('tblproductgroups')->where('id',$_REQUEST['id'])->first()->name;
        //=====================================================
        return view('superadmin.project.agents.form',['project_name'=>$project_name,'project_id'=>$_REQUEST['id']]);
        //=====================================================
    }
    // =========================================================================
    public function store_project_agent(){
        // return $_REQUEST;

        $project_id         =   $_REQUEST['project_id'];
        $roles              =   $_REQUEST['role'];
        $usernames          =   $_REQUEST['username'];
        $passwords          =   $_REQUEST['password'];
        $dashboard_paths    =   $_REQUEST['dashboard_path'];
        
        for($i=0;$i<count($roles);$i++){

            //=================================================================
            $result=Capsule::table('projects_agents')->where([
                'project_id'    => $project_id,
                'role'          => $roles[$i],
                'username'      => $usernames[$i],
            ])->count();
            if ($result>0) {
                // An update was performed
                $operation="updated";
            } else {
                // An insert was performed
                $operation="added";
            }
            //=================================================================
            //*************************************************************************************************** */
            Capsule::table('projects_agents')->updateOrInsert([
                'project_id'        => $project_id,
                'role'              => $roles[$i],
                'username'          => $usernames[$i],
            ],[
                'project_id'        => $project_id,
                'role'              => $roles[$i],
                'username'          => $usernames[$i],
                'password'          => $passwords[$i],
                'dashboard_path'    => $dashboard_paths[$i],
            ]);

            $this->storeInActiveLog('Agent for Project of ID '.$project_id.' has been '.$operation);
            //*************************************************************************************************** */
        }
        // return 'done';
        //*************************************************************************************************** */
        $project_name=Capsule::table('tblproductgroups')->where('id',$project_id)->first()->name;
        $agents=Capsule::table('projects_agents')->where('project_id',$project_id)->get();
        return view('superadmin.project.agents.index',['error' => 'Added Done','agents'=>$agents,'success'=>1,'project_name'=>$project_name,'project_id'=>$project_id]);            
        //*************************************************************************************************** */
    }
    // =========================================================================
    public function edit_project_agents(){
        //=====================================================
        $agent_id   = $_REQUEST['id'];
        $project_id = $_REQUEST['project_id'];
        $project_name=Capsule::table('tblproductgroups')->where('id',$project_id)->first()->name;
        $agent_detail=Capsule::table('projects_agents')->where('id',$agent_id)->first();
        //=====================================================
        return view('superadmin.project.agents.edit',['project_name'=>$project_name,'project_id'=>$project_id,'agent_detail'=>$agent_detail]);
        //=====================================================
    }
    // =========================================================================
    public function update_project_agents(){
        //=====================================================
        // print_r($_REQUEST);
        // return 1;
        // $id         = $_REQUEST['id'];
        $project_id         = $_REQUEST['project_id'];
        $agent_id           = $_REQUEST['agent_id'];
        $role               = $_REQUEST['role'];
        $username           = $_REQUEST['username'];
        $password           = $_REQUEST['password'];
        $dashboard_path     = $_REQUEST['dashboard_path'];
        //*************************************************************************************************** */
        $project_name=Capsule::table('tblproductgroups')->where('id',$project_id)->first()->name;
        $agents=Capsule::table('projects_agents')->where('project_id',$project_id)->get();
        //*************************************************************************************************** */
        //check validation
        $check_agent_count=Capsule::table('projects_agents')->where([
            'project_id'    => $project_id,
            'role'          => $role,
            'username'      => $username,
        ])->where('id','!=',$agent_id)->count();

        if($check_agent_count>0){
            //=====================================================
            $error='Found Role and username with same value for this project !';
            $this->storeInActiveLog('validation error ( '.$error.' )');
            return view('superadmin.project.agents.index',['error' => $error,'agents'=>$agents,'success'=>0,'project_name'=>$project_name,'project_id'=>$project_id]);            
            //=====================================================
        }
        //*************************************************************************************************** */
        //update data
        Capsule::table('projects_agents')->where('id',$agent_id)->update([
            'role'              => $role,
            'username'          => $username,
            'password'          => $password,
            'dashboard_path'    => $dashboard_path,
        ]);
        //*************************************************************************************************** */
        return view('superadmin.project.agents.index',['error' => 'Updated Done','agents'=>$agents,'success'=>1,'project_name'=>$project_name,'project_id'=>$project_id]);            
        //*************************************************************************************************** */
    }
    // =========================================================================
    public function delete_project_agents(){
        $id=$_REQUEST['id'];//agent_id
        $project_id=$_REQUEST['project_id'];//project_id
        Capsule::table('projects_agents')->where('id',$id)->delete();
        //*************************************************************************************************** */
        $project_name=Capsule::table('tblproductgroups')->where('id',$project_id)->first()->name;
        $agents=Capsule::table('projects_agents')->where('project_id',$project_id)->get();
        return view('superadmin.project.agents.index',['error' => 'Delete Done','agents'=>$agents,'success'=>1,'project_name'=>$project_name,'project_id'=>$project_id]);            
        //*************************************************************************************************** */
    }
    // =========================================================================
    public function generateSelect($select_options,$data='',$column=''){
        $options='';
        foreach ($select_options as $value=>$key) {
            $options .='<option value="'.$value.'"'; 
            if(isset($data) && gettype($data)!=='string' && isset($data->$column)){
                ($data->$column == $value)?$options .=' selected':$options .='';
            }
            $options .='>'.$key.'</option>';
        }
        return $options;
    }
    //=========================================================================
    public function storeInActiveLog($description){
        //we nned here to fetch current admin data to be used in inseration
        /*
                    user        --> username
                    admin_id    --> id
                    userid      --> userid in action table (userid in order if activity related to order)
                    user_id     --> auth_user_id for userid in tblusers_clients table
        */
        Capsule::table('tblactivitylog')->insert([
            'date'          => Carbon::now()->format('Y-m-d H:i:s'),
            'description'   => $description,
            'user'          => ($this->currentAdmin())?$this->currentAdmin()->username:'',
            'admin_id'      => ($this->currentAdmin())?$this->currentAdmin()->id:0,
            'userid'        => 0,
            'user_id'       => 0,
        ]);

    }
    //=========================================================================
    public function currentAdmin(){
        $currentUser = new \WHMCS\Authentication\CurrentUser;
        $user= null;

        if ($currentUser->isAuthenticatedAdmin()) {
            // echo "Current user authenticated as Admin.";
            $user= $currentUser->admin();
        } else {
            // echo "Current user not authenticated as Admin .";
            $user= null;
        }
        return $user;
    }
    //=========================================================================
    public function currentUser(){
        $currentUser = new \WHMCS\Authentication\CurrentUser;
        $user= null;

        if ($currentUser->isAuthenticatedUser()) {
            // echo "Current user authenticated as User.";
            $user= $currentUser->user();
        } else {
            // echo "Current user not authenticated as User .";
            $user= null;
        }
        return $user;
    }
    //=========================================================================
    public function currentClient(){
        $currentUser = new \WHMCS\Authentication\CurrentUser;
        $user= $currentUser->client();
        return $user;
    }
    //=========================================================================
}
