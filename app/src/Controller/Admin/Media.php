<?php

namespace Dappur\Controller\Admin;

use Dappur\Controller\Controller as Controller;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Media extends Controller
{
    /** @SuppressWarnings(PHPMD.UnusedFormalParameter)  */
    public function cloudinarySign(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('media.cloudinary', 'dashboard')) {
            return $check;
        }

        $cloudinary = $this->cloudinary;

        $params = array();
        foreach ($request->getQueryParam('data') as $key => $value) {
            $params[$key] = $value;
        }

        // Sign Request With Cloudinary
        $signature = $cloudinary->api_sign_request(
            $params,
            $cloudinary->config_get("api_secret")
        );

        if ($signature) {
            return $signature;
        }

        return false;
    }

    public function getCloudinaryCMS($container, $signatureOnly = null)
    {
        $sentinel = new \Dappur\Dappurware\Sentinel($container);
        if ($check = $sentinel->hasPerm('media.cloudinary', 'dashboard')) {
            return $check;
        }

        // Generate Timestamp
        $date = new \DateTime();
        $timestamp = $date->getTimestamp();
        
        // Prepare Cloudinary CMS Params
        $params = array("timestamp" => $timestamp);
        if (is_null($signatureOnly)) {
            $params['mode'] = "tinymce";
        }

        // Prepare Cloudinary Options
        $options = array("cloud_name" => $container->settings['cloudinary']['cloud_name'],
            "api_key" => $container->settings['cloudinary']['api_key'],
            "api_secret" => $container->settings['cloudinary']['api_secret']);
        $cloudinary = new \Cloudinary;
        // Sign Request With Cloudinary
        $output = $cloudinary->sign_request($params, $options);

        if ($output) {
            // Build the http query
            $apiParamsCl = http_build_query($output);

            // Complete the Cloudinary URL
            $cloudinaryCmsUrl = "https://cloudinary.com/console/media_library/cms?$apiParamsCl";
            if ($signatureOnly) {
                $output['signature'] = $cloudinary-> api_sign_request(
                    array(
                        "timestamp" => $timestamp
                    ),
                    $container->settings['cloudinary']['api_secret']
                );

                $output['api_key'] = $container->settings['cloudinary']['api_key'];
                $output['timestamp'] = $timestamp;
                return $output;
            }

            return $cloudinaryCmsUrl;
        }

        return false;
    }

    private function getFiles($directory)
    {
        if ($check = $this->sentinel->hasPerm('media.local', 'dashboard')) {
            return $check;
        }

        $listing = scandir($directory);

        $foldersArray = array();
        $filesArray = array();
        
        $excludes = array('.', '..', 'index.php', '.htaccess');

        foreach ($listing as $value) {
            if (in_array($value, $excludes)) {
                continue;
            }

            if (is_dir($directory . "/" . $value)) {
                $foldersArray[] = $value;
                continue;
            }

            $filesArray[] = $value;
        }
        usort($foldersArray, 'strnatcasecmp');
        usort($filesArray, 'strnatcasecmp');

        $finalFilesArray = array();

        foreach ($filesArray as $fivalue) {
            if ($explode = explode("/", mime_content_type($directory . '/' . $fivalue))) {
                if ($explode[0] == "image") {
                    $finalFilesArray[] = array("type" => "image", "file" => $fivalue);
                    continue;
                }
                $finalFilesArray[] = array("type" => "other", "file" => $fivalue);
            }
        }

        return array('folders' => $foldersArray, 'files' => $finalFilesArray);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function media(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('media.local', 'dashboard')) {
            return $check;
        }

        return $this->view->render($response, 'media.twig');
    }

    public function mediaDelete(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('media.delete', 'dashboard')) {
            return $check;
        }

        $requestParams = $request->getParams();

        $directory = $requestParams['current_folder'];
        $file = $requestParams['current_file'];

        $checkDir = substr(realpath($this->uploadDir . "/$directory/$file"), 0, strlen($this->uploadDir));

        if ($checkDir !== $this->uploadDir) {
            $this->validator->addError('error', 'You do not have permission to be in this directory.');
        }

        if (!is_file(realpath($this->uploadDir . "/$directory/$file"))) {
            $this->validator->addError('error', 'The file you are trying to delete does not exist.');
        }


        $output = array();
        // Parse validation errors to output for SWAL;
        $errors = $this->validator->getErrors();
        if ($errors) {
            $output['result'] = "error";
            $output['message'] = "There was an error creating the folder.";

            $errortemp = array();
            foreach ($errors as $evalue) {
                foreach ($evalue as $evvalue) {
                    $errortemp[] = $evvalue;
                }
            }

            $output['data'] = $errortemp;

            return $response->write(json_encode($output), 201);
        }

        if ($this->validator->isValid()) {
            if (unlink(realpath($this->uploadDir . "/$directory/$file"))) {
                $output['result'] = "success";
                $output['message'] = "File deleted successfully.";
                return $response->write(json_encode($output), 201);
            }
        }

        $output['result'] = "error";
        $output['message'] = "File could not be deleted.  Uhh ohh!";
        $output['data'] = array("File could not be deleted. Uhh ohh!");
        return $response->write(json_encode($output), 201);
    }

    public function mediaFolder(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('media.local', 'dashboard')) {
            return $check;
        }

        $requestParams = $request->getParams();

        $directory = $requestParams['directory'];

        if (substr(realpath($this->uploadDir . "/$directory"), 0, strlen($this->uploadDir)) !== $this->uploadDir) {
            return $response->write(json_encode(array("status" => "error")), 201);
        }

        $output = $this->getFiles($this->uploadDir . "/$directory");

        return $response->write(json_encode($output), 201);
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity) At threshold
     */
    public function mediaFolderNew(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('media.local', 'dashboard')) {
            return $check;
        }

        $requestParams = $request->getParams();
        $currentFolder = $requestParams['current_folder'];
        $newFolderName = $requestParams['new_folder_name'];

        // Check that the upload folder exists
        if (!is_dir(realpath($this->uploadDir . "/$currentFolder"))) {
            $this->validator->addError('new_folder_name', 'Selected folder does not exist.');
        }

        // Check that the folder doesn't exists
        if (is_dir($this->uploadDir . "/$currentFolder/$newFolderName")) {
            $this->validator->addError('new_folder_name', 'Folder already exists.');
        }

        $checkDir = substr(realpath($this->uploadDir . "/$currentFolder"), 0, strlen($this->uploadDir));

        // Check to make sure that the folder is within the upload dir
        if ($checkDir !== $this->uploadDir) {
            $this->validator->addError('new_folder_name', 'Folder already exists.');
        }

        // Validate Data
        $validateData = array(
            'new_folder_name' => array(
                'rules' => \Respect\Validation\Validator::length(2, 25)->alnum('-_')->noWhitespace(),
                'messages' => array(
                    'length' => 'Must be between 2 and 25 characters.',
                    'alnum' => 'Name must be alphanumeric with - and _',
                    'noWhitespace' => "Name must not contain any spaces."
                    )
            ),
            
        );
        $this->validator->validate($request, $validateData);

        if ($this->validator->isValid()) {
            $newfolderpath = $this->uploadDir . $currentFolder . '/' . $newFolderName;
            
            if (mkdir($newfolderpath)) {
                $output['result'] = "success";
                $output['message'] = "Folder was successfully created.";
                return $response->write(json_encode($output), 201);
            }

            $output['result'] = "error";
            $output['message'] = "There was an unknown error creating the folder.";
            $output['data'] = array("There was an unknown error creating the folder.");
        }

        // Parse validation errors to output for SWAL;
        $errors = $this->validator->getErrors();
        if ($errors) {
            $output['result'] = "error";
            $output['message'] = "There was an error creating the folder.";

            $errortemp = array();
            foreach ($errors as $evalue) {
                foreach ($evalue as $evvalue) {
                    $errortemp[] = $evvalue;
                }
            }

            $output['data'] = $errortemp;
        }

        return $response->write(json_encode($output), 201);
    }

    public function mediaUpload(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('media.upload', 'dashboard')) {
            return $check;
        }

        $requestParams = $request->getParams();

        $directory = $requestParams['current_folder'];

        if (substr(realpath($this->uploadDir . "/$directory"), 0, strlen($this->uploadDir)) !== $this->uploadDir) {
            return $response->write(json_encode(array("status" => "error")), 201);
        }

        $errors = 0;

        $files = $request->getUploadedFiles();
        if (empty($files['uploaded_file'])) {
            throw new Exception('Expected a newfile');
        }

        $newFile = $files['uploaded_file'];

        move_uploaded_file(
            $newFile->file,
            realpath($this->uploadDir . "/$directory") . "/" . $newFile->getClientFilename()
        );

        if ($errors == 0) {
            return $response->write(json_encode(array("status" => "success")), 201);
        }

        return $response->write(json_encode(array("status" => "error")), 201);
    }
}
