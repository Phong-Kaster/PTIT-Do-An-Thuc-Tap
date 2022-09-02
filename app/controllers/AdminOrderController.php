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
                $this->modifyOrder();
            }
            else if( $request_method === 'DELETE'){
                $this->delete();
            }
        }


        /**
         * @author Phong-Kaster
         * get order by id
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
                "user_id" => (int)$Order->get("user_id"),
                "receiver_phone" => $Order->get("receiver_phone"),
                "receiver_address" => $Order->get("receiver_address"),
                "receiver_name" => $Order->get("receiver_name"),
                "description" =>  $Order->get("description"),
                "description" => $Order->get("description"),
                "status" => $Order->get("status"),
                "total" => (int)$Order->get("total"),
                "create_at" => $Order->get("create_at"),
                "update_at" => $Order->get("update_at")
            );

            $this->jsonecho();
        }

        /**
         * @author Phong-Kaster
         * modify an order
         * 
         * Step 1 - declare local variable
         * Step 2 - check required fields
         * Step 3 - try to pick the order up with id
         * Step 3.1 - only processing | verified | packed | being transported then order can be modified
         * Step 3.2 - check phone number
         * Step 3.3 - check name - only letters and space
         * Step 3.4 - check address - only letters and space
         * Step 4 - is status of the order valid ?
         * valid status is processing | verified | packed | "being transported" | delivered | cancel
         * Step 5 - query from TABLE ORDER'S CONTENT and get product_id, remaining, quantity & product_name
         *      Step 5a - if $status == processing and status change to ("verified", "packed", "being transported", "delivered") 
         * then decrease products remaining
         *      Step 5b - if $status == ("verified", "packed", "being transported") and status change to (cancel) 
         * then increase products remaining
         */
        private function modifyOrder(){
            /**Step 1 - declare local variable */
            $Route = $this->getVariable("Route");
            $this->resp->result = 0;

            if( !isset($Route->params->id) ){
                $this->resp->msg = "ID is required !";
                $this->jsonecho();
            }


            /**Step 2 - check required fields - no need to check duplicate with id */
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



            /**Step 3 - get the order  */
            $Order = Controller::model("Order", $id);
            if( !$Order->isAvailable() ){
                $this->resp->msg = "This Order is not available !";
                $this->jsonecho();
            }


            // $user_id = $Order->get("user_id") != NULL ? $Order->get("user_id") : NULL;

            /**Step 3.1 - only processing | verified | packed | being transported then order can be modified */
            $invalid_status = ["delivered", "cancel"];
            $current_status = $Order->get("status");
            if( in_array($current_status, $invalid_status)){
                $this->resp->msg = "This order can not be modified when delivered or cancelled !";
                $this->jsonecho();
            }


            /**Step 3.2 - check phone number */
            if( strlen($receiver_phone) < 10 ){
                $this->resp->msg = "Phone number has at least 10 number !";
                $this->jsonecho();
            }

            $phone_number_validation = isNumber($receiver_phone);
            if( !$phone_number_validation ){
                $this->resp->msg = "This is not a valid phone number. Please, try again !";
                $this->jsonecho();
            }

            /**Step 3.3 - check name - only letters and space */
            $name_validation = isVietnameseName($receiver_name);
            if( $name_validation != 1 ){
                $this->resp->msg = "Receiver name only has letters and space";
                $this->jsonecho();
            }


            /**Step 3.4 - check address - only letters and space */
            $address_validation = isAddress($receiver_address);
            if( $address_validation != 1){
                $this->resp->msg = "Address only has letters, space & comma";
                $this->jsonecho();
            }



            /**Step 4 - is status of the order valid ?
             * valid status is processing | verified | packed | "being transported" | delivered | cancel */
            $valid_status = ["processing", "verified", "packed", "being transported", "delivered", "cancel"];
            if( !in_array($status, $valid_status)){
                $this->resp->msg = "Status is not valid, only has processing, verified, packed, being transported, delivered, cancel";
                $this->jsonecho();
            }


            /**Step 5 - query from TABLE ORDER'S CONTENT and get product_id, remaining, quantity & product_name*/
            $query = DB::table(TABLE_PREFIX.TABLE_ORDERS_CONTENT)
                        ->leftJoin(TABLE_PREFIX.TABLE_PRODUCTS, 
                                TABLE_PREFIX.TABLE_PRODUCTS.".id",
                                "=",
                                TABLE_PREFIX.TABLE_ORDERS_CONTENT.".product_id")
                        ->where(TABLE_PREFIX.TABLE_ORDERS_CONTENT.".order_id", "=", $Order->get("id"))
                        ->select([
                            DB::raw(TABLE_PREFIX.TABLE_ORDERS_CONTENT.".product_id"),
                            DB::raw(TABLE_PREFIX.TABLE_PRODUCTS.".remaining"),
                            DB::raw(TABLE_PREFIX.TABLE_ORDERS_CONTENT.".quantity"),
                            DB::raw(TABLE_PREFIX.TABLE_PRODUCTS.".name as product_name")
                        ]);
                    
            $result = $query->get();


            /**Step 5a - if $status == processing and status change to ("verified", "packed", "being transported", "delivered") 
             * then decrease products remaining
             */
            
            /**valid_increase_status is status accepted to decrease products remaining */
            $valid_decrease_status = ['verified', 'packed', 'being transported', 'delivered' ];

            if($Order->get("status") == 'processing' && 
                in_array($status, $valid_decrease_status))
            {
                /** is the current order empty ? if yes, stop this function */
                if( count($result) == 0 ){
                    $this->resp->msg = "Your order is empty now !";
                    $this->jsonecho();
                }

                /** does product's remaining greater than required quantity, does it ? */
                foreach($result as $element){
                    if( $element->remaining < $element->quantity ){
                        $this->resp->msg = "Oops ! ".$element->product_name." is out of stock !";
                        $this->jsonecho();
                    }
                }

                /** if product's remaining greater than required quantity, update their remaining */
                foreach ($result as $element) {
                    $query = DB::table(TABLE_PREFIX.TABLE_PRODUCTS)
                            ->where(TABLE_PREFIX.TABLE_PRODUCTS.".id", "=", $element->product_id)
                            ->update(array(
                                "remaining" => DB::raw("remaining - ".$element->quantity)
                            ));
                }
            }


            /**Step 5b - if $status == ("verified", "packed", "being transported") and status change to (cancel) then
             * increase products remaining
             */
            $valid_increase_status = ['verified', 'packed', 'being transported'];

            if( in_array($Order->get("status"), $valid_increase_status) &&
                $status == 'cancel')
            {
                /** is the current order empty ? if yes, stop this function */
                if( count($result) == 0 ){
                    $this->resp->msg = "Your order is empty now !";
                    $this->jsonecho();
                }

                foreach ($result as $element) {
                    $query = DB::table(TABLE_PREFIX.TABLE_PRODUCTS)
                            ->where(TABLE_PREFIX.TABLE_PRODUCTS.".id", "=", $element->product_id)
                            ->update(array(
                                "remaining" => DB::raw("remaining + ".$element->quantity)
                            ));
                }
            }


            /**Step 6 - store the order's information */
            try 
            {
                //code...
                $Order
                    ->set("receiver_name", $receiver_name)
                    ->set("receiver_address", $receiver_address)
                    ->set("receiver_phone", $receiver_phone)
                    ->set("description", $description)
                    ->set("update_at", date("Y-m-d H:i:s"))
                    ->set("status", $status)
                    ->save();

                $this->resp->result = 1;
                $this->resp->msg = "Order is modified successfully !";
                $this->resp->data = array(
                    "id"   => $Order->get("id"),
                    "user_id" => (int)$Order->get("user_id"),
                    "receiver_phone" => $Order->get("receiver_phone"),
                    "receiver_address" => $Order->get("receiver_address"),
                    "receiver_name" => $Order->get("receiver_name"),
                    "description" =>  $Order->get("description"),
                    "description" => $Order->get("description"),
                    "status" => $Order->get("status"),
                    "total" => (int)$Order->get("total"),
                    "create_at" => $Order->get("create_at"),
                    "update_at" => $Order->get("update_at")
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
            $invalid_status = ["being transported","delivered","verified"];
            $current_status = $Order->get("status");
            if( in_array($current_status, $invalid_status)){
                $this->resp->msg = "This order status is ".$current_status." and can't do this action";
                $this->jsonecho();
            }

            try {
                //code...
                $query = DB::table(TABLE_PREFIX.TABLE_ORDERS)
                        ->join(TABLE_PREFIX.TABLE_ORDERS_CONTENT,
                            TABLE_PREFIX.TABLE_ORDERS_CONTENT.".order_id",
                            "=",
                            TABLE_PREFIX.TABLE_ORDERS.".id")
                        ->where(TABLE_PREFIX.TABLE_ORDERS.".id", "=", $Route->params->id)
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