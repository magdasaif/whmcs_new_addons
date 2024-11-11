
create new addon module 
=

i download  https://github.com/WHMCS/sample-addon-module

and take copy of modules/addons/addonmodule folder 

rename folder with new module name (superadminmodule)

rename modules/addons/addonmodule/addonmodule.php name to be  superadminmodule.php

replace all old module name in  superadminmodule.php file with superadminmodule

[ex:- function addonmodule_config()  → functions superadminaddonmodule_config()  ]

handle extra implmentation in hooks.php file

save changes 

make sure new module folder locate in modules/addons directory
=
---
[ main functionality for this addon ]
=
    1- handle AfterModuleCreate hook action to configure tenant dbs and project files if cpanel server choosen
    2- after activate addon, new tables created like[
        project_services        --> that will store all services for available project
        project_extra_details   --> that will store extra details for project as dashboard data , mail informations, npm version,central domain
        tenant_extra_details    --> that will store extra details for tenant as db names, deployment_type ,selected project and package
        package_extra_details   --> that will store deployment_type for each package (product)
        projects_agents         --> that will store project dashboard agents data to be used later in pipeline 
    ]
    3- after deactivate addon, added tables will be removed
    4- handle sidebar links for new tables 
    5- handle necessary function to manage new tables data in modules/addons/superadminaddonmodule/lib/Admin/Controller.php
        like 
        [
            all_project_service
            project_service
            handle_project_service
            projects_extra_details
            edit_project_extra_details
            update_project_extra_details
            products_extra_details
            edit_product_extra_details
            update_product_extra_details
            project_agents
            create_project_agents
            store_project_agent
            edit_project_agents
            update_project_agents
            delete_project_agents
            generateSelect
            storeInActiveLog
            currentAdmin
            currentUser
            currentClient
        ]
    6- blade views for those links/forms will be located in resource root directory in resources/views/superadmin
