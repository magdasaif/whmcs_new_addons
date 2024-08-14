<?php
/**
 * WHMCS SDK Sample Addon Module
 *
 * An addon module allows you to add additional functionality to WHMCS. It
 * can provide both client and admin facing user interfaces, as well as
 * utilise hook functionality within WHMCS.
 *
 * This sample file demonstrates how an addon module for WHMCS should be
 * structured and exercises all supported functionality.
 *
 * Addon Modules are stored in the /modules/addons/ directory. The module
 * name you choose must be unique, and should be all lowercase, containing
 * only letters & numbers, always starting with a letter.
 *
 * Within the module itself, all functions must be prefixed with the module
 * filename, followed by an underscore, and then the function name. For this
 * example file, the filename is "superadminaddonmodule" and therefore all functions
 * begin "superadminaddonmodule_".
 *
 * For more information, please refer to the online documentation.
 *
 * @see https://developers.whmcs.com/addon-modules/
 *
 * @copyright Copyright (c) WHMCS Limited 2017
 * @license http://www.whmcs.com/license/ WHMCS Eula
 */

/**
 * Require any libraries needed for the module to function.
 * require_once __DIR__ . '/path/to/library/loader.php';
 *
 * Also, perform any initialization required by the service's library.
 */

use WHMCS\Database\Capsule;
use WHMCS\Module\Addon\superadminaddonmodule\Admin\AdminDispatcher;
use WHMCS\Module\Addon\superadminaddonmodule\Client\ClientDispatcher;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Define addon module configuration parameters.
 *
 * Includes a number of required system fields including name, description,
 * author, language and version.
 *
 * Also allows you to define any configuration parameters that should be
 * presented to the user when activating and configuring the module. These
 * values are then made available in all module function calls.
 *
 * Examples of each and their possible configuration parameters are provided in
 * the fields parameter below.
 *
 * @return array
 */
function superadminaddonmodule_config()
{
    return [
        
        'name'          => 'SuperAdmin Addon Module',// Display name for your module
        'description'   => 'This module provides an example WHMCS Addon Module'  . ' which can be used as a basis for building a custom addon module.',// Description displayed within the admin interface
        'author'        => 'magda',// Module author name
        'language'      => 'english',// Default language
        'version'       => '1.0',// Version number
        // 'fields' => [
            //     // a text field type allows for single line text input
            //     'tenant_id' => [
            //         'FriendlyName' => 'Tenant',
            //         'Type' => 'text',
            //         'Size' => '25',
            //         'Default' => 'Default value',
            //         'Description' => 'name for tenant (will be tenant_id)',
            //     ],
            //     // // a password field type allows for masked text input
            //     // 'Password Field Name' => [
            //     //     'FriendlyName' => 'Password Field Name',
            //     //     'Type' => 'password',
            //     //     'Size' => '25',
            //     //     'Default' => '',
            //     //     'Description' => 'Enter secret value here',
            //     // ],
            //     // // the yesno field type displays a single checkbox option
            //     // 'Checkbox Field Name' => [
            //     //     'FriendlyName' => 'Checkbox Field Name',
            //     //     'Type' => 'yesno',
            //     //     'Description' => 'Tick to enable',
            //     // ],
            //     // the dropdown field type renders a select menu of options
            //     'project_id' => [
            //         'FriendlyName' => 'Project',
            //         'Type' => 'dropdown',
            //         'Options' => [
            //             'option1' => 'Eradonline',
            //             'option2' => 'Sidalih',
            //             'option3' => 'Visitor',
            //         ],
            //         'Default' => 'option1',
            //         'Description' => 'Choose one project',
            //     ],
            //     // the radio field type displays a series of radio button options
            //     'deployment_project_type' => [
            //         'FriendlyName' => 'Deployment Type',
            //         'Type' => 'radio',
            //         'Options' => 'product,project',
            //         'Default' => 'product',
            //         'Description' => 'Choose your deployment_project_type!',
            //     ],
            //     // // the textarea field type allows for multi-line text input
            //     // 'Textarea Field Name' => [
            //     //     'FriendlyName' => 'Textarea Field Name',
            //     //     'Type' => 'textarea',
            //     //     'Rows' => '3',
            //     //     'Cols' => '60',
            //     //     'Default' => 'A default value goes here...',
            //     //     'Description' => 'Freeform multi-line text input field',
            //     // ],
        // ]
    ];
}

