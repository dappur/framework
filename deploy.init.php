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
/** @SuppressWarnings(PHPMD) */
class Deployment
{

    // Git Repository URL
    protected $repoUrl;
    // Web root directory on server
    protected $documentRoot;
    // User Home Directory
    protected $userHome;
    // Settings Array
    protected $repoDir;
    // Full path to git binary is required if git is not in your PHP user's path. Otherwise just use 'git'.
    protected $gitBinPath;
    // Log File
    protected $logFile;
    // Deployment Cert File (Full path the the deployment RSA key's *.pub file)
    protected $certFolder;
    // Deployment Cert File (Full path the the deployment RSA key's *.pub file)
    protected $certFileName;
    // Set high timeout limit due to composer and git sometimes taking longer than default.
    protected $timeLimit;
    // Certificate path with filename
    protected $certPath;
    // Framework settings
    protected $settings;
    
    
    public function __construct(
        $documentRoot = null,
        $userHome = null,
        $gitBinPath = 'git',
        $certFileName = 'deploy',
        $timeLimit = 300
    ) {
        $this->documentRoot = $_SERVER['DOCUMENT_ROOT'];
        if (!is_null($documentRoot)) {
            $this->documentRoot = $documentRoot;
        }
        $this->userHome = $_SERVER['HOME'];
        if (!is_null($userHome)) {
            $this->userHome = $userHome;
        }
        $this->gitBinPath = $gitBinPath;
        $this->logFile = dirname($this->documentRoot) . '/storage/log/deployment/deployment-'.date("YmdGis").'.log';
        $this->repoDir = dirname($this->documentRoot) . '/repo';
        $this->certFolder = dirname($this->documentRoot) . '/storage/certs/deployment';
        $this->certFileName = $certFileName;
        $this->certPath = realpath($this->certFolder . "/" . $this->certFileName);
        set_time_limit($timeLimit);

        // Check for settings.json
        $settingsFile = json_decode(file_get_contents(realpath($documentRoot) . "/../settings.json"));
        if (!$settingsFile) {
            die($this->logEntry("Could not locate a settings.json file in the document root."));
        }
        $this->settings = $settingsFile;
        
        // Check Repo Url
        if (!isset($settingsFile->deployment->repo_url) || $settingsFile->deployment->repo_url == "") {
            die($this->logEntry("Please ensure that you have a repo URL in the deployment section of settings.json."));
        }
        $this->repoUrl = $settingsFile->deployment->repo_url;

        // Check Repo Branch
        if (!isset($settingsFile->deployment->repo_branch) || $settingsFile->deployment->repo_branch == "") {
            die($this->logEntry("Please ensure that you have a repo branch in the deployment section of settings.json."));
        }
        $this->repoBranch = $settingsFile->deployment->repo_branch;

        // Check Project Name
        if (!isset($settingsFile->framework) || $settingsFile->framework == "") {
            die($this->logEntry("Please ensure that you have a valid framework name in settings.json."));
        }
    }

    // Initialize Dappur
    public function initDappur()
    {
        echo $this->logEntry("Initializing Dappur Framework...");
        $this->validateDocumentRoot();
        $this->installUpdateComposer();
        $this->checkGit();
        $this->checkInstallRepo();
        $this->checkPhinx();
        $this->migrateUp();
        echo $this->logEntry("Framework initialization complete.");
    }

    // Execute
    public function execute()
    {
        echo $this->logEntry("Checking requirements...");
        $this->validateDocumentRoot();
        $this->installUpdateComposer();
        $this->checkGit();
        $this->checkInstallRepo();
        $this->checkConnection();
        echo $this->logEntry("Requirements validated!");
    }

    public function migrate()
    {
        echo $this->logEntry("Beginning Migration...");
        $this->checkPhinx();
        $this->migrateUp();
    }

