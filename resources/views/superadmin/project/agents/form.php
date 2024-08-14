<!-- this file should be locate in /home/murabba/public_html/whmcs.murabba.dev/resources/views/superadmin/project_services/index.php -->
<?php if(isset($success)){?>
<div <?php if($success==1){echo'class="successbox"';}else{echo'class="errorbox"';}?>>
    <strong>
        <span class="title">Project Extra Details</span>
    </strong><br>
    <?php echo $error;?>
</div>
<?}?>
<!-- ----------------------------------------------------------------------------------------------------------------- -->
<form method="post"
<?php
    echo 'action="https://whmcs.murabba.dev/WMAA/addonmodules.php?module=superadminaddonmodule&action=store_project_agent"';
?>
>

    <table class="form" width="100%" border="0" id="0" cellspacing="2" cellpadding="3">
        <tbody>
            <tr>
                <td width="130" class="fieldlabel">project</td>
                <td class="fieldarea"><?php echo $project_name;?><input type="hidden" name="project_id" value="<?php echo $project_id?>"></td>
            </tr>
        </tbody>
    </table>

    <hr>
    <p><b>Agents</b></p>

    <div id="agents">
        <div id="agent0" class="product">
            <table class="form" width="100%" border="0" id="0" cellspacing="2" cellpadding="3">
                <tbody>
                    <tr>
                        <td width="130" class="fieldlabel">Dashboard Role</td>
                        <td class="fieldarea"><input type="text" name="role[]" id="role0" value="" size="30" class="form-control" pattern="[a-zA-Z0-9,#.-]+" required></td>
                    </tr>
                    <tr>
                        <td width="130" class="fieldlabel">Dashboard Username</td>
                        <td class="fieldarea"><input type="text" name="username[]" id="username0" value="" size="30" class="form-control" required></td>
                    </tr>
                    <tr>
                        <td width="130" class="fieldlabel">Dashboard Password</td>
                        <td class="fieldarea"><input type="password" name="password[]" id="password0" value="" size="30" class="form-control" required></td>
                    </tr>
                    <tr>
                        <td width="130" class="fieldlabel">Dashboard path</td>
                        <td class="fieldarea"><input type="dashboard_path" name="dashboard_path[]" id="dashboard_path0" value="" size="30" class="form-control" required></td>
                    </tr>
                </tbody>
            </table>
        </div><hr>
    </div>

    <p style="padding:10px 0 5px 20px;"><a href="#" class="btn btn-default btn-sm addagent" onclick="javascript:addAgent();"><img src="images/icons/add.png" border="0" align="absmiddle"> Add Another Agent</a></p>

    <input type="submit" class="btn btn-success" value="Add" />
</form>

<script>
    // const element = document.getElementsByClassName("addagent");
    // element.addEventListener("click", myFunction);
    let i=0;
    function addAgent() {
        i++;            
        //append used to cread new div without clear previous div data
        $("#agents").append(' <div id="agent'+i+'" class="product"><table class="form" width="100%" border="0" id="'+i+'" cellspacing="2" cellpadding="3"><tbody> <tr><td width="130" class="fieldlabel">Dashboard Role</td><td class="fieldarea"><input type="text" name="role[]" id="role'+i+'" value="" size="30" class="form-control"></td></tr><tr><td width="130" class="fieldlabel">Dashboard Username</td><td class="fieldarea"><input type="text" name="username[]" id="username'+i+'" value="" size="30" class="form-control"></td></tr><tr><td width="130" class="fieldlabel">Dashboard Password</td><td class="fieldarea"><input type="password" name="password[]" id="password'+i+'" value="" size="30" class="form-control"></td></tr><tr><td width="130" class="fieldlabel">Dashboard path</td><td class="fieldarea"><input type="dashboard_path" name="dashboard_path[]" id="dashboard_path'+i+'" value="" size="30" class="form-control"></td></tr></tbody></table></div><hr>');

    }
    
</script>
