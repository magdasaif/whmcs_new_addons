===============================================================================
WHMCS - new addon guide for superadmin management new part
===============================================================================

===============================================================================
[ in whmcs hook files ]
===============================================================================

new files that has been added are as follows:

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

===============================================================================
===============================================================================
[ new addons ]
===============================================================================

----