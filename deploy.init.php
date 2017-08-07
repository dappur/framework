<?php
namespace Dappur\Dappurware;

/**
 * This is the Git Auto-Deployment class for the Dappur Framework.  This class
 * provides usage for checking for all necessary dependencies and automatically
 * installing them.  This is meant to deploy a Dappur framework project, but I
 * don't see why it couldn't be used to deply frameworks with similar structures
 * (Laravel, etc.)
 *
 * This is very much PRE-ALPHA as there is much more work that needs to be done,
 * but it works on my test web server running Ubuntu 16.04, Apache and Nginx.
 * Please do not expect much from this module yet.
 */

class Deployment {

    // Git Repository URL
    protected $repo_url;
    // Web root directory on server
    protected $document_root;
    // User Home Directory
    protected $user_home;
    // Settings Array
    protected $settings_array;
    // Repository Deirector on Server
    protected $repo_dir;
    // Full path to git binary is required if git is not in your PHP user's path. Otherwise just use 'git'.
    protected $git_bin_path; 
    // Log File
    protected $log_file;
    // Deployment Cert File (Full path the the deployment RSA key's *.pub file)
    protected $cert_folder;
    // Deployment Cert File (Full path the the deployment RSA key's *.pub file)
    protected $cert_file_name;
    // Set high timeout limit due to composer and git sometimes taking longer than default.
    protected $timelimit;
    
    /**
     * { function_description }
     *
     * @param      string   $repo_url        The repo url
     * @param      string   $document_root   The document root
     * @param      string   $user_home       The user home
     * @param      array    $settings_array  The settings array
     * @param      string   $repo_dir        The repo dir
     * @param      string   $git_bin_path    The git bin path
     * @param      string   $log_file        The log file
     * @param      string   $cert_folder     The cert folder
     * @param      string   $cert_file_name  The cert file name
     * @param      integer  $timelimit       The timelimit
     */
    public function __construct(
                        $repo_url = null,
                        $document_root = null, 
                        $user_home = null,
                        $settings_array = array(), 
                        $repo_dir = null, 
                        $git_bin_path = null, 
                        $log_file = null, 
                        $cert_folder = null, 
                        $cert_file_name = null,  
                        $timelimit = 300){

        // Validate and set default values
        if (is_null($document_root)) {
            die('DOCUMENT_ROOT is required');
        }
        if (is_null($document_root)) {
            die('USER_HOME is required');
        }
        if (is_null($repo_dir)) {
            $repo_dir = dirname($document_root) . '/repo';
        }
        if (is_null($repo_url)) {
            die('Repository URL is required');
        }
        if (is_null($git_bin_path)) {
            $git_bin_path = 'git';
        }
        if (is_null($log_file)) {
            $log_timestamp = date("YmdGis");
            $log_file = dirname($document_root) . '/storage/log/deployment/deployment-'.$log_timestamp.'.log';
        }

        if (is_null($cert_folder)) {
            $cert_folder = dirname($document_root) . '/storage/certs/deployment';
        }

        if (is_null($cert_path)) {
            $cert_file_name = 'deploy';
        }

        // Set Class Variables
        $this->repo_url = $repo_url;
        $this->git_bin_path = $git_bin_path;
        $this->document_root = $document_root;
        $this->log_file = $log_file;
        $this->repo_dir = $repo_dir;
        $this->user_home = $user_home;
        $this->settings_array = $settings_array;
        $this->cert_folder = $cert_folder;
        $this->cert_file_name = $cert_file_name;
        set_time_limit($timelimit);

    }


    //
    // Execute
    //
    public function execute(){
        $this->validateDocumentRoot();
        $this->installUpdateComposer();
        $this->checkGit();
        $this->checkInstallRepo();
    }

    public function initDappur(){
        $this->checkSettings();
        $this->updateSettings();
        $this->checkPhinx();
        $this->migrateUp();

    }