/**
 * Activate.
 *
 * Called upon activation of the module for the first time.
 * Use this function to perform any database and schema modifications
 * required by your module.
 *
 * This function is optional.
 *
 * @see https://developers.whmcs.com/advanced/db-interaction/
 *
 * @return array Optional success/failure message
 */
function superadminaddonmodule_activate(){
    // Create custom tables and schema required by your module
    try {
        //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
        //here we will create some new tables
        //project services table
        Capsule::schema()->create(
            'project_services',
            function ($table) {
                /** @var \Illuminate\Database\Schema\Blueprint $table */
                $table->increments('id');
                $table->integer('project_id')->comment('group_id');
                $table->string('name');
                //TODO, db_path [we put a copy from projects migrations to whmcs folders , or we will access project migration path direct]
                $table->string('db_path')->comment('path of migration files for this service in whmcs folders')->nullable();
                $table->boolean('active')->default(true);
            }
        );
        //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
        Capsule::schema() ->create(
            'project_extra_details',
            function ($table) {
                $table->increments('id');
                $table->integer('project_id')->comment('group_id');
                $table->string('central_domain')->comment('this is the base domain for each project used with tenant');
                $table->string('dashboard_path')->comment('path for project dashboard after /')->default('/dashboard'); 
                $table->boolean('allow_logo')->default(false)->comment('allow to upload logo when choose this project or no');
                $table->boolean('allow_color')->default(false)->comment('allow to choose site color when choose this project or no');

                // &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
                $table->string('base_dir')->comment('project BASE_DIR like (/home/murabba/public_html/${APPNAME})')->default('/dashboard');
                $table->float('npm_version')->comment('NPM_VERSION')->default('18');
                $table->string('php_version')->comment('php version used')->default('/usr/local/bin/ea-php82');
                $table->string('swagger_link')->comment('swagger link')->default('https://backend.eradonline.murabba.dev/api');
                $table->string('live_link')->comment('live link')->default('https://backend.eradonline.murabba.dev');
                // &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
                //mail configuration
                $table->string('mail_driver')->default('smtp')->comment('MAILMAILER');
                $table->string('mail_host')->default('sandbox.smtp.mailtrap.io');
                $table->string('mail_port')->default('587');
                $table->string('mail_username');
                $table->string('mail_password');
                $table->string('mail_encryption')->default('tls');
                $table->string('mail_from_address')->default('murabba.dev');
                $table->string('mail_from_name')->default('murabba');
                // &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
                $table->string('dashboard_username')->comment('username for accessing dashboard')->default('admin'); 
                $table->string('dashboard_password')->comment('password for accessing dashboard')->default('admin'); 
                // &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&

            }
        );
        //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
        Capsule::schema() ->create(
            'tenant_extra_details',
            function ($table) {
                /** @var \Illuminate\Database\Schema\Blueprint $table */
                // $table->increments('id');
                $table->string('tenant_id')->primary()->comment('host_id');
                $table->integer('order_id')->nullable()->comment('order_id');
                $table->integer('project_id')->nullable()->comment('group_id');
                $table->integer('package_id')->nullable()->comment('product_id');
                $table->enum('deployment_project_type',['product','project'])->comment('refer to type of project ("product or project") for deployment');
                $table->json('tenancy_db_names')->nullable()->comment('for extra data like multi dbs ');
                $table->json('tenancy_db_details')->nullable()->comment('contain service name and db path ');
                $table->longText('deployment_details')->nullable()->comment('contain .yml varaibles to used with pipeline ');
                // &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
                $table->integer('deploy_count')->default(0)->comment('number of deploy done for this tenant');
                // &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
            }
        );
        //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
        Capsule::schema() ->create(
            'package_extra_details',
            function ($table) {
                $table->increments('id');
                $table->integer('package_id')->nullable()->comment('product_id');
                $table->enum('deployment_type',['product','project'])->comment('refer to type of package ("product or project") for deployment');
            }
        );
         //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
         Capsule::schema() ->create(
            'projects_agents',
            function ($table) {
                $table->increments('id');
                $table->integer('project_id')->comment('group_id');
                $table->string('role');
                $table->string('username')->comment('project username/phone/email for this role');
                $table->string('password')->comment('project password for this role');
                $table->string('dashboard_path')->comment('path for project dashboard after / for this agent account')->default('/dashboard'); 
            }
        );
        //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

        return [
            // Supported values here include: success, error or info
            'status' => 'success',
            'description' => 'This is a demo module only. '
                . 'In a real module you might report a success or instruct a '
                    . 'user how to get started with it here.',
        ];
    }catch (\Exception $e) {
        return [
            // Supported values here include: success, error or info
            'status' => "error",
            'description' => 'Unable to create tenant_details: ' . $e->getMessage(),
        ];
    }
}