    // Make sure PHP user has all appropriate permissions and that server
    // structure is correct
    private function validateDocumentRoot()
    {
        
        // Check that the DOCUMENT_ROOT is a directory called `public`.
        if (end(explode('\\', $this->documentRoot)) != "public" &&
            end(explode('/', $this->documentRoot)) != "public") {
            die($this->logEntry("Your servers DOCUMENT_ROOT needs to be a directory called `public`"));
        }

        // Check that the DOCUMENT_ROOT parent directory is writable.
        if (!is_writable(dirname($this->documentRoot))) {
            die(
                $this->logEntry(
                        "Web server user does not have access to the DOCUMENT_ROOT's parent directory. ".
                    "This is required in order for Dappur to function properly."
                )
            );
        }
    }

    // Check if composer is installed or download phar and use that.
    private function installUpdateComposer()
    {
        if (!is_file(dirname($this->documentRoot) . '/composer.phar')) {
            // Download composer to the DOCUMENT_ROOT's parent directory.
            if (file_put_contents(
                dirname($this->documentRoot) . '/composer.phar',
                fopen("https://getcomposer.org/download/1.7.1/composer.phar", 'r')
            )) {
                echo $this->logEntry("Composer downloaded successfully. Making composer.phar executable...");
                // CD into DOCUMENT_ROOT parent and make composer.phar executable
                exec("cd " . dirname($this->documentRoot) . " && chmod +x composer.phar");
            } else {
                echo $this->logEntry("Could not get Composer working.  Please check your settings and try again.");
            }
        } else {
            // Check that composer is working
            $check_composer = shell_exec(dirname($this->documentRoot) . "/composer.phar" . ' --version 2>&1');
            echo $this->logEntry($check_composer);
            if (strpos($check_composer, 'omposer version')) {
                // Check for Composer updates
                $update_composer = shell_exec(dirname($this->documentRoot) . "/composer.phar self-update 2>&1");
                echo $this->logEntry("Checking For Composer Update...");
                echo $this->logEntry($update_composer);
            }
        }
    }

    private function checkGit()
    {
        // Check that git is installed
        $check_git = shell_exec($this->gitBinPath . " --version");
        echo $this->logEntry($check_git);
        if (!strpos($check_git, 'it version ')) {
            die($this->logEntry("<pre>Git is required in order for auto deployment to work. ".
                "Please check the deploy.init.log for errors.</pre>"));
        }
    }

    private function checkInstallRepo()
    {

        // Create repository directory if it doesnt exist.
        if (!is_dir($this->repoDir)) {
            echo $this->logEntry("Repository directory does not exist. Creating it now...");
            if (!mkdir($this->repoDir)) {
                die($this->logEntry("There was an error creating the repository directory.  ".
                    "Please check your PHP user's permissions and try again."));
            } else {
                echo $this->logEntry("Repository directory created successfully.");
                
                // Initialize and prepare the git repository
                $this->initializeRepository();

                // Update composer
                $this->updateComposer();
            }
        } else {
            if (!is_file($this->repoDir . '/config')) {
                echo $this->logEntry("Repository doesn't exist.  Attempting to create now...");
                // Initialize and prepare the git repository
                $this->initializeRepository();

                // Update the repository
                $this->updateRepository();

                // Update composer
                $this->updateComposer();
            } else {
                // Update the repository.
                $this->updateRepository();

                // Update composer
                $this->updateComposer();
            }
        }
    }

