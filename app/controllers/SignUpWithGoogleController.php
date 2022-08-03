<?php
    class SignUpWithGoogleController extends Controller
    {
        public function process()
        {
            $method = Input::method();
            if( $method == 'POST')
            {
                $this->loginWithGoogle();
            }
        }


        private function loginWithGoogle()
        {
            /**Step 0 - declare required_fields */
            $this->resp->result = 0;
            
            $required_fields  = array(
                "email", 
                "password", "password-confirm"
            );


            foreach ($required_fields as $field) 
            {
                if (!Input::post($field)) {
                    $this->resp->msg = __("Missing field: ".$field);
                    $this->jsonecho();
                }
            }

            /** Step 1 - declare variable to store value that HTTP sends us */
            $email = strtolower(Input::post("email"));
            $password = Input::post("password");
            $firstName = Input::post("first_name") ? Input::post("first_name") : Input::post("email");
            $lastName = Input::post("last_name") ? Input::post("last_name") : "";
                
            $query = DB::table(TABLE_PREFIX.TABLE_USERS)
                    ->where(TABLE_PREFIX.TABLE_USERS.".email", "=", $email );

            $result = $query->get();

            if( count($result) > 0)
            {
                $User = Controller::model("User", $email);

                if ($User->isAvailable() &&
                    $User->get("active") == 1 &&
                    password_verify($password, $User->get("password"))) 
                {
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

                    $jwt = Firebase\JWT\JWT::encode($payload, EC_SALT, 'HS256');

                    $this->resp->result = 1;
                    $this->resp->msg = __("Log in with google account successfully");
                    $this->resp->accessToken = $jwt;
                    $this->resp->data = $data;

                    $this->jsonecho();
                }
            }
        }
    }
?>