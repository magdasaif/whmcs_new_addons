WHMCS - new addon guide for superadmin management new part
=
---------------------------------------------------------------------------------------------
[ in whmcs hook files ]
=
new files has been added are as follows:

1. action_after_accept_order.php

    this file used to handle any action must be done after order or subscription accepted
    - store in log table
    - handle deployment valiable .yml file with requested project details for client/tenant
    - push this file in remote repo in new branch related to this tenant to handle pipeline based on it later 
    - handle project dashboard admin data in new file and push it too in same repo to be used with seeder data in new tenant part 
    - update dashboard url with the help of domain

2. action_after_shopping_cart_checkout.php

    this file used to save tenant extra details in new tables after tenant subscripe to project
      - handle customfield for this tenant and this project to be used in client area project details


---------------------------------------------------------------------------------------------
-       super-admin     ------------------              whmcs                               -
- user                  ------------------   client                   (tblclients)          -
- project               ------------------   product groups           (tblproductgroups)    - 
- package               ------------------   products                 (tblproducts)         -
- package_durations     ------------------   billing cycles ,prices   (tblpricing)          -
- package_features      ------------------   product description      (tblproducts)         -
- tenant                ------------------   services                 (tblhosting)          -
- add subscription      ------------------   add order                (tblorders)           -
- accept subscription   ------------------   accept order             (tblorders)           -
---------------------------------------------------------------------------------------------



- we will add product with correct server module
if we need to add product(package) as project deployment type ---> we will select cpanel as server module 
if we need to add product(package) as product(sass) deployment type ---> we will select new module(super-admin) as server module

- after order created we will handle client database details in tenant_extra_details table 
, handle db and its migration based on stored details related to db , and if package deployment type project --> upload project folder into new domain
, and send mail with project details
 
---------------------------------------------------------------------------------------------

- we need to create new project_services table and insert all services inside it , (or handle new addon to insert them)
this table content will be returned after group added to select service or more from them.


- after adding project(group) we need to , redirect to new form to insert project extra details
(
    all services and its path to help in migration - dashboard path - central domain to help with domain - 
    allow_logo - allow_color -
)
---------------------------------------------------------------------------------------------

link that open add group form
=

https://whmcs.murabba.dev/WMAA/configproducts.php?action=creategroup

---------------------------------------------------------------------------------------------

redirect link after add grop
=
https://whmcs.murabba.dev/WMAA/configproducts.php?action=editgroup&ids=5&success=true

---------------------------------------------------------------------------------------------

[new addon details will be found in addon readme.md file]
=