    public function updateDappur(){
        $this->checkPhinx();
        $this->migrateUp();
    }

    //
    // Make sure PHP user has all appropriate permissions and that server
    // structure is correct
    //
    private function validateDocumentRoot(){
        
        // Check that the DOCUMENT_ROOT is a directory called `public`.
        if (end(explode('\\', $this->document_root)) != "public" && end(explode('/', $this->document_root)) != "public"){
            die($this->logEntry("Your servers DOCUMENT_ROOT needs to be a directory called `public`"));
        }

        // Check that the DOCUMENT_ROOT parent directory is writable.
        if (!is_writable(dirname($this->document_root))) {
            die($this->logEntry("Web server user does not have access to the DOCUMENT_ROOT's parent directory. This is required in order for Dappur to function properly."));
        }
    }

    //
    // Check if composer is installed or download phar and use that.
    //
    private function installUpdateComposer(){

        if (!is_file(dirname($this->document_root) . '/composer.phar')) {
            // Download composer to the DOCUMENT_ROOT's parent directory.
            if(file_put_contents(dirname($this->document_root) . '/composer.phar', fopen("https://getcomposer.org/download/1.4.2/composer.phar", 'r'))){
                echo $this->logEntry("Composer downloaded successfully. Making composer.phar executable...");
                // CD into DOCUMENT_ROOT parent and make composer.phar executable
                exec("cd " . dirname($this->document_root) . " && chmod +x composer.phar");
            }else{
                echo $this->logEntry("Could not get Composer working.  Please check your settings and try again.");
            }
        }else{
            // Check that composer is working
            $check_composer = shell_exec(dirname($this->document_root) . "/composer.phar" . ' --version 2>&1');
            echo $this->logEntry($check_composer);
            if (strpos($check_composer, 'omposer version')){
                // Check for Composer updates
                $update_composer = shell_exec(dirname($this->document_root) . "/composer.phar self-update 2>&1");
                echo $this->logEntry("Checking For Composer Update...");
                echo $this->logEntry($update_composer);
            }
        }

    }

    private function checkGit(){
        // Check that git is installed
        $check_git = shell_exec($this->git_bin_path . " --version");
        echo $this->logEntry($check_git);
        if(!strpos($check_git, 'it version ')){

            die($this->logEntry("<pre>Git is required in order for auto deployment to work.  Please check the deploy.init.log for errors.</pre>"));
        }
    }

    private function checkInstallRepo(){

        // Create repository directory if it doesnt exist.
        if (!is_dir($this->repo_dir)) {
            echo $this->logEntry("Repository directory does not exist. Creating it now...");
            if(!mkdir($this->repo_dir)){
                die($this->logEntry("There was an error creating the repository directory.  Please check your PHP user's permissions and try again."));
            }else{
                echo $this->logEntry("Repository directory created successfully.");
                // Initialize and prepare the git repository
                $this->initializeRepository();
                //$this->updateRepository();
            }
        }else{
            if (!is_file($this->repo_dir . '/config')) {
                echo $this->logEntry("Repository doesn't exist.  Attempting to create now...");
                // Initialize and prepare the git repository
                $this->initializeRepository();

                // Update the repository
                $this->updateRepository();

                // Update composer
                $this->updateComposer();
            }else{

                // Update the repository.
                $this->updateRepository();

                // Update composer
                $this->updateComposer();
            }
        }
    }