/**
 * Deactivate.
 *
 * Called upon deactivation of the module.
 * Use this function to undo any database and schema modifications
 * performed by your module.
 *
 * This function is optional.
 *
 * @see https://developers.whmcs.com/advanced/db-interaction/
 *
 * @return array Optional success/failure message
 */
function superadminaddonmodule_deactivate(){
    // Undo any database and schema modifications made by your module here
    try {
        //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
        //here we will create some new tables
        Capsule::schema()->dropIfExists('project_services');
        Capsule::schema()->dropIfExists('project_extra_details');
        Capsule::schema()->dropIfExists('tenant_extra_details');
        Capsule::schema()->dropIfExists('package_extra_details');
        Capsule::schema()->dropIfExists('projects_agents');
        //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
        return [
            // Supported values here include: success, error or info
            'status' => 'success',
            'description' => 'This is a demo module only. '
                . 'In a real module you might report a success here.',
        ];
    } catch (\Exception $e) {
        return [
            // Supported values here include: success, error or info
            "status" => "error",
            "description" => "Unable to drop tables: {$e->getMessage()}",
        ];
    }
}

/**
 * Upgrade.
 *
 * Called the first time the module is accessed following an update.
 * Use this function to perform any required database and schema modifications.
 *
 * This function is optional.
 *
 * @see https://laravel.com/docs/5.2/migrations
 *
 * @return void
 */
function superadminaddonmodule_upgrade($vars)
{
    $currentlyInstalledVersion = $vars['version'];

    /// Perform SQL schema changes required by the upgrade to version 1.1 of your module
    if ($currentlyInstalledVersion < 1.1) {
        $schema = Capsule::schema();
        // Alter the table and add a new text column called "demo2"
        // $schema->table('tenant_details', function($table) {
        //     $table->text('demo2');
        // });
    }

    /// Perform SQL schema changes required by the upgrade to version 1.2 of your module
    if ($currentlyInstalledVersion < 1.2) {
        $schema = Capsule::schema();
        // Alter the table and add a new text column called "demo3"
        // $schema->table('tenant_details', function($table) {
        //     $table->text('demo3');
        // });
    }
}

/**
 * Admin Area Output.
 *
 * Called when the addon module is accessed via the admin area.
 * Should return HTML output for display to the admin user.
 *
 * This function is optional.
 *
 * @see superadminaddonmodule\Admin\Controller::index()
 *
 * @return string
 */
function superadminaddonmodule_output($vars)
{
    // return 'addon index page';
    //   if ($_GET['action'] === 'editgroup') {
    //         // Redirect to your addon's form
    //         $groupId = (int)$_GET['group_id'];
    //         header('Location: addonmodules.php?module=superadminaddonmodule&action=create');
    //         exit;
    //         return 'after add group';
    //     }else{
    //         return 'add group else';
    //     }

    //=====================================================
    // Get common module parameters
    $modulelink     = $vars['modulelink']; // eg. superadminaddonmodules.php?module=superadminaddonmodule
    $version        = $vars['version']; // eg. 1.0
    $_lang          = $vars['_lang']; // an array of the currently loaded language variables

    //=====================================================
    // Get module configuration parameters
    // $configPasswordField = $vars['Password Field Name'];
    // $configCheckboxField = $vars['Checkbox Field Name'];
    // $configTextareaField = $vars['Textarea Field Name'];
    
    $tenant_id = $vars['tenant_id'];
    $project_id = $vars['project_id'];
    $deployment_project_type = $vars['deployment_project_type'];

    //=====================================================
    // Dispatch and handle request here. What follows is a demonstration of one
    // possible way of handling this using a very basic dispatcher implementation.

    // $action='create';
    // $_REQUEST['action']=$action;
    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

    $dispatcher = new AdminDispatcher();
    $response = $dispatcher->dispatch($action, $vars);
    echo $response;
}

