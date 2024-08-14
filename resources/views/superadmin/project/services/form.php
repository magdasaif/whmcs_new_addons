<?php if(isset($success)){?>
<div <?php if($success==1){echo'class="successbox"';}else{echo'class="errorbox"';}?>>
    <strong>
        <span class="title">Project Service</span>
    </strong><br>
    <?php echo $error;?>
</div>
<?}?>
<!-- ----------------------------------------------------------------------------------------------------------------- -->
<form method="post" 
<?php
if(isset($edit) && $edit==1){
    echo 'action="https://whmcs.murabba.dev/WMAA/addonmodules.php?module=superadminaddonmodule&action=handle_project_service&id='.$service_details->id.'"';
}else{
    echo 'action="https://whmcs.murabba.dev/WMAA/addonmodules.php?module=superadminaddonmodule&action=handle_project_service&id=0"';
}
?>
>
    <table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
        <tbody>
            <tr>
                <td class="fieldlabel">Project/group</td>
                <td class="fieldarea">
                    <select name="project_id" class="form-control select-inline" required>
                        <?php
                            foreach ($groups as $group) {
                               echo '<option value="' . $group->id . '"';
                               if($service_details->project_id==$group->id){echo'selected';}else{echo'';}
                               echo'>' . $group->name . '</option>';
                            }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="fieldlabel">active</td>
                <td class="fieldarea">
                    <select name="active" class="form-control select-inline" required>
                        <option value="1" <?php if($service_details->active==1){echo'selected';}else{echo'';}?>>Active</option>
                        <option value="0" <?php if($service_details->active==0){echo'selected';}else{echo'';}?>>UnActive</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="fieldlabel">project service name</td>
                <td class="fieldarea"><input type="text" name="service_name" value="<?php echo ($service_details->name)??'';?>" class="form-control input-300" pattern="[a-zA-Z0-9,#.-]+" required></td>
            </tr>
            <tr>
                <td class="fieldlabel">project service db path</td>
                <td class="fieldarea"><input type="text" name="db_path" value="<?php echo($service_details->db_path)??'';?>" class="form-control input-300" required><span style="color:red">path of service database migration files</span></td>
            </tr>
        </tbody>
    </table>
    <input type="submit" class="btn btn-success" value="Save service" />
</form>