    private function initializeRepository(){

        // Check Known Hosts file for github.com and add using ssh-keyscan
        if (is_file('~/.ssh/known_hosts')){
            $known_hosts = file_get_contents('~/.ssh/known_hosts');
            if (!strpos($known_hosts, "github.com")) {
                // Add Github to the known hosts
                $add_github = shell_exec('ssh-keyscan github.com >> ~/.ssh/known_hosts');
                $this->logEntry($add_github);
            }
        }else{
            // Add Github to the known hosts
            $add_github = shell_exec('ssh-keyscan github.com >> ~/.ssh/known_hosts');
        }

        // Create the Mirror Repository
        $create_repo_mirror = shell_exec('cd ' . $this->repo_dir . ' && ' . $this->git_bin_path  . ' clone --mirror ' . $this->repo_url . " . 2>&1");
        echo $this->logEntry($create_repo_mirror);
        if (strpos($create_repo_mirror, 'ermission denied (publickey)')) {
            echo $this->logEntry("Access denied to repository... Creating deployment key now...");
            die($this->logEntry("Please add the following public key between the dashes to your deployment keys for the repository and run this script again.\n" . str_repeat("-", 80) . "\n" . $this->getDeployKey() . "\n" . str_repeat("-", 80)));
        }

        // Do the initial checkout
        $git_checkout = shell_exec('cd ' . $this->repo_dir . ' && GIT_WORK_TREE=' . dirname($this->document_root) . ' ' . $this->git_bin_path  . ' checkout -f 2>&1');
        echo $this->logEntry($git_checkout);
        // Get the deployment commit hash
        $commit_hash = shell_exec('cd ' . $this->repo_dir . ' && ' . $this->git_bin_path  . ' rev-parse --short HEAD 2>&1');
        echo $this->logEntry("Deployed Commit: " . $commit_hash);
    }

    private function updateRepository(){

        // Fetch any new changes
        $git_fetch = shell_exec('cd ' . $this->repo_dir . ' && ' . $this->git_bin_path  . ' fetch 2>&1');
        if (empty($git_fetch)) {
            die($this->logEntry("There is nothing new to fetch from this repository."));
        }else{
            echo $this->logEntry($git_fetch);
            // Do the checkout
            shell_exec('cd ' . $this->repo_dir . ' && GIT_WORK_TREE=' . dirname($this->document_root) . ' ' . $this->git_bin_path  . ' checkout -f 2>&1');
            // Get the deployment commit hash
            $commit_hash = shell_exec('cd ' . $this->repo_dir . ' && ' . $this->git_bin_path  . ' rev-parse --short HEAD 2>&1');
            echo $this->logEntry("Deployed Commit: " . $commit_hash);
        }

        

    }

    private function updateComposer(){

        $update_composer = shell_exec('cd ' . dirname($this->document_root) . ' && ' . dirname($this->document_root) . '/composer.phar install 2>&1');
        echo $this->logEntry($update_composer);
        if (!strpos($update_composer, 'Generating autoload files')) {
            echo $this->logEntry("An error might have occured while updating composer.  Please check the deployment log to confirm.");
        }else{
            echo $this->logEntry("Composer updated successfully!");
        }
    }

    private function getDeployKey(){
        // Create certificate folder if it does not exist
        if (!is_dir($this->cert_folder)) {
            mkdir($this->cert_folder, 0755, true);
        }

        if (file_exists($this->cert_folder . '/' . $this->cert_file_name) && file_exists($this->cert_folder . '/' . $this->cert_file_name . ".pub")) {
            return file_get_contents($this->cert_folder . '/' . $this->cert_file_name . ".pub");
        } else if (!file_exists($this->cert_folder . '/' . $this->cert_file_name)) {
            // Create the deploy key with ssh-keygen
            $generate_key = shell_exec("ssh-keygen -q -N '' -t rsa -b 4096 -f " . $this->cert_folder . "/" . $this->cert_file_name);
            echo $this->logEntry($generate_key);
            // Get the contents of the public key
            $public_key = file_get_contents($this->cert_folder . '/' . $this->cert_file_name . '.pub');
            // Install the deploy key if not already done
            $this->installDeployKey();

            // Return the public key
            return $public_key;
        
        }else{
            // Install the deploy key if not already done
            $this->installDeployKey();

            // Return the public key
            return file_get_contents($this->cert_folder . '/' . $this->cert_file_name . '.pub');
        }


    }

