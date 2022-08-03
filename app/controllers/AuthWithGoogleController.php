<?php
    class AuthWithGoogleController extends Controller
    {
        public function process()
        {
            $AuthUser = $this->getVariable("AuthUser");
            if ($AuthUser) {
                $this->resp->result = 1;
                $this->resp->msg = __("You are Already Logged in");
                $this->jsonecho();
            }
            $this->loginWithGoogle();
        }


        private function loginWithGoogle()
        {
            /**Step 0 - declare required_fields */
            $this->resp->result = 0;
            $id_token = Input::post("id_token");


            if( !$id_token )
            {
                $this->resp->msg = __("Missing id_token !");
                $this->jsonecho();
            }
            $client = new Google_Client(['client_id' => CLIENT_ID]);  // Specify the CLIENT_ID of the app that accesses the backend

            try {
                $payload = $client->verifyIdToken($id_token);
              } catch (Exception $ex) {
                $this->resp->msg = __("Oops! Something went wrong. Please try again!");
                $this->jsonecho();
              }
      
              if (!$payload) {
                $this->resp->msg = __("Login is fail!");
                $this->jsonecho();
              }
      
              $email = $payload['email'];
              $firstname = $payload['given_name'];
              $lastname = $payload['family_name'];
              $picture = $payload['picture'];
      
             
              $this->resp->result = 1;
              $this->resp->email = $email;
              $this->resp->firs_tname= $firstname;
              $this->resp->last_name=$lastname;
              $this->resp->picture=$picture;
              $this->jsonecho();
      
            //   try 
            //   {        
            //     $User = Controller::model("User", $email);
      
            //     if (!$User->isAvailable()) 
            //     {
            //       $tempname = uniqid();
            //       $ext = "png";
          
            //       $filepath = UPLOAD_PATH . "/" . $tempname . "." .$ext;
            //       download_image($picture, $filepath);
                  
            //       $User->set("email", $email)
            //           ->set("password", password_hash(uniqid(), PASSWORD_DEFAULT))
            //           ->set("firstname", $firstname)
            //           ->set("lastname", $lastname)
            //           ->set("avatar", $tempname . "." .$ext)
            //           ->set("is_active", 1)
            //           ->save();
            //     }
                
            //     if( !$User->get("is_active") ){
            //       $this->resp->msg = __("Account is banned!");
            //       $this->jsonecho();
            //     }
                  
            //     $data = array(
            //         "account_type" => $User->get("account_type"),
            //         "email" => $User->get("email"),
            //         "firstname" => $User->get("firstname"),
            //         "lastname" => $User->get("lastname"),
            //         "avatar" => $User->get("avatar"),
            //         "id" => (int)$User->get("id"),
            //         "is_active" => (bool)$User->get("is_active"),
            //         "date" => $User->get("date"),
            //     );
      
            //     $payload = $data;
      
            //     $payload["hashPass"] = md5($User->get("password"));
            //     $payload["iat"] = time();
            //     $jwt = Firebase\JWT\JWT::encode($payload, MP_SALT);
      
            //     $this->resp->result = 1;
            //     $this->resp->accessToken = $jwt;
            //     $this->resp->data = $data;
            //     $this->resp->msg = __("Your account has been created successfully!");
            //   } 
            //   catch (\Exception $ex) 
            //   {
            //       $this->resp->msg = __("Oops! Something went wrong. Please try again!");
            //       $this->jsonecho();
            //   }
      
            //   $this->resp->result = 1;
            //   $this->resp->msg = __("Login is success!");
            //   $this->jsonecho();
            
        }
    }
?>