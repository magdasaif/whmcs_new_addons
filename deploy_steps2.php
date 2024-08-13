<?php
//this file will be located in whmcs includes/hooks folders
// use WHMCS\Database\Capsule;

// if (!defined('WHMCS'))
// die('You cannot access this file directly.');

//::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
// return executeGitCommand("pwd");
    $orderid='69';
    //third way to handle dbs
    //=======================================================================
    //here we will use external repo to handle pipeline using .yml files
    //so , we need to handle tenant deployment data
    $file_content   = handleDeploymentVariables($orderid)['content'];
    $username       = handleDeploymentVariables($orderid)['username'];
    //=======================================================================
    //then , put thid data in variable.yml file 
    $file_data=handleVaraibleFile($file_content,$username);
    //=======================================================================
    //finally , we need to push this file to deployment repo
   return handlePushToRepo($file_data['user_branch'],$file_data['file']);
//::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

//==========================================================================
function handleDeploymentVariables($order_id){

    //=======================================================
        $data='new content will be here';
        $user='magda69';
        return ['content'=>$data,'username'=>$user];
    //=======================================================
}
//==========================================================================
function handleVaraibleFile($yamlContent,$user){
    //===================================================================
    $root_path      = '/home/murabba/public_html/whmcs.murabba.dev';
    $repoPath       = $root_path.'/deployment_test';
    echo $repoPath;
    chdir($repoPath);
//    return executeGitCommand("pwd");
    //===================================================================
//    executeGitCommand("git checkout dev");
    executeGitCommand("git checkout ".$user);
    //===================================================================
    $file_directory = $repoPath.'/'.$user;
    $file_name      = 'variable.yml';
    $file_path      = $file_directory.'/'.$file_name;
    //===================================================================
    checkExistDirectoty($file_directory);
    checkFileExist($file_path);
    //===================================================================
    $yamlContent = str_replace('\r\n', "\n", $yamlContent);
    file_put_contents($file_path, $yamlContent);
    //===================================================================
    return ['user_branch'=>$user,'file'=>$file_path];
}
##============================== check Exist Directoty ==================
function checkExistDirectoty($directory) {
    // if (!File::exists($directory))
    if (!file_exists($directory))
    {
        try {
            #if not exist create new folder for all model it  and allow permission 
            // File::makeDirectory($directory, 0755, true, true);
            mkdir($directory, 0777, true);

        } catch (\Exception $e) {
            // Capsule::table('tblerrorlog')->insert(['severity' => 'file error', 'message'=>"Could not create directory:". $directory,'details' => $e->getMessage()]);

            // $this->error("Could not create directory: $directory");
            // $this->info("Please check that the parent directory exists and is writable.");
            return;
        }
    }
}    
##============================== check Exist File =======================
function checkFileExist($file_path) {
    try {
        #if not exist create new folder for all model it  and allow permission 
        if (!file_exists($file_path)) {
            touch($file_path);
        }
    } catch (\Exception $e) {
        // Capsule::table('tblerrorlog')->insert(['severity' => 'file error', 'message'=>"Could not create file:". $file_path,'details' => $e->getMessage()]);

        // $this->error("Could not create file: $file_path");
        // $this->info("Please check that the parent directory exists and is writable.");
        return;
    }
    
}
##=========================== Execte git commands =======================
function executeGitCommand($command){
    $output = null;
    $exitCode = null;

    try {
        // Execute the Git command
        exec($command, $output, $exitCode);

        // Log the output for debugging
        // Capsule::table('tblerrorlog')->insert([
        //     'severity' => 'Git command output',
        //     'message'  => $command,
        //     // 'details'  => implode(PHP_EOL, $output)
        //     'details'  => $exitCode .'-'. json_encode($output)

        // ]);

        // Check if the command encountered an error
        if ($exitCode !== 0) {
            throw new \Exception("Git command failed with exit code: $exitCode");
        }

        // Return the output as an array of lines
        print_r($output);
    } catch (\Exception $e) {
        // Log the exception
        // Capsule::table('tblerrorlog')->insert([
        //     'severity' => 'Git command error',
        //     'message' => $command,
        //     'details' => $e->getMessage()
        // ]);

        // Handle the error as needed
        // For example, you can throw a new exception, log an error, or return a specific value
        throw $e;
    }
}
##=========================== push .yml to delpoy repo ==================
function handlePushToRepo($user_branch,$file) {
    //===================================================================
    $root_path      = '/home/murabba/public_html/whmcs.murabba.dev';//$_SERVER['DOCUMENT_ROOT'];
    $repoPath       = $root_path.'/deployment_test';
    chdir($repoPath);
    //===================================================================
    executeGitCommand("git checkout ".$user_branch);
    executeGitCommand("git add .");
    executeGitCommand('git commit -m "Push new branch with new content"');
    executeGitCommand("git push --set-upstream origin ".$user_branch);
    //===================================================================
    return 'done';
}
