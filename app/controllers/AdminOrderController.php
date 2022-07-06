<?php
    class AdminOrderController extends Controller
    {
        public function process()
        {
            $AuthUser = $this->getVariable("AuthUser");
            //Auth
            if (!$AuthUser)
            {
                header("Location: ".APPURL."/login");
                exit;
            }
            else if( !$AuthUser->isAdmin() )
            {
                header("Location: ".APPURL."/dashboard");
                exit;
            }
            

            $request_method = Input::method();

            if($request_method === 'GET'){
                $this->getOrderById();
            }
            else if( $request_method === 'PUT'){
                $this->save();
            }
            else if( $request_method === 'DELETE'){
                $this->delete();
            }
        }


        /**
         * @author Phong-Kaster
         * get product by id
         */
        private function getOrderById(){

            /**Step 1 */
            $Route = $this->getVariable("Route");
            $this->resp->result = 0;

            if( !isset($Route->params->id) ){
                $this->resp->msg = "ID is required !";
                $this->jsonecho();
            }


            /**Step 2 - get the product  */
            $Order = Controller::model("Order", $Route->params->id);
            if( !$Order->isAvailable() ){
                $this->resp->msg = "This order is not available !";
                $this->jsonecho();
            }


            /**Step 3 - get its photos */
           

            /**Step 4 - return */
            $this->resp->result = 1;
            $this->resp->msg = "Get Order by id successfully !";
            $this->resp->data =  array(
                "id"   => $Order->get("id"),
                "user_id" => $Order->get("user_id"),
                "receiver_phone" => $Order->get("receiver_phone"),
                "receiver_address" => $Order->get("receiver_address"),
                "receiver_name" => $Order->get("receiver_name"),
                "description" =>  $Order->get("description"),
                "description" => $Order->get("description"),
                "status" => $Order->get("status"),
                "total" => $Order->get("total"),
                "create_at" => $Order->get("create_at"),
                "update_at" => $Order->get("update_at")
            );

            $this->jsonecho();
        }

        /**
         * @author Phong-Kaster
         * modify a product
         */
        private function save(){
            /**Step 1 */
            $Route = $this->getVariable("Route");
            $this->resp->result = 0;

            if( !isset($Route->params->id) ){
                $this->resp->msg = "ID is required !";
                $this->jsonecho();
            }


            /**Step 2 - no need to check duplicate with id */
            $required_fields = ["receiver_phone","receiver_address","receiver_name"];

            foreach($required_fields as $field){
                if( !Input::put($field) ){
                    $this->resp->msg = "Missing field ".$field;
                    $this->jsonecho();
                }
            }


            $id                 = $Route->params->id;
            $receiver_phone     = Input::put("receiver_phone");
            $receiver_address   = Input::put("receiver_address");
            $receiver_name      = Input::put("receiver_name");
            $description        = Input::put("description");
            $update_at          = date("Y-m-d H:i:s");
            $status             = Input::put("status");



            /**Step 2 - get the product  */
            $Order = Controller::model("Order", $id);
            if( !$Order->isAvailable() ){
                $this->resp->msg = "This Order is not available !";
                $this->jsonecho();
            }


            $user_id = $Order->get("user_id") != NULL ? $Order->get("user_id") : NULL;

            /**Step 2.1 - only processing | packed then order can be modified */
            $invalid_status = ["being transported", "delivered", "cancel"];
            $current_status = $Order->get("status");
            if( in_array($current_status, $invalid_status)){
                $this->resp->msg = "This order can not be modified when being transported, delivered or canceled !";
                $this->jsonecho();
            }


            /**Step 2.2 - check phone number */
            if( strlen($receiver_phone) < 10 ){
                $this->resp->msg = "Phone number has at least 10 number !";
                $this->jsonecho();
            }

            $phone_number_validation = isNumber($receiver_phone);
            if( !$phone_number_validation ){
                $this->resp->msg = "This is not a valid phone number. Please, try again !";
                $this->jsonecho();
            }

            /**Step 2.3 - check name - only letters and space */
            $name_validation = isVietnameseName($receiver_name);
            if( $name_validation != 1 ){
                $this->resp->msg = "Receiver name only has letters and space";
                $this->jsonecho();
            }


            /**Step 2.4 - check name - only letters and space */
            $address_validation = isAddress($receiver_address);
            if( $address_validation != 1){
                $this->resp->msg = "Address only has letters, space & comma";
                $this->jsonecho();
            }


            /**Step 3 - create order with default status is pending 
             * valid status is pending | packing | delivered | cancel */
            $valid_status = ["processing", "packed", "being transported", "delivered", "cancel"];
            if( !in_array($status, $valid_status)){
                $this->resp->msg = "Status is not valid, only has processing, packed, being transported, delivered, cancel";
                $this->jsonecho();
            }

            try 
            {
                //code...
                $Order->set("user_id", NULL)
                    ->set("receiver_name", $receiver_name)
                    ->set("receiver_address", $receiver_address)
                    ->set("receiver_phone", $receiver_phone)
                    ->set("description", $description)
                    ->set("update_at", date("Y-m-d H:i:s"))
                    ->set("status", $status)
                    ->save();

                $this->resp->result = 0;
                $this->resp->msg = "Order is modified successfully !";
                $this->resp->data = array(
                    "id" => $Order->get("id"),
                    "user_id" => $Order->get("user_id"),
                    "receiver_name"=> $Order->get("receiver_name"),
                    "receiver_address"=> $Order->get("receiver_address"),
                    "receiver_phone"=> $Order->get("receiver_phone"),
                    "description"=> $Order->get("description"),
                    "status"=> $Order->get("status"),
                    "update_at"=> $Order->get("update_at")
                );
            } 
            catch (\Exception $ex) {
                $this->resp->msg = $ex->getMessage();
            }
            $this->jsonecho();
        }


        /**
         * @author Phong-Kaster
         * we won't remove the product because it is referenced by many table.
         * We just set its remaining to zero - 0.
         */
        private function delete(){
            /**Step 1 */
            $Route = $this->getVariable("Route");
            $this->resp->result = 0;

            if( !isset($Route->params->id) ){
                $this->resp->msg = "ID is required !";
                $this->jsonecho();
            }

            /**Step 2 - get the product  */
            $Order = Controller::model("Order", $Route->params->id);
            if( !$Order->isAvailable() ){
                $this->resp->msg = "This order is not available !";
                $this->jsonecho();
            }

            /**Step 2.1 - only processing | packed then order can be modified */
            $invalid_status = ["being transported"];
            $current_status = $Order->get("status");
            if( in_array($current_status, $invalid_status)){
                $this->resp->msg = "This order can not be delete when being transported !";
                $this->jsonecho();
            }

            try {
                //code...
                $query = DB::table(TABLE_PREFIX.TABLE_ORDERS)
                        ->join(TABLE_PREFIX.TABLE_ORDERS_CONTENT,
                            TABLE_PREFIX.TABLE_ORDERS_CONTENT.".order_id",
                            "=",
                            TABLE_PREFIX.TABLE_ORDERS.".id")
                        ->select([
                            TABLE_PREFIX.TABLE_ORDERS_CONTENT.".id"
                        ]);
                $result = $query->get();
                if( count($result) > 0 ){
                    $this->resp->msg = "This order can't be deleted, it's holding reference to order's content";
                    $this->jsonecho();
                }
                else{
                    $Order->delete();
                }

                $this->resp->result = 1;
                $this->resp->msg = "Order is deleted successfully !";
            } catch (\Exception $ex) {
                $this->resp->msg = $ex->getMessage();
            }
            $this->jsonecho();
        }
    }
?>