    private function initializeRepository()
    {

        // Check Known Hosts file for github.com and add using ssh-keyscan
        if (is_file('~/.ssh/known_hosts')) {
            $known_hosts = file_get_contents('~/.ssh/known_hosts');
            if (!strpos($known_hosts, "github.com")) {
                // Add Github to the known hosts
                $add_github = shell_exec('ssh-keyscan github.com >> ~/.ssh/known_hosts');
                $this->logEntry($add_github);
            }
        } else {
            // Add Github to the known hosts
            $add_github = shell_exec('ssh-keyscan github.com >> ~/.ssh/known_hosts');
        }

        // Create the Mirror Repository
        $create_repo_mirror = shell_exec(
            'cd ' . $this->repoDir . ' && ' . 'GIT_SSH_COMMAND="ssh -i ' . $this->certPath . ' -F /dev/null" ' . $this->gitBinPath  . ' clone --mirror ' . $this->repoUrl . " . 2>&1"
        );
        echo $this->logEntry($create_repo_mirror);
        if (strpos($create_repo_mirror, 'ermission denied (publickey)') ||
            strpos($create_repo_mirror, 'Repository not found.') ||
            strpos($create_repo_mirror, 'and the repository exists')) {
            echo $this->logEntry("Access denied to repository... Creating deployment key now...");
            die($this->logEntry(
                "Please add the following public key between the dashes to your ".
                "deployment keys for the repository and run this script again.\n" .
                str_repeat("-", 80) . "\n" .
                $this->getDeployKey() . "\n" .
                str_repeat("-", 80)
            ));
        }

        // Do the initial checkout
        $git_checkout = shell_exec(
            'cd ' . $this->repoDir .
            ' && GIT_WORK_TREE=' . dirname($this->documentRoot) . ' ' .
                $this->gitBinPath  . ' checkout ' . $this->repoBranch . ' -f 2>&1'
        );
        echo $this->logEntry($git_checkout);
        // Get the deployment commit hash
        $commit_hash = exec('cd ' . $this->repoDir . ' && ' . $this->gitBinPath  . ' rev-parse --short HEAD 2>&1');
        echo $this->logEntry("Deployed Commit: " . $commit_hash);
    }

    private function updateRepository()
    {

        // Fetch any new changes
        $git_fetch = exec('cd ' . $this->repoDir . ' && ' . 'GIT_SSH_COMMAND="ssh -i ' . $this->certPath . ' -F /dev/null" ' .  $this->gitBinPath  . ' fetch 2>&1');
        if (empty($git_fetch)) {
            echo $this->logEntry("There is nothing new to fetch from this repository.");
        } else {
            echo $this->logEntry($git_fetch);
            // Do the checkout
            shell_exec(
                'cd ' . $this->repoDir . ' && GIT_WORK_TREE=' . dirname($this->documentRoot) . ' ' .
                $this->gitBinPath  . ' checkout ' . $this->repoBranch . ' -f 2>&1'
            );
            // Get the deployment commit hash
            $commit_hash = exec(
                'cd ' . $this->repoDir . ' && ' . $this->gitBinPath  . ' rev-parse --short HEAD 2>&1'
            );
            echo $this->logEntry("Deployed Commit: " . $commit_hash);
        }
    }

    private function updateComposer()
    {
        $checkLockFile = exec('cd ' . $this->repoDir . ' && ' . $this->gitBinPath  . ' ls-files --error-unmatch composer.lock 2>&1');
        if (strpos($checkLockFile, 'did not match any file')) {
            $update_composer = shell_exec(
                'cd ' . dirname($this->documentRoot) . ' && ' .
                dirname($this->documentRoot) . '/composer.phar update --no-dev 2>&1'
            );
        } else {
            $update_composer = shell_exec(
                'cd ' . dirname($this->documentRoot) . ' && ' .
                dirname($this->documentRoot) . '/composer.phar install --no-dev 2>&1'
            );
        }
        
        echo $this->logEntry($update_composer);
        if (!strpos($update_composer, 'Generating autoload files')) {
            echo $this->logEntry("An error might have occured while updating composer.  ".
                "Please check the deployment log to confirm.");
        } else {
            echo $this->logEntry("Composer updated successfully!");
        }
    }

    private function getDeployKey()
    {
        // Create certificate folder if it does not exist
        if (!is_dir($this->certFolder)) {
            mkdir($this->certFolder, 0755, true);
        }

        if (file_exists($this->certFolder . '/' . $this->certFileName) &&
            file_exists($this->certFolder . '/' . $this->certFileName . ".pub")) {
            return file_get_contents($this->certFolder . '/' . $this->certFileName . ".pub");
        } elseif (!file_exists($this->certFolder . '/' . $this->certFileName)) {
            // Create the deploy key with ssh-keygen
            $generate_key = exec(
                "ssh-keygen -q -N '' -t rsa -b 4096 -f " . $this->certFolder . "/" . $this->certFileName
            );
            echo $this->logEntry($generate_key);
            // Get the contents of the public key
            $public_key = file_get_contents($this->certFolder . '/' . $this->certFileName . '.pub');
            // Install the deploy key if not already done

            // Return the public key
            return $public_key;
        } else {
            // Install the deploy key if not already done

            // Return the public key
            return file_get_contents($this->certFolder . '/' . $this->certFileName . '.pub');
        }
    }

