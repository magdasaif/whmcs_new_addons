create new provisioning module
=

A module is a collection of functions that provide additional functionality to the WHMCS platform, most commonly used to integrate with third party services and APIs.

The first step in creating a module for WHMCS is determining which type of module you wish to create

* download selected module repo from  https://github.com/WHMCS/sample-provisioning-module

* after that , handle module new name in all module files 

* then ,upload module files in selected module type directory 

---

1-i download  https://github.com/WHMCS/sample-provisioning-module

2-and take copy of modules/servers/provisioningmodule folder 

3-rename folder with new module name (superadminmodule)

4-rename modules/servers/provisioningmodule/provisioningmodule.php name to be  superadminmodule.php

5-replace all old module name in  superadminmodule.php file with superadminmodule

    [ex:- function provisioningmodule_ConfigOptions()  → function superadminmodule_ConfigOptions()  ]

6-change module name also in hooks.php file

7-save changes 

8-make sure new module folder locate in modules/servers directory


[ main functionality for this addon ]
=
    1- change in superadminmodule_ClientArea function to retrieve extra details for selected package to be displayed in client area
    2- handle new template files that will be displayed [
        templates/manage.tpl
        templates/productextradetails.tpl
    ]
    3- display superadmin Services in client primary navbar in client area