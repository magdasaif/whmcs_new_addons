<?php
//this file to try display dashboard details for services  
use WHMCS\Database\Capsule;

echo 'ggggggggggggggg';
    // ==========================================================
        //here we will fetch host details to be desplayed in details page
         $host_id        = $_REQUEST['id'];

        
        $host_details   = Capsule::table('tblhosting')->where('id',$host_id)->first();
        
        print_r (['ggg'=>'yyyyyyy']);
        
        $order_details  = Capsule::table('tblorders')->where('id',$host_details->orderid)->first();
        $product_details= Capsule::table('tblproducts')->where('id',$host_details->packageid)->first();
        $group_details  = Capsule::table('tblproductgroups')->where('id',$product_details->gid)->first();
        $domain         = $host_details->domain;
        $package_name   = $product_details->name;
        $group_name     = $group_details->name;

        //project/geoup extra details
        return $project_details  = Capsule::table('project_extra_details')->where('project_id',$product_details->gid)->first();

        

        // href="http://{{{$tenant->finalDomain()}}}}{{{$tenant->project->dashboard_path}}}}"
        //  ($dashboard_details=$this->retrieveDashboardData($host_id,$product_details->gid,$host_details->packageid));
        // return $dashboard_username=$dashboard_details['dashboard_username'];
        // $dashboard_password=$dashboard_details['dashboard_password'];
        // $dashboard_url=$domain.$project_details->dashboard_path;
      // ==========================================================
      
      //==========================================================================
function retrieveDashboardData($tenant_id,$project_id,$package_id){

// echo 'cccccccccc';
    $custom_username=$this->retrieveCustomFeildValue('dashboard Username',$package_id,$tenant_id);
    $custom_password=$this->retrieveCustomFeildValue('dashboard Password',$package_id,$tenant_id);
    
    //here fetch username,password from project extra details table if no custom feild set for them
    $username = ($custom_username!=null)?$custom_username:fetchprojectExtraDetails($project_id)->dashboard_username;
    $password = ($custom_password!=null)?$custom_password:fetchprojectExtraDetails($project_id)->dashboard_password;

    return ['dashboard_username'=>$username,'dashboard_password'=>$password];
}
//==========================================================================
function retrieveCustomFeildValue($feild_name,$package_id,$tenant_id){
    // echo 'bbbbbbbbbbbbbbb';
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
?>


        <!------------------------start display dasboard details----------------------------------------
<div class="col-md-12">
    <div class="panel panel-default card mb-3" id="cPanelPackagePanel">
        <div class="panel-heading card-header">
            <h3 class="panel-title card-title m-0">Product Dashboard Details</h3>
        </div>
        <div style="padding: 0.75rem 1.25rem;">
                <div class="row">
                    <div class="col-sm-5">
                        {{{$LANG.dashboardUrl}}
                    </div>
                    <div class="col-sm-7">
                        {{$dashboard_url}}
                        <a href="http://{{$dashboard_url}}" target="_blank" class="btn btn-default btn-xs">{{$LANG.visitwebsite}}</a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-5">
                        {{$LANG.dashboardUsername}}
                    </div>
                    <div class="col-sm-7">
                        {{$dashboard_username}}
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-5">
                        {{$LANG.dashboardPassword}}
                    </div>
                    <div class="col-sm-7">
                        {{$dashboard_password}}
                    </div>
                </div>
                <hr>
        </div>
    </div>
</div>
            ------------------------end display dasboard details----------------------------------------->
