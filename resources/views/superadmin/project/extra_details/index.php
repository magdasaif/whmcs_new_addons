<!-- this file should be locate in /home/murabba/public_html/whmcs.murabba.dev/resources/views/superadmin/project/extra_details/index.php -->
<?php
use WHMCS\Database\Capsule;

if(isset($success)){?>
<div <?php if($success==1){echo'class="successbox"';}else{echo'class="errorbox"';}?>>
    <strong>
        <span class="title">Project Services </span>
    </strong><br>
    <?php echo $error;?>
</div>
<?}?>

<br>
<table id="sortabletbl0" class="datatable" width="100%" border="0" cellspacing="1" cellpadding="3">
    <tbody>
        <tr>
            <th>project name</th>
            <th>central domain</th>
            <th>dashboard path</th>
            <th>allow logo</th>
            <th>allow color</th>
            <th></th>
        </tr>
        <?php
        //=====================================================
        //when display all projects from tblproductgroups
        $projects=Capsule::table('tblproductgroups')->get();
        // Loop through the $projects array and build the options HTML
        foreach ($projects as $project) {
            $project_details= Capsule::table('project_extra_details')->where('project_id',$project->id)->first();

            echo '<tr>
                <td><a href="https://whmcs.murabba.dev/WMAA/configproducts.php?action=editgroup&ids='.$project->project_id.'">'.$project->name.'</a></td>
                <td>';
            echo    isset($project_details)?$project_details->central_domain:'-';
            echo'</td><td>';
            echo    isset($project_details)?$project_details->dashboard_path:'-';
            echo'</td>';
            echo    isset($project_details)?(($project_details->allow_logo==1)?'<td><span class="label active">Allow</span></td>':'<td><span class="label" style="background-color: #c51e1e;">Not Allow</span></td>'):'<td><span class="label" style="background-color: #c51e1e;">Not Allow</span></td>';
            echo    isset($project_details)?(($project_details->allow_color==1)?'<td><span class="label active">Allow</span></td>':'<td><span class="label" style="background-color: #c51e1e;">Not Allow</span></td>'):'<td><span class="label" style="background-color: #c51e1e;">Not Allow</span></td>';
            echo'
            <td style="width: 6%; background-color:#f3f3f3;" align="center">
                <a href="?module=superadminaddonmodule&action=edit_project_extra_details&id='.$project->id.'">
                    <img src="images/edit.gif" border="0" alt="Edit">
                </a>
                <a href="?module=superadminaddonmodule&action=project_agents&id='.$project->id.'">
                    <i class="fa fa-user"></i>
                </a>
            </td>

            </tr>';
        }
        //=====================================================
        ?>
    </tbody>
</table>