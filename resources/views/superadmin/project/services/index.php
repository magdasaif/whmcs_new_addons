<!-- this file should be locate in /home/murabba/public_html/whmcs.murabba.dev/resources/views/superadmin/project_services/index.php -->
<a style="margin-left: 85%;" href="https://whmcs.murabba.dev/WMAA/addonmodules.php?module=superadminaddonmodule&action=project_service">
    <input type="submit" value="Add project Service" class="btn btn-primary" tabindex="53">
</a>
<hr>

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

<table id="sortabletbl0" class="datatable" width="100%" border="0" cellspacing="1" cellpadding="3">
    <tbody>
        <tr>
            <th>project id</th>
            <th>service name</th>
            <th>service db path</th>
            <th>active</th>
            <th></th>
        </tr>
            <?php
            //=====================================================
                $services=Capsule::table('project_services')->get();
                // return $services;
                $trHtml='';
                // Loop through the $groups array and build the options HTML
                foreach ($services as $service) {
                    $project_name= Capsule::table('tblproductgroups')->where('id',$service->project_id)->select('name')->first()->name;

                   echo '<tr>
                        <td><a href="https://whmcs.murabba.dev/WMAA/configproducts.php?action=editgroup&ids='.$service->project_id.'">'.$project_name.'</a></td>
                        <td>'.$service->name.'</td>
                        <td>'.$service->db_path.'</td>';
                        echo ($service->active==1)?  '<td><span class="label active">Active</span></td>':  '<td><span class="label" style="background-color: #c51e1e;">UnActive</span></td>';
                        
                        // <a href="?module=superadminaddonmodule&action=edit_project_service&id='.$service->id.'">
                        //     <img src="images/edit.gif" border="0" alt="Edit">
                        // </a>

                    echo '
                    <td style="width: 2%; background-color:#f3f3f3;" align="center">
                        <a href="?module=superadminaddonmodule&action=project_service&id='.$service->id.'">
                            <img src="images/edit.gif" border="0" alt="Edit">
                        </a>
                    </td>
                    </tr>';
                }
            //=====================================================
            ?>
        </tbody>
</table>