/**
 * Admin Area Sidebar Output.
 *
 * Used to render output in the admin area sidebar.
 * This function is optional.
 *
 * @param array $vars
 *
 * @return string
 */
function superadminaddonmodule_sidebar($vars){
    //=====================================================
    // Get common module parameters
    $modulelink = $vars['modulelink'];
    $version    = $vars['version'];
    $_lang      = $vars['_lang'];
    //=====================================================
    // Get module configuration parameters
    // $configPasswordField = $vars['Password Field Name'];
    // $configCheckboxField = $vars['Checkbox Field Name'];
    // $configTextareaField = $vars['Textarea Field Name'];
    $configTextField        = $vars['tenant_id'];
    $configDropdownField    = $vars['project_id'];
    $configRadioField       = $vars['deployment_project_type'];
    //=====================================================
    //handle sidebae links
    $sidebar = '<ul class="menu">
        <li><p> project/group extra details </p></li>
        <li><a href="https://whmcs.murabba.dev/WMAA/addonmodules.php?module=superadminaddonmodule&action=products_extra_details">products/packages Extra Details</a></li>
        <hr>
        <li><a href="https://whmcs.murabba.dev/WMAA/addonmodules.php?module=superadminaddonmodule&action=all_project_service">Project Services</a></li>
        <li><a href="https://whmcs.murabba.dev/WMAA/addonmodules.php?module=superadminaddonmodule&action=projects_extra_details">projects Extra Details</a></li>

        <hr>
        </ul>';
        
        // <li><a href="https://whmcs.murabba.dev/WMAA/addonmodules.php?module=superadminaddonmodule&action=create_project_agents">Add Project Agents</a></li>
        // <li><a href="https://whmcs.murabba.dev/WMAA/addonmodules.php?module=superadminaddonmodule&action=create_project_service">Add project Service</a></li>
        // <li><a href="https://whmcs.murabba.dev/WMAA/addonmodules.php?module=superadminaddonmodule&action=create_project_extra_details">Add project Extra Details</a></li>
        // <li><a href="https://whmcs.murabba.dev/WMAA/addonmodules.php?module=superadminaddonmodule&action=project_mail_setting">projects Mail Setting</a></li>

    // $sidebar .= '<a href="https://whmcs.murabba.dev/register.php">Show All Tenant </a><br>';
    //=====================================================

    return $sidebar;
}

/**
 * Client Area Output.
 *
 * Called when the addon module is accessed via the client area.
 * Should return an array of output parameters.
 *
 * This function is optional.
 *
 * @see superadminaddonmodule\Client\Controller::index()
 *
 * @return array
 */
function superadminaddonmodule_clientarea($vars)
{
    // Get common module parameters
    $modulelink = $vars['modulelink']; // eg. index.php?m=superadminaddonmodule
    $version = $vars['version']; // eg. 1.0
    $_lang = $vars['_lang']; // an array of the currently loaded language variables

    // Get module configuration parameters
    $configTextField = $vars['tenant_id'];
    // $configPasswordField = $vars['Password Field Name'];
    // $configCheckboxField = $vars['Checkbox Field Name'];
    $configDropdownField = $vars['project_id'];
    $configRadioField = $vars['deployment_project_type'];
    // $configTextareaField = $vars['Textarea Field Name'];

    /**
     * Dispatch and handle request here. What follows is a demonstration of one
     * possible way of handling this using a very basic dispatcher implementation.
     */

    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

    $dispatcher = new ClientDispatcher();
    return $dispatcher->dispatch($action, $vars);
}