    private function installDeployKey(){
        // Check that the user home has a .ssh folder. If not, then create it.
        if (!is_dir($this->user_home . '/.ssh')) {
            mkdir($this->user_home . '/.ssh', 0700, true);
        }

        // Check if the ssh config file exists.  If not, then create it
        if (!file_exists($this->user_home . '/.ssh/config')) {
            touch($this->user_home . '/.ssh/config');
        }

        // Check for and add the deploy key to the ssh config file
        $ssh_config = file_get_contents($this->user_home . '/.ssh/config');
        if (!strpos($ssh_config, $this->cert_file_name)) {
            file_put_contents($this->user_home . '/.ssh/config', "IdentityFile " . $this->cert_folder . '/' . $this->cert_file_name . "\n", FILE_APPEND);
        }

    }

    private function checkSettings(){

        // Check that the user home has a settings.json file. If not, then create it.
        if (!is_dir(dirname($this->document_root) . '/app/bootstrap')) {
            echo $this->logEntry("Creating bootstrap folder for settings...");
            mkdir(dirname($this->document_root) . '/app/bootstrap', 0755, true);
        }
        if (!is_file(dirname($this->document_root) . '/app/bootstrap/settings.json')) {
            
            $this->logEntry("Dappur settings.json not found.  Creating now...");
            //Get current settings.json from github
            $this->logEntry("Downloading current settings.dist.json file from Github and cloning to app/bootstrap/settings.json");
            $settings_file = file_get_contents("https://raw.githubusercontent.com/dappur/framework/master/app/bootstrap/settings.dist.json");

            file_put_contents(dirname($this->document_root) . '/app/bootstrap/settings.json', "$settings_file");
        }else{
            $settings = file_get_contents(dirname($this->document_root) . '/app/bootstrap/settings.json');
            $settings = json_decode($settings, TRUE);
            if ($settings['framework'] != 'dappur') {
                die($this->logEntry("You do not appear to have a valid settings file.  Please check and try again."));
            }else{
                echo $this->logEntry("Valid Dappur settings file found.");
            }
        }
    }

