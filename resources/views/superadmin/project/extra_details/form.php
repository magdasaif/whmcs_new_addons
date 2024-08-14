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
    echo 'action="https://whmcs.murabba.dev/WMAA/addonmodules.php?module=superadminaddonmodule&action=update_project_extra_details&id='.$id.'"';
?>
>
    <p><b>Extra Details</b></p>
    <table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
        <tbody>
            <tr>
                <td style="width: 500px;">Project/Group</td>
                <td class="fieldarea"><?php echo $project_name;?></td>
            </tr>
            <tr>
                <td style="width: 500px;">Allow Logo</td>
                <td class="fieldarea">
                    <select name="allow_logo" class="form-control select-inline" required>
                        <?php echo $allow_logo_options;?>
                    </select>
                </td>
            </tr>
            <tr>
                <td style="width: 500px;">Allow Color</td>
                <td class="fieldarea">
                    <select name="allow_color" class="form-control select-inline" required>
                        <?php echo $allow_color_options;?>
                    </select>
                </td>
            </tr>
            <tr>
                <td style="width: 500px;">Central Domain</td>
                <td class="fieldarea"><input type="text" name="central_domain" value="<?php echo $project_details->central_domain;?>" class="form-control input-300" required></td>
            </tr>
            <tr>
                <td style="width: 500px;">project dashboard path</td>
                <td class="fieldarea"><input type="text" name="dashboard_path" value="<?php echo $project_details->dashboard_path;?>" class="form-control input-300" required></td>
            </tr>
            <tr>
                <td style="width: 500px;">project dashboard username</td>
                <td class="fieldarea"><input type="text" name="dashboard_username" value="<?php echo $project_details->dashboard_username;?>" class="form-control input-300" required></td>
            </tr>
            <tr>
                <td style="width: 500px;">project dashboard password</td>
                <td class="fieldarea"><input type="text" name="dashboard_password" value="<?php echo $project_details->dashboard_password;?>" class="form-control input-300" required></td>
            </tr>
            <tr>
                <td style="width: 500px;">project base dir</td>
                <td class="fieldarea"><input type="text" name="base_dir" value="<?php echo $project_details->base_dir;?>" class="form-control input-300" required></td>
            </tr>
            <tr>
                <td style="width: 500px;">project npm version</td>
                <td class="fieldarea"><input type="text" name="npm_version" value="<?php echo $project_details->npm_version;?>" class="form-control input-300" required></td>
            </tr>
            <tr>
                <td style="width: 500px;">project php version</td>
                <td class="fieldarea"><input type="text" name="php_version" value="<?php echo $project_details->php_version;?>" class="form-control input-300" required></td>
            </tr>
            <tr>
                <td style="width: 500px;">project live link</td>
                <td class="fieldarea"><input type="text" name="live_link" value="<?php echo $project_details->live_link;?>" class="form-control input-300" required></td>
            </tr>
            <tr>
                <td style="width: 500px;">project swagger link</td>
                <td class="fieldarea"><input type="text" name="swagger_link" value="<?php echo $project_details->swagger_link;?>" class="form-control input-300" required></td>
            </tr>

        </tbody>
    </table>
    <hr>
    <p><b>Mail Configuration</b></p>
    <table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
        <tbody>
            <tr>
                <td style="width: 500px;">Driver</td>
                <td class="fieldarea"><input type="text" name="mail_driver" value="<?php echo $project_details->mail_driver;?>" class="form-control input-300" required></td>
            </tr>
            <tr>
                <td style="width: 500px;">Host</td>
                <td class="fieldarea"><input type="text" name="mail_host" value="<?php echo $project_details->mail_host;?>" class="form-control input-300" required></td>
            </tr>
            <tr>
                <td style="width: 500px;">Port</td>
                <td class="fieldarea"><input type="text" name="mail_port" value="<?php echo $project_details->mail_port;?>" class="form-control input-300" required></td>
            </tr>
            <tr>
                <td style="width: 500px;">UserName</td>
                <td class="fieldarea"><input type="text" name="mail_username" value="<?php echo $project_details->mail_username;?>" class="form-control input-300" required></td>
            </tr>
            <tr>
                <td style="width: 500px;">Password</td>
                <td class="fieldarea"><input type="password" name="mail_password" value="<?php echo $project_details->mail_password;?>" class="form-control input-300" required></td>
            </tr>
            <tr>
                <td style="width: 500px;">Encryption</td>
                <td class="fieldarea"><input type="text" name="mail_encryption" value="<?php echo $project_details->mail_encryption;?>" class="form-control input-300" required></td>
            </tr>
            <tr>
                <td style="width: 500px;">From Address</td>
                <td class="fieldarea"><input type="text" name="mail_from_address" value="<?php echo $project_details->mail_from_address;?>" class="form-control input-300" required></td>
            </tr>
            <tr>
                <td style="width: 500px;">From Name</td>
                <td class="fieldarea"><input type="text" name="mail_from_name" value="<?php echo $project_details->mail_from_name;?>" class="form-control input-300" required></td>
            </tr>

        </tbody>
    </table>
    <input type="submit" class="btn btn-success" value="Update" />
</form>
