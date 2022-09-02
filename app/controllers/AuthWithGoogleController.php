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
            $msg = "Login with Google account successfully !";

            if( !$id_token )
            {
                $this->resp->msg = __("Missing id_token !");
                $this->jsonecho();
            }
            $client = new Google_Client(['client_id' => CLIENT_ID]);  // Specify the CLIENT_ID of the app that accesses the backend

            try 
            {
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
              $firstName = $payload['given_name'];
              $lastName = "";
              // $lastName = $payload['family_name'];
              //$picture = $payload['picture'];
      
            
              try 
              {        
                $User = Controller::model("User", $email);
                
                /**if user does not exist */
                if (!$User->isAvailable()) 
                {
                
                    /**store and set up avatar */
                    //   $tempname = uniqid();
                    //   $ext = "png";
                
                    //   $filepath = UPLOAD_PATH . "/" . $tempname . "." .$ext;
                    //   download_image($picture, $filepath);
                  
                    //   $User->set("email", $email)
                    //       ->set("password", password_hash(uniqid(), PASSWORD_DEFAULT))
                    //       ->set("first_name", $firstname)
                    //       ->set("last_name", $lastname)
                    //       ->set("avatar", $tempname . "." .$ext)
                    //       ->set("is_active", 1)
                    //       ->save();

                    $msg = __("Your account has been created successfully!");
                    
                    $User->set("email", $email)
                            ->set("password", password_hash(uniqid(), PASSWORD_DEFAULT))
                            ->set("first_name", $firstName)
                            ->set("last_name", $lastName)
                            ->set("phone", "")
                            ->set("address","Vietnam")
                            ->set("role", "member")
                            ->set("active", 1)
                            ->set("create_at", date("Y-m-d H:i:s"))
                            ->set("update_at", date("Y-m-d H:i:s"))
                            ->save();
                }
                
                /**if user is inactive */
                if( $User->get("active") == 0 ){
                  $this->resp->msg = __("Account is inactive!");
                  $this->jsonecho();
                }
                
                /**prepare data to send back */
                $data = array(
                    "id"    => (int)$User->get("id"),
                    "email" => $User->get("email"),
                    "first_name" => $User->get("first_name"),
                    "last_name" => $User->get("last_name"),
                    "phone" => $User->get("phone"),
                    "address" => $User->get("address"),
                    "role" => $User->get("role"),
                    "active" => (int)$User->get("active"),
                    "create_at" => $User->get("create_at"),
                    "update_at" => $User->get("update_at")
                );
      
                $payload = $data;
      
                $payload["hashPass"] = md5($User->get("password"));
                $payload["iat"] = time();
                // $jwt = Firebase\JWT\JWT::encode($payload, EC_SALT);
                $jwt = Firebase\JWT\JWT::encode($payload, EC_SALT, 'HS256');
      
                $this->resp->result = 1;
                $this->resp->msg = $msg;
                $this->resp->accessToken = $jwt;
                $this->resp->data = $data;
                
              } 
              catch (\Exception $ex) 
              {
                  $this->resp->msg = __("Oops! Something went wrong. Please try again!");
              }
              $this->jsonecho();
            
        }
    }
?>