    private function updateSettings(){
        
        if (!empty($this->settings_array)) {

            $check_connection = $this->checkConnection();

            if ($check_connection['check_construct'] == true) {

                // Update the settings file with the array from the input
                echo $this->logEntry("Updating the settings file with the new database connection.");
                $settings = file_get_contents(dirname($this->document_root) . '/app/bootstrap/settings.json');
                $settings = json_decode($settings, TRUE);
                $settings_new = array_replace_recursive($settings, $this->settings_array);
                if(file_put_contents(dirname($this->document_root) . '/app/bootstrap/settings.json', json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE))){
                    echo $this->logEntry("Settings file successfully updated.");
                }else{
                    die($this->logEntry("There was an error updating the settings file."));
                }
                
            }else{
                die("Database connection parameters defined, but a connection could not be made.  Please check your settings and run this script again.");
            }
        }
        
    }

    private function checkConnection(){

        $output == array();

        $settings = file_get_contents(dirname($this->document_root) . '/app/bootstrap/settings.json');
        $settings = json_decode($settings, TRUE);
        $file_database = $settings['db']['databases'][$settings['db']['use']];

        $construct_database = $this->settings_array['db']['databases'][$this->settings_array['db']['use']];

        $output['check_file'] = false;
        $output['check_construct'] = false;


        // Check the mysql database in the settings file.
        if ($file_database['host'] != "" && $file_database['database'] != "" && $file_database['username'] != "" && $file_database['password'] != "") {
            if (!@mysqli_connect($file_database['host'], $file_database['username'], $file_database['password'], $file_database['database'])) {
                echo $this->logEntry("MySQL Connection Error: " . mysqli_connect_error());
            }else{
                echo $this->logEntry("Successfully connected to the settings.json selected database.");
                $output['check_file'] = true;
            }
        }
        if ($construct_database) {
            if (!@mysqli_connect($construct_database['host'], $construct_database['username'], $construct_database['password'], $construct_database['database'])) {
                echo $this->logEntry("MySQL Connection Error: " . mysqli_connect_error());
            }else{
                echo $this->logEntry("Successfully connected to the new selected database.");
                $output['check_construct'] = true;
            }
        }

        if ($output['check_file'] == false && $output['check_construct'] == false) {
            die($this->logEntry("Could not successfully connect to a database.  Please check your settings and run this script again."));
        }else{
            return $output;
        }
    }

    private function checkPhinx(){

        // Check if Phinx is installed
        if (!is_file(dirname($this->document_root) . '/vendor/robmorgan/phinx/bin/phinx')) {
            // Install/Update Phinx globally in composer
            $install_phinx = shell_exec("cd " . dirname($this->document_root) . " && ./composer.phar require robmorgan/phinx 2>&1");
            echo $this->logEntry($install_phinx);
            if(!strpos($install_phinx, 'for robmorgan/phinx')){
                die($this->logEntry("Phinx is required as a global composer dependency.  Please check that it is installed and your web user has access to it."));
            }
        }

        // Check that phinx was installed properly
        $check_phinx = shell_exec(dirname($this->document_root) . "/vendor/robmorgan/phinx/bin/phinx --version");
        echo $this->logEntry($check_phinx);
        if(!strpos($check_phinx, 'by Rob Morgan - https://phinx.org')){
            die($this->logEntry("Phinx is required in order for database migration to work.  Please check the deploy.init.log for errors."));
        }

        // Check for Phinx config file
        if (!is_file(dirname($this->document_root) . '/phinx.php')) {
            die($this->logEntry("You do not appear to have a valid phinx.php file in your project root directory."));
        }else{
            echo $this->logEntry("Phinx config: phinx.php found.");
        }

    }

    private function migrateUp(){
        $migrate_up = shell_exec("cd " . dirname($this->document_root) . " && ./vendor/robmorgan/phinx/bin/phinx migrate 2>&1");
        echo $this->logEntry($migrate_up);
        if (!strpos($migrate_up, 'All Done.')) {
            echo $this->logEntry("There might have been an error in the database migration.  Please check the logs to be sure.");
        }else{
            echo $this->logEntry("Database migration completed successfully.");
        }
    }

    private function logEntry($log_text, $return = true){

        // Create log folder if it does not exist
        if (!is_dir(dirname($this->log_file))) {
            mkdir(dirname($this->log_file), 0755, true);
        }

        // Create Log File if it does not exist
        if (!file_exists($this->log_file)) {
            touch($this->log_file);
        }

        // Add Log Entry to file
        file_put_contents($this->log_file, date('m/d/Y h:i:s a') . " " . $log_text . "\n", FILE_APPEND);

        if ($return == true) {
            return "<pre>" . date('m/d/Y h:i:s a') . " $log_text" . "</pre>";
        }
        
    }
}


$token = "da39a3ee5e6b4b0d3255bfef95601890afd80709";
$repo_url = "git@github.com:dappur/framework.git";
$document_root = $_SERVER['DOCUMENT_ROOT'];
$user_home = $_SERVER['HOME'];
$settings = array(
    "db" => array(
        "use" => "production",
        "production" => array(
            "host" => "104.236.152.182",
            "port" => 3306,
            "database" => "framework",
            "username" => "53e94badb352",
            "password" => "8f1aeaae81c5459d",
        )
    )
);

if (isset($_GET['token']) && $_GET['token'] == $token) {
    $deploy = new \Dappur\Dappurware\Deployment($repo_url, $document_root, $user_home, $settings);
    echo $deploy->initDappur();
    echo $deploy->execute();
    echo $deploy->updateDappur();
}else{
    die('Deployment Token Invalid');
}