    private function checkConnection()
    {
        $output == array();

        $database = $this->settings->db->{$this->settings->environment};

        $output['check_file'] = false;
        $output['check_construct'] = false;


        // Check the mysql database in the settings file.
        if ($database->host != ""
            && $database->database != ""
            && $database->username != ""
            && $database->password != "") {
            if (!@mysqli_connect(
                $database->host,
                $database->username,
                $database->password,
                $database->database
            )) {
                echo $this->logEntry("MySQL Connection Error: " . mysqli_connect_error());
            } else {
                echo $this->logEntry("Successfully connected to " . $database->database . ".");
                $output['check_file'] = true;
            }
        }

        if ($output['check_file'] == false && $output['check_construct'] == false) {
            die($this->logEntry("Could not successfully connect to a database.  ".
                "Please check your settings and run this script again."));
        } else {
            return $output;
        }
    }

    private function checkPhinx()
    {

        // Check if Phinx is installed
        if (!is_file(dirname($this->documentRoot) . '/vendor/robmorgan/phinx/bin/phinx')) {
            // Install/Update Phinx globally in composer
            $install_phinx = shell_exec(
                "cd " . dirname($this->documentRoot) . " && ./composer.phar require robmorgan/phinx 2>&1"
            );
            echo $this->logEntry($install_phinx);
            if (!strpos($install_phinx, 'for robmorgan/phinx')) {
                die($this->logEntry("Phinx is required as a global composer dependency.  ".
                    "Please check that it is installed and your web user has access to it."));
            }
        }

        // Check that phinx was installed properly
        $check_phinx = shell_exec(dirname($this->documentRoot) . "/vendor/robmorgan/phinx/bin/phinx --version");
        echo $this->logEntry($check_phinx);
        if (!strpos($check_phinx, 'https://phinx.org')) {
            die($this->logEntry("Phinx is required in order for database migration to work.  ".
                "Please check the deploy.init.log for errors."));
        }

        // Check for Phinx config file
        if (!is_file(dirname($this->documentRoot) . '/phinx.php')) {
            die($this->logEntry("You do not appear to have a valid phinx.php file in your project root directory."));
        } else {
            echo $this->logEntry("Phinx config: phinx.php found.");
        }
    }

    private function migrateUp()
    {
        $migrate_up = shell_exec(
            "cd " . dirname($this->documentRoot) . " && ./vendor/robmorgan/phinx/bin/phinx migrate 2>&1"
        );
        echo $this->logEntry($migrate_up);
        if (!strpos($migrate_up, 'All Done.')) {
            echo $this->logEntry("There might have been an error in the database migration.  ".
                "Please check the logs to be sure.");
        } else {
            echo $this->logEntry("Database migration completed successfully.");
        }
    }

    private function logEntry($log_text, $return = true)
    {

        // Create log folder if it does not exist
        if (!is_dir(dirname($this->logFile))) {
            mkdir(dirname($this->logFile), 0755, true);
        }

        // Create Log File if it does not exist
        if (!file_exists($this->logFile)) {
            touch($this->logFile);
        }

        // Add Log Entry to file
        file_put_contents($this->logFile, date('m/d/Y h:i:s a') . " " . $log_text . "\n", FILE_APPEND);

        if ($return == true) {
            return "<pre>" . date('m/d/Y h:i:s a') . " $log_text" . "</pre>";
        }
    }
}

$deploy = new Deployment();
echo $deploy->initDappur();
unlink(__FILE__);
