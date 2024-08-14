<!-- this file should be locate in /home/murabba/public_html/whmcs.murabba.dev/resources/views/superadmin/project/extra_details/index.php -->
<?php
    use WHMCS\Database\Capsule;
    echo '<a style="margin-left: 85%;" href="https://whmcs.murabba.dev/WMAA/addonmodules.php?module=superadminaddonmodule&action=create_project_agents&id='.$project_id.'">
        <input type="submit" value="Add project Agents" class="btn btn-primary" tabindex="53"></a>
        <hr>
    ';

if(isset($success)){?>
<div <?php if($success==1){echo'class="successbox"';}else{echo'class="errorbox"';}?>>
    <strong>
        <span class="title">Project Agents </span>
    </strong><br>
    <?php echo $error;?>
</div>
<?}?>

<table id="sortabletbl0" class="datatable" width="100%" border="0" cellspacing="1" cellpadding="3">
    <tbody>
        <tr>
            <th>Role</th>
            <th>Dashboard Path</th>
            <th>Username</th>
            <th>Password</th>
            <th></th>
        </tr>
        <?php
        //=====================================================
        foreach ($agents as $agent) {

            echo '<tr> 
            <td>'.$agent->role.'</td>
            <td>'.$agent->dashboard_path.'</td>
            <td>'.$agent->username.'</td>
            <td>'.$agent->password.'</td>';
            echo'
            <td style="width: 6%; background-color:#f3f3f3;" align="center">
                <a href="?module=superadminaddonmodule&action=edit_project_agents&id='.$agent->id.'&project_id='.$project_id.'">
                    <i class="fa fa-edit"></i>
                </a>
                <a href="?module=superadminaddonmodule&action=delete_project_agents&id='.$agent->id.'&project_id='.$project_id.'">
                    <i class="fa fa-trash"></i>
                </a>
            </td>

            </tr>';
        }
        //=====================================================
        ?>
    </tbody>
</table>