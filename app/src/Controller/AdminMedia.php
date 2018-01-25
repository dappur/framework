<?php

namespace Dappur\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;
use Dappur\Dappurware\Sentinel as S;
use Cloudinary;

class AdminMedia extends Controller{

	private function getFiles($directory){

        $sentinel = new S($this->container);
        if(!$sentinel->hasPerm('media.local')){
            return $this->redirect($response, 'dashboard');
        }

        $listing = scandir($directory);

        $folders_array = array(); 
        $files_array = array();
               

        foreach ($listing as $value) {
            if ($value == '.' || $value == '..' || $value == 'index.php' || $value == '.htaccess') {
                continue;
            }

            if (is_dir($directory . "/" . $value)) {
                $folders_array[] = $value;
            }else{
                $files_array[] = $value;
            }

        }
        usort($folders_array, 'strnatcasecmp');
        usort($files_array, 'strnatcasecmp');

        $final_files_array = array();

        foreach ($files_array as $fikey => $fivalue) {
          
            if ($explode = explode("/", mime_content_type($directory . '/' . $fivalue))) {
                if ($explode[0] == "image") {
                    $final_files_array[] = array("type" => "image", "file" => $fivalue);
                }else{
                    $final_files_array[] = array("type" => "other", "file" => $fivalue);
                }
            }
        }

        return array('folders' => $folders_array, 'files' => $final_files_array);

    }

    public function mediaFolder(Request $request, Response $response){

        $sentinel = new S($this->container);
        if(!$sentinel->hasPerm('media.folder')){
            return $this->redirect($response, 'dashboard');
        }

        $requestParams = $request->getParams();

        $directory = $requestParams['directory'];

        if (substr(realpath($this->upload_dir . "/$directory"), 0, strlen($this->upload_dir)) !== $this->upload_dir) {
            return $response->write(json_encode(array("status" => "error")), 201);
        }

        $output = $this->getFiles($this->upload_dir . "/$directory");

        return $response->write(json_encode($output), 201);

    }

    public function mediaDelete(Request $request, Response $response){

        $sentinel = new S($this->container);
        if(!$sentinel->hasPerm('media.delete')){
            return $this->redirect($response, 'dashboard');
        }

        $requestParams = $request->getParams();

        $directory = $requestParams['current_folder'];
        $file = $requestParams['current_file'];

        if (substr(realpath($this->upload_dir . "/$directory/$file"), 0, strlen($this->upload_dir)) !== $this->upload_dir) {
            $this->validator->addError('error', 'You do not have permission to be in this directory.');
        }

        if (!is_file(realpath($this->upload_dir . "/$directory/$file"))) {
            $this->validator->addError('error', 'The file you are trying to delete does not exist.');
        }


        $output = array();
        // Parse validation errors to output for SWAL;
        $errors = $this->validator->getErrors();
        if ($errors) {
            $output['result'] = "error";
            $output['message'] = "There was an error creating the folder.";

            $errortemp = array();
            foreach ($errors as $ekey => $evalue) {
                foreach ($evalue as $evkey => $evvalue) {
                    $errortemp[] = $evvalue;
                }
                
            }

            $output['data'] = $errortemp;

            return $response->write(json_encode($output), 201);
        }

        if ($this->validator->isValid()) {

            if (unlink(realpath($this->upload_dir . "/$directory/$file"))) {
                $output['result'] = "success";
                $output['message'] = "File deleted successfully.";
                return $response->write(json_encode($output), 201);
            }else{
                $output['result'] = "error";
                $output['message'] = "File could not be deleted.  Uhh ohh!";
                $output['data'] = array("File could not be deleted. Uhh ohh!");
                return $response->write(json_encode($output), 201);
            }
        }

    }

    public function mediaUpload(Request $request, Response $response){

        $sentinel = new S($this->container);
        if(!$sentinel->hasPerm('media.upload')){
            return $this->redirect($response, 'dashboard');
        }

        $requestParams = $request->getParams();

        $directory = $requestParams['current_folder'];

        

        if (substr(realpath($this->upload_dir . "/$directory"), 0, strlen($this->upload_dir)) !== $this->upload_dir) {
            return $response->write(json_encode(array("status" => "error")), 201);
        }

        $errors = 0;
        foreach($_FILES['files']['tmp_name'] as $key => $tmp_name) {
            if(!move_uploaded_file($_FILES['files']['tmp_name'][$key], realpath($this->upload_dir . "/$directory") . "/".$_FILES["files"]["name"][$key])){
                $errors++;
            }
        }

        if ($errors > 0) {
            return $response->write(json_encode(array("status" => "error")), 201);
        }else{
            return $response->write(json_encode(array("status" => "success")), 201);
        }
        

    }


    public function mediaFolderNew(Request $request, Response $response){

        $sentinel = new S($this->container);
        if(!$sentinel->hasPerm('media.local')){
            return $this->redirect($response, 'dashboard');
        }

        $requestParams = $request->getParams();
        $current_folder = $requestParams['current_folder'];
        $new_folder_name = $requestParams['new_folder_name'];

        // Check that the upload folder exists
        if (!is_dir(realpath($this->upload_dir . "/$current_folder"))) {
            $this->validator->addError('new_folder_name', 'Selected folder does not exist.');
        }

        // Check that the folder doesn't exists
        if (is_dir($this->upload_dir . "/$current_folder/$new_folder_name")) {
            $this->validator->addError('new_folder_name', 'Folder already exists.');
        }

        // Check to make sure that the folder is within the upload dir
        if (substr(realpath($this->upload_dir . "/$current_folder"), 0, strlen($this->upload_dir)) !== $this->upload_dir) {
            $this->validator->addError('new_folder_name', 'Folder already exists.');
        }

        // Validate Data
        $validate_data = array(
            'new_folder_name' => array(
                'rules' => V::length(2, 25)->alnum('-_')->noWhitespace(), 
                'messages' => array(
                    'length' => 'Must be between 2 and 25 characters.',
                    'alnum' => 'Name must be alphanumeric with - and _',
                    'noWhitespace' => "Name must not contain any spaces."
                    )
            ),
            
        );
        $this->validator->validate($request, $validate_data);

        

        // Parse validation errors to output for SWAL;
        $errors = $this->validator->getErrors();
        if ($errors) {
            $output['result'] = "error";
            $output['message'] = "There was an error creating the folder.";

            $errortemp = array();
            foreach ($errors as $ekey => $evalue) {
                foreach ($evalue as $evkey => $evvalue) {
                    $errortemp[] = $evvalue;
                }
                
            }

            $output['data'] = $errortemp;

            return $response->write(json_encode($output), 201);
        }

        if ($this->validator->isValid()) {
            
            $newfolderpath = $this->upload_dir . $current_folder . '/' . $new_folder_name;
            
            
            if (mkdir($newfolderpath)) {
                $output['result'] = "success";
                $output['message'] = "Folder was successfully created.";

                return $response->write(json_encode($output), 201);
            }else{
                $output['result'] = "error";
                $output['message'] = "There was an unknown error creating the folder.";
                $output['data'] = array("There was an unknown error creating the folder.");

                return $response->write(json_encode($output), 201);
            }

            
        }

    }


    public function media(Request $request, Response $response){

        $sentinel = new S($this->container);
        if(!$sentinel->hasPerm('media.local')){
            return $this->redirect($response, 'dashboard');
        }

        return $this->view->render($response, 'media.twig');
    }

    public function getCloudinaryCMS($container, $signature_only = false){

        $sentinel = new S($container);
        $sentinel->hasPerm('media.cloudinary');

        // Generate Timestamp
        $date = new \DateTime();
        $timestamp = $date->getTimestamp();
        
        // Prepare Cloudinary CMS Params
        if ($signature_only) {
            $params = array("timestamp" => $timestamp);
        }else{
            $params = array("timestamp" => $timestamp, "mode" => "tinymce");
        }

        // Prepare Cloudinary Options
        $options = array("cloud_name" => $container->settings['cloudinary']['cloud_name'],
            "api_key" => $container->settings['cloudinary']['api_key'],
            "api_secret" => $container->settings['cloudinary']['api_secret']);

        // Sign Request With Cloudinary
        $output = \Cloudinary::sign_request($params, $options);

        if ($output) {
            // Build the http query
            $api_params_cl = http_build_query($output);

            // Complete the Cloudinary URL
            $cloudinary_cms_url = "https://cloudinary.com/console/media_library/cms?$api_params_cl";
            if ($signature_only) {
                $output['signature'] = \Cloudinary:: api_sign_request(
                    array(
                        "timestamp" => $timestamp
                    ), 
                    $container->settings['cloudinary']['api_secret']
                );

                $output['api_key'] = $container->settings['cloudinary']['api_key'];
                $output['timestamp'] = $timestamp;
                return $output;
            }else{
                return $cloudinary_cms_url;
            }
        }else{
            return false;
        }
        
    }


    public function cloudinarySign(Request $request, Response $response){

        $sentinel = new S($this->container);
        $sentinel->hasPerm('media.cloudinary');

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
        }else{
            return false;
        }
        
    }
}