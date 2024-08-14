<!-- this file should be locate in /home/murabba/public_html/whmcs.murabba.dev/resources/views/superadmin/project/extra_details/index.php -->
<?php
use WHMCS\Database\Capsule;

if(isset($success)){?>
<div <?php if($success==1){echo'class="successbox"';}else{echo'class="errorbox"';}?>>
    <strong>
        <span class="title">Product/Package Extra Details </span>
    </strong><br>
    <?php echo $error;?>
</div>
<?}?>

<br>
<table id="sortabletbl0" class="datatable" width="100%" border="0" cellspacing="1" cellpadding="3">
    <tbody>
        <tr>
            <th>group/project</th>
            <th>product/Package</th>
            <th>deployment type</th>
            <th></th>
        </tr>
        <?php
        //=====================================================
        //when display all projects from tblproductgroups
        $products=Capsule::table('tblproducts')->get();
        // Loop through the $products array and build the options HTML
        foreach ($products as $product) {
            $group_details      = Capsule::table('tblproductgroups')->where('id',$product->gid)->first();
            $product_details    = Capsule::table('package_extra_details')->where('package_id',$product->id)->first();

            echo '<tr>
                <td><a href="https://whmcs.murabba.dev/WMAA/configproducts.php?action=editgroup&ids='.$group_details->id.'">'.$group_details->name.'</a></td>
                <td>';
                echo $product->name;
                echo'</td><td>';
                echo    isset($product_details)?$product_details->deployment_type:'-';
                echo'</td>';
           echo'
            <td style="width: 6%; background-color:#f3f3f3;" align="center">
                <a href="?module=superadminaddonmodule&action=edit_product_extra_details&id='.$product->id.'">
                    <img src="images/edit.gif" border="0" alt="Edit">
                </a>
            </td>
            </tr>';
        }
        //=====================================================
        ?>
    </tbody>
</table>