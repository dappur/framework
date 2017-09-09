<?php

namespace Dappur\Dappurware;

use Dappur\Model\Users;
use Dappur\Model\Config;
use Dappur\Model\ConfigGroups;
use Dappur\Model\EmailsTemplates;
use Dappur\Model\Emails;


class Email extends Dappurware
{
    
    public function getPlaceholders(){

        $output = array();

        $users = Users::first()->toArray();

        foreach ($users as $key => $value) {
            
            if ($key == "password" || $key == "permissions") {
                continue;
            }
            
            $output['User Info'][] = array("name" => "user_" . str_replace("-", "_", $key));
        }

        $config = Config::select('config.*')->leftJoin("config_groups", "config_groups.id", "=", "config.id")->get()->toArray();
        $config_groups = ConfigGroups::get()->toArray();

        foreach ($config as $cvalue) { 
            $output[$cvalue['group_id']][] = array("name" => "settings_" . str_replace("-", "_", $cvalue['name']), "value" => $cvalue['value']);
        }

        foreach ($config_groups as $cgvalue) {

            if ($output[$cgvalue['id']]) {
                $output[$cgvalue['name']] = $output[$cgvalue['id']];
                unset($output[$cgvalue['id']]);
            }
            
        }
        return $output;

    }


    private function send($email, $subject, $html, $plain_text){

        $output = array();

        $mail = $this->mail;
        $mail->ClearAllRecipients();
        $mail->setFrom($this->config['from-email']);
        $mail->addAddress($email);
        $mail->addReplyTo($this->config['from-email']);
        $mail->isHTML(true);

        $mail->Subject = $subject;
        $mail->Body    = $html;
        $mail->AltBody = $plain_text;

        if(!$mail->send()) {
            $output['result'] = false;
            $output['error'] = $mail->ErrorInfo;
        } else {
            if ($this->settings['mail']['logging']) {
                $add_email = new Emails;
                $add_email->secure_id = bin2hex(mcrypt_create_iv(22, MCRYPT_DEV_URANDOM));
                $add_email->template_id = $template_id;
                $add_email->send_to = $email;
                $add_email->subject = $subject;
                if (in_array("html", $this->settings['mail']['log_details'])) {
                    $add_email->html = $html;
                }
                if (in_array("plain_text", $this->settings['mail']['log_details'])) {
                    $add_email->plain_text = $plain_text;
                }
                $add_email->save();
            }
            $output['result'] = true;
        }

        return $output;
    }

    private function parseRecipients(Array $send_to){
        $output = array();
        foreach ($send_to as $value) {
            if (is_int($value)) {
                // If int, get user email
                if ($this->auth->findById($value)) {
                    $output['users'][] = $value;
                }
                
            }else if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                // If email, check if user
                $email_user = $this->container->auth->findByCredentials(['login' => $value]);
                if ($email_user) {
                    if (!in_array($value, $output['users'])) {
                        $output['users'][] = $email_user->id;
                    }
                }else{
                    $output['email'][] = $value;
                }
                
            }else{
                //if other, check to see if user role exists for slug
                $role = $this->auth->findRoleBySlug($value);
                if ($role) {
                    $users = $role->users()->get(['id']);
                    foreach ($users as $value) {
                        if (!in_array($value, $output['users'])) {
                            $output['users'][] = $value['id'];
                        }
                    }
                }
            }
        }

        return $output;
    }

    private function preparePlaceholders($placeholders){
        
        $output = array();

        foreach ($placeholders as $key => $value) {
            foreach ($value as $key2 => $value2) {
                $output[$value2['name']] = $value2['value'];
            }
        }

        return $output;
    }

    public function sendTemplate(Array $send_to, $template_slug = null, Array $params = null){

        $output = array();

        $placeholders = $this->getPlaceholders();

        $template = EmailsTemplates::where('slug', '=', $template_slug)->first();

        $recipients = $this->parseRecipients($send_to);

        if (!empty($recipients)) {
        
            if ($template) {

                // Get Email Bodies
                $html = $template->html;
                $plain_text = $template->plain_text;
                $subject = $template->subject;

                // Set Up Placeholders
                
                // Process Custom Placeholders
                $tplaceholders = json_decode($template->placeholders);

                foreach ($tplaceholders as $value) {

                    if (isset($params[$value])) {
                        $placeholders['Template'][] = array("name" => $value, "value" => $params[$value]);
                    }
                }

                // Send Email To Users
                if ($recipients['users']) {
                    foreach ($recipients['users'] as $uvalue) {
                        // Process Bodies with Custom and System Placeholders
                        $user_temp = Users::find($uvalue);
                        $placeholders_temp = $placeholders;

                        if ($user_temp) {
                            foreach ($placeholders['User Info'] as $key => $sssvalue) {
                                $placeholders_temp['User Info'][$key]['value'] = $user_temp[str_replace("user_", "", $sssvalue['name'])];
                            }
                        }

                        $placeholders_temp = $this->preparePlaceholders($placeholders_temp);

                        // Process HTML Email
                        $html_temp = new \Twig_Environment(new \Twig_Loader_Array([$template->slug . '_html' => $html]));
                        $html_temp = $html_temp->render($template->slug . '_html', $placeholders_temp);

                        // Process Plain Text Email
                        $plain_text_temp = new \Twig_Environment(new \Twig_Loader_Array([$template->slug . '_pt' => $plain_text]));
                        $plain_text_temp = $plain_text_temp->render($template->slug . '_pt', $placeholders_temp);

                        //Process Subject
                        $subject_temp = new \Twig_Environment(new \Twig_Loader_Array([$template->slug . '_sub' => $subject]));
                        $subject_temp = $subject_temp->render($template->slug . '_sub', $placeholders_temp);

                        $send = $this->send($user_temp->email, $subject_temp, $html_temp, $plain_text_temp, $template->id);

                        if ($send['result']) {
                            $output['results']['success'][] = array("email" => $user_temp->email);
                        }else{
                            $output['results']['errors'][] = array("email" => $user_temp->email, "error" => $result['error']);
                        }
                    }
                }

                // Send Email to Email Addresses
                if ($recipients['email']) {

                    $placeholders_temp = $this->preparePlaceholders($placeholders);
                    
                    foreach ($recipients['email'] as $evalue) {
                        // Process HTML Email
                        $html_temp = new \Twig_Environment(new \Twig_Loader_Array([$template->slug . '_html' => $html]));
                        $html_temp = $html_temp->render($template->slug . '_html', $placeholders_temp);

                        // Process Plain Text Email
                        $plain_text_temp = new \Twig_Environment(new \Twig_Loader_Array([$template->slug . '_pt' => $plain_text]));
                        $plain_text_temp = $plain_text_temp->render($template->slug . '_pt', $placeholders_temp);

                        //Process Subject
                        $subject_temp = new \Twig_Environment(new \Twig_Loader_Array([$template->slug . '_sub' => $subject]));
                        $subject_temp = $subject_temp->render($template->slug . '_sub', $placeholders_temp);

                        $send = $this->send($evalue, $subject_temp, $html_temp, $plain_text_temp, $template->id);

                        if ($send['result']){
                            $output['results']['success'][] = array("email" => $evalue);
                        }else{
                            $output['results']['errors'][] = array("email" => $evalue, "error" => $result['error']);
                        }

                    }
                }
                
                
            }else{
                $output['status'] = "error";
                $output['message'] = "No valid template was selected.";
            }
        }else{
            $output['status'] = "error";
            $output['message'] = "There were no recipients to send to.";
        }

        return $output;

    }

    public function sendEmail(Array $send_to, $subject, $html, $plain_text){

        $output = array();

        $placeholders = Email::getPlaceholders();

        $recipients = Email::parseRecipients($send_to);

        if (!empty($recipients)) {

            // Send Email To Users
            if ($recipients['users']) {
                foreach ($recipients['users'] as $uvalue) {
                    // Process Bodies with Custom and System Placeholders
                    $user_temp = Users::find($uvalue);
                    $placeholders_temp = $placeholders;

                    if ($user_temp) {
                        foreach ($placeholders['User Info'] as $key => $sssvalue) {
                            $placeholders_temp['User Info'][$key]['value'] = $user_temp[str_replace("user_", "", $sssvalue['name'])];
                        }
                    }

                    $placeholders_temp = $this->preparePlaceholders($placeholders_temp);

                    // Process HTML Email
                    $html_temp = new \Twig_Environment(new \Twig_Loader_Array([$template->slug . '_html' => $html]));
                    $html_temp = $html_temp->render($template->slug . '_html', $placeholders_temp);

                    // Process Plain Text Email
                    $plain_text_temp = new \Twig_Environment(new \Twig_Loader_Array([$template->slug . '_pt' => $plain_text]));
                    $plain_text_temp = $plain_text_temp->render($template->slug . '_pt', $placeholders_temp);

                    //Process Subject
                    $subject_temp = new \Twig_Environment(new \Twig_Loader_Array([$template->slug . '_sub' => $subject]));
                    $subject_temp = $subject_temp->render($template->slug . '_sub', $placeholders_temp);

                    if (Email::send($user_temp->email, $subject_temp, $html_temp, $plain_text_temp, $template->id)) {
                        $output['results']['success'][] = array("email" => $user_temp->email);
                    }else{
                        $output['results']['errors'][] = array("email" => $user_temp->email, "error" => $result['error']);
                    }
                }
            }


            // Send Email to Email Addresses
            if ($recipients['email']) {
                foreach ($recipients['email'] as $evalue) {

                    $placeholders_temp = $this->preparePlaceholders($placeholders);

                    // Process HTML Email
                    $html_temp = new \Twig_Environment(new \Twig_Loader_Array([$template->slug . '_html' => $html]));
                    $html_temp = $html_temp->render($template->slug . '_html', $placeholders_temp);

                    // Process Plain Text Email
                    $plain_text_temp = new \Twig_Environment(new \Twig_Loader_Array([$template->slug . '_pt' => $plain_text]));
                    $plain_text_temp = $plain_text_temp->render($template->slug . '_pt', $placeholders_temp);

                    //Process Subject
                    $subject_temp = new \Twig_Environment(new \Twig_Loader_Array([$template->slug . '_sub' => $subject]));
                    $subject_temp = $subject_temp->render($template->slug . '_sub', $placeholders_temp);

                    if (Email::send($evalue, $subject_temp, $html_temp, $plain_text_temp, $template->id)){
                        $output['results']['success'][] = array("email" => $evalue);
                    }else{
                        $output['results']['errors'][] = array("email" => $evalue, "error" => $result['error']);
                    }

                }
            }
        }else{
            $output['status'] = "error";
            $output['message'] = "There were no recipients to send to.";
        }
        
        return $output;
    }

    

}