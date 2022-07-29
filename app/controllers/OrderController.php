<?php 
    class OrderController extends Controller
    {
        public function process()
        {
            $method = Input::method();

            if( $method === 'POST')
            {
                $this->modifyOrderInformation();
            }
        }

        /**
         * @author Phong-Kaster
         * modify order information
         * this functions does not check if users own order or not or not ?
         */
        private function modifyOrderInformation()
        {
            $this->resp->result = 0;
            $Route = $this->getVariable("Route");

            if( !isset($Route->params->id) )
            {
                $this->resp->msg = "Order ID is required!";
                $this->jsonecho();
            }

            /**Step 1 - check order exists or not ? */
            $Order = Controller::model("Order", $Route->params->id);
            if( !$Order->isAvailable() ){
                $this->resp->msg = "This order is not available !";
                $this->jsonecho();
            }

            $receiver_address = Input::post("receiver_address");
            $receiver_name = Input::post("receiver_name");
            $receiver_phone = Input::post("receiver_phone");
            $description = Input::post("description");
            $total = Input::post("total") ? Input::post("total") : 0;// this is total amount of the order, not price of product

            /**does order have any product ? if not, refuse update total amount of the order */
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
            if( count($result) == 0 && $total > 0 ){
                $this->resp->msg = "Your order is empty now then can not update order's total amount !";
                $this->jsonecho();
            }

            $required_fields = ["receiver_phone","receiver_address","receiver_name"];

            foreach($required_fields as $field){
                if( !Input::post($field) ){
                    $this->resp->msg = "Missing field ".$field;
                    $this->jsonecho();
                }
            }

            /**Step 2 - check phone number */
            if( strlen($receiver_phone) < 10 ){
                $this->resp->msg = "Phone number has at least 10 number !";
                $this->jsonecho();
            }

            $phone_number_validation = isNumber($receiver_phone);
            if( !$phone_number_validation ){
                $this->resp->msg = "This is not a valid phone number. Please, try again !";
                $this->jsonecho();
            }

            /**Step 3 - check name - only letters and space */
            $name_validation = isVietnameseName($receiver_name);
            if( $name_validation != 1 ){
                $this->resp->msg = "Receiver name only has letters and space";
                $this->jsonecho();
            }


            /**Step 4 - check address */
            $address_validation = isAddress($receiver_address);
            if( $address_validation != 1){
                $this->resp->msg = "Address only has letters, space & comma";
                $this->jsonecho();
            }


            /**Step 5 - save */
            try 
            {
                //code...
                $Order = Controller::model("Order", $Route->params->id);
                $Order->set("receiver_phone", $receiver_phone)
                    ->set("receiver_address", $receiver_address)
                    ->set("receiver_name", $receiver_name)
                    ->set("description", $description)
                    ->set("total", $total)
                    ->set("update_at", date("Y-m-d H:i:s"))
                    ->save();

                $this->resp->result = 1;
                $this->resp->msg = "Order's information have been updated successfully !";
                $this->resp->data = array(
                    "id" => $Order->get("id"),
                    "user_id" => (int)$Order->get("user_id"),
                    "receiver_phone" => $Order->get("receiver_phone"),
                    "receiver_address" => $Order->get("receiver_address"),
                    "receiver_name" => $Order->get("receiver_name"),
                    "description" => $Order->get("description"),
                    "status"   => $Order->get("status"),
                    "total"     => (int)$Order->get("total"),
                    "create_at" => $Order->get("create_at"),
                    "update_at" => $Order->get("update_at")
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