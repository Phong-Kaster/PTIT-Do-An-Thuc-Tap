<?php 
    class ProfileController extends Controller
    {
        public function process()
        {
            $AuthUser = $this->getVariable("AuthUser");
            // Authentication
            if (!$AuthUser)
            {
                header("Location: ".APPURL."/login");
                exit;
            }

            $request_method = Input::method();
            if( $request_method === 'GET' )
            {
                $this->getProfile();
            }
            else if( $request_method === 'POST')
            {
                $this->changeInformation();
            }
        }


        private function getProfile()
        {
            $this->resp->result = 0;
            $AuthUser = $this->getVariable("AuthUser");


            $query = DB::table(TABLE_PREFIX.TABLE_USERS)
                    ->where(TABLE_PREFIX.TABLE_USERS.".id", "=", $AuthUser->get("id"))
                    ->select("*");

            $result = $query->get();

            if( count($result) < 0){
                $this->resp->msg = "Your email or password is incorrect ! Try again";
                $this->jsonecho();
            }

            $data =[];

            foreach($result as $element){
                $data = array(
                    "id" => (int)$element->id,
                    "email" => $element->email,
                    "first_name" => $element->first_name,
                    "last_name" => $element->last_name,
                    "role" => $element->role,
                    "active" => (int)$element->active,
                    "create_at" => $element->create_at,
                    "update_at" => $element->update_at
                );
            }

            $this->resp->result = 1;
            $this->resp->data = $data;
            $this->jsonecho();
        }

        /**
         * @author Phong-Kaster
         * change account information
         */
        private function changeInformation()
        {
            $this->resp->result = 0;

            /**Step 1 */
            $requiredFields = ["email", "first_name", "last_name", "phone", "address"];

            foreach($requiredFields as $field)
            {
                if( !Input::post($field) )
                {
                    $this->resp->msg = "Missing field ".$field;
                    $this->jsonecho();
                }
            }



            /**Step 2 - declare local variable*/
            $email = Input::post("email");
            $firstName = Input::post("first_name");
            $lastName = Input::post("last_name");
            $phone = Input::post("phone");
            $address = Input::post("address");



            /**Step 3 - check input data */
            /**Step 3.1 - filter email */
            if (!filter_var( $email, FILTER_VALIDATE_EMAIL)) {
                $this->resp->msg = __("Email is not valid!");
                $this->jsonecho();
            } 
            


            /**Step 3.2 - check first name & last name - only letters and space */
            $first_name_validation = isVietnameseName($firstName);
            if( $first_name_validation != 1 ){
                $this->resp->msg = "First name only has letters and space";
                $this->jsonecho();
            }

            $last_name_validation = isVietnameseName($lastName);
            if( $last_name_validation != 1 ){
                $this->resp->msg = "Last name only has letters and space";
                $this->jsonecho();
            }


            /**Step 3.3 - check phone */
            $phone_number_validation = isNumber($phone);
            if( !$phone_number_validation ){
                $this->resp->msg = "This is not a valid phone number. Please, try again !";
                $this->jsonecho();
            }


            /**Step 3.4 - check address - only letters and space */
            $address_validation = isAddress($address);
            if( $address_validation != 1){
                $this->resp->msg = "Address only has letters, space & comma";
                $this->jsonecho();
            }


            /**Step 4 - does user exist */
            $User = Controller::model("User", $email);
            if( !$User->isAvailable() )
            {
                $this->resp->result = "There is no account matching with ".$email;
                $this->jsonecho();
            }


            /**Step 5 - save changes */
            try 
            {
                $User->set("first_name", $firstName)
                    ->set("last_name", $lastName)
                    ->set("phone", $phone)
                    ->set("address", $address)
                    ->set("update_at", date("Y-m-d H:i:s"))
                    ->save();

                 $this->resp->result = 1;
                 $this->resp->msg = "Account information have been changed successfully !";
                 $this->resp->data = array(
                    "email" => (int)$User->get("email"),
                    "first_name" => $User->get("first_name"),
                    "last_name" => $User->get("last_name"),
                    "phone" => $User->get("phone"),
                    "address" => $User->get("address"),
                    "role" => (int)$User->get("role"),
                    "active" => $User->get("active"),
                    "create_at" => $User->get("create_at"),
                    "update_at" => $User->get("update_at")
                 );
            } 
            catch (\Exception $ex) 
            {
                $this->resp->msg = $ex->getMessage();
            }
            $this->jsonecho();
        }
    }
?>