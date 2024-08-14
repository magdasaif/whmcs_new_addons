<!-- this file should be locate in /home/murabba/public_html/whmcs.murabba.dev/resources/views/superadmin/project_services/index.php -->
<?php if(isset($success)){?>
<div <?php if($success==1){echo'class="successbox"';}else{echo'class="errorbox"';}?>>
    <strong>
        <span class="title">Product/Package Extra Details</span>
    </strong><br>
    <?php echo $error;?>
</div>
<?}?>
<!-- ----------------------------------------------------------------------------------------------------------------- -->
<form method="post"
<?php
    echo 'action="https://whmcs.murabba.dev/WMAA/addonmodules.php?module=superadminaddonmodule&action=update_product_extra_details&id='.$id.'"';
?>
>
    <p><b>Extra Details</b></p>
    <table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
        <tbody>
            <tr>
                <td style="width: 500px;">Product/Package</td>
                <td class="fieldarea"><?php echo $product_name;?></td>
            </tr>
            <tr>
                <td style="width: 500px;">Deployment Type</td>
                <td class="fieldarea">
                    <select name="deployment_type" class="form-control select-inline" required>
                        <?php echo $deployment_type;?>
                    </select>
                </td>
            </tr>
        </tbody>
    </table>
    <input type="submit" class="btn btn-success" value="Update" />
</form>
