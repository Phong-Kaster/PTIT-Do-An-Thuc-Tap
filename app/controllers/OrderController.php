<?php 
    class OrderController extends Controller
    {
        public function process()
        {
            $AuthUser = $this->getVariable("AuthUser");
            $method = Input::method();
            $Route = $this->getVariable("Route");


            if( !$AuthUser )
            {
                $this->resp->result = 0;
                $this->resp->msg = "There is no authenticated user !";
                $this->jsonecho();
            }

            if( !isset($Route->params->id) )
            {
                $this->resp->result = 0;
                $this->resp->msg = "Order ID is required !";
                $this->jsonecho();
            }


            if( $method == 'GET')
            {
                $this->getOrderById();
            }
            else if( $method == 'POST' )
            {
                $action = Input::post("action");
                switch ($action)
                {
                    case 'orderInformation':
                        $this->modifyOrderInformation();
                        break;
                    case 'orderContent':
                        $this->modifyOrderContent();
                        break;
                    default:
                        break;
                }
            }
            else if($method == 'PUT')
            {
                $this->confirmOrder();
            }

        }


        /**
         * @author Phong-Kaster
         * get order and its content with Id
         */
        private function getOrderById(){

            /**Step 1 */
            $AuthUser = $this->getVariable("AuthUser");
            $Route = $this->getVariable("Route");
            $this->resp->result = 0;
            $data = [];
            $content = [];

            if( !$AuthUser )
            {
                $this->resp->msg = "There is no authenticated user!";
                $this->jsonecho();
            }

            if( !isset($Route->params->id) ){
                $this->resp->msg = "ID is required !";
                $this->jsonecho();
            }


            /**Step 2 - get order  */
            $Order = Controller::model("Order", $Route->params->id);
            if( !$Order->isAvailable() ){
                $this->resp->msg = "This order is not available !";
                $this->jsonecho();
            }

            $query = DB::table(TABLE_PREFIX.TABLE_ORDERS)
                    // ->where(TABLE_PREFIX.TABLE_ORDERS.".user_id", "=", $AuthUser->get("id"))
                    ->where(TABLE_PREFIX.TABLE_ORDERS.".id", "=", $Route->params->id)
                    ->select("*");
                
            $result = $query->first();
            /**Step 3 - get order information */
            $data = array(
                "id"   => $result->id,
                "user_id" => (int)$result->user_id,
                "receiver_phone" => $result->receiver_phone,
                "receiver_address" => $result->receiver_address,
                "receiver_name" => $result->receiver_name,
                "description" =>  $result->description,
                "description" => $result->description,
                "status" => $result->status,
                "total" => (int)$result->total,
                "create_at" => $result->create_at,
                "update_at" => $result->update_at
            );

            // foreach ($result as $element) 
            // {
            //     $data[] = array(
            //         "id"   => $element->id,
            //         "user_id" => (int)$element->user_id,
            //         "receiver_phone" => $element->receiver_phone,
            //         "receiver_address" => $element->receiver_address,
            //         "receiver_name" => $element->receiver_name,
            //         "description" =>  $element->description,
            //         "description" => $element->description,
            //         "status" => $element->status,
            //         "total" => (int)$element->total,
            //         "create_at" => $element->create_at,
            //         "update_at" => $element->update_at
            //     );
            // }

           /**Step 4 - order content */
           /**and then get the order's content */
           $orderContent = DB::table(TABLE_PREFIX.TABLE_ORDERS_CONTENT)
                            ->where(TABLE_PREFIX.TABLE_ORDERS_CONTENT.".order_id", "=", $Route->params->id)
                            ->leftJoin(TABLE_PREFIX.TABLE_PRODUCTS, 
                                        TABLE_PREFIX.TABLE_PRODUCTS.".id", 
                                        "=", 
                                        TABLE_PREFIX.TABLE_ORDERS_CONTENT.".product_id")
                            ->select([
                                TABLE_PREFIX.TABLE_ORDERS_CONTENT.".*",
                                DB::raw(TABLE_PREFIX.TABLE_PRODUCTS.".name as product_name"),
                                DB::raw(TABLE_PREFIX.TABLE_PRODUCTS.".price as product_price"),
                            ]);
            $result = $orderContent->get();

            foreach($result as $element)
            {
                $product_avatar = getProductAvatar($element->product_id);

                $content[] = array(
                    "id" => (int)$element->id,
                    "product_id" => (int)$element->product_id,
                    "product_name" => $element->product_name,
                    "product_price" => (int)$element->product_price,
                    "product_avatar" => $product_avatar,
                    "quantity" => (int)$element->quantity,
                    "price" => (int)$element->price
                );
            }

            /**Step 4 - return */
            $this->resp->result = 1;
            $this->resp->msg = "Get Order by id successfully !";
            $this->resp->data = $data;
            $this->resp->content = $content;
            $this->jsonecho();
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


        /**
         * @author Phong-Kaster
         * client modify order's content
         * Step 1: declare local variable
         * Step 2: get the order with id
         * Step 2.1: order is able to be modified when it's processing | packed | being transported
         * Step 3: check product exists or not ?
         * Step 4: declare product_id, quantity, product_price to calculate total & quantity
         * Step 5: check product_id exists in current order ?
         *      Situation 1: if product_id exits but quantity = 0 => delete
         *      Situation 2: if product_id have existed then increase its quantity one unit
         *      Situation 3: if product_id doesn't exist then create a new order's content
         */
        private function modifyOrderContent(){
            /**Step 1 */
            $this->resp->result = 0;
            $Route = $this->getVariable("Route");


            if( !isset($Route->params->id) ){
                $this->resp->msg = "ID is required !";
                $this->jsonecho();
            }

            /**Step 2 - get the order  */
            $Order = Controller::model("Order", $Route->params->id);
            if( !$Order->isAvailable() ){
                $this->resp->msg = "This order is not available !";
                $this->jsonecho();
            }

            /**Step 2.1 - order is able to be modified when it's processing | packed | being transported */
            $invalid_status = ["verified","packed", "being transported","delivered", "cancel"];
            $current_status = $Order->get("status");
            if( in_array($current_status, $invalid_status)){
                $this->resp->msg = "This order can't be modified because it's ".$current_status;
                $this->jsonecho();
            }


            $required_fields = ["product_id"];
            foreach($required_fields as $field){
                if(!Input::post($field)){
                    $this->resp->msg = "Missing field ".$field;
                    $this->jsonecho();
                }
            }


            /**Step 3*/
            $Product = Controller::model("Product", Input::post("product_id"));
            if( !$Product->isAvailable() ){
                $this->resp->msg = "Product doesn't exist !";
                $this->jsonecho();
            }


            /**Step 4 - declare product_id, quantity, product_price to calculate total & quantity  */
            $product_id = Input::post("product_id");
            $quantity = Input::post("quantity") ? (int)Input::post("quantity") : 0;
            $product_price = (int)$Product->get("price");
            $data = [];//store returned data;


            /**Step 5 - check product_id exists in current order ? */
            $query = DB::table(TABLE_PREFIX.TABLE_ORDERS_CONTENT)
                    ->where(TABLE_PREFIX.TABLE_ORDERS_CONTENT.".order_id","=",$Route->params->id)
                    ->where(TABLE_PREFIX.TABLE_ORDERS_CONTENT.".product_id","=",$product_id)
                    ->select([
                        DB::raw(TABLE_PREFIX.TABLE_ORDERS_CONTENT.".quantity"),
                        DB::raw(TABLE_PREFIX.TABLE_ORDERS_CONTENT.".price")
                    ]);
            
            
            $result = $query->get();

            /** if product_id exits but quantity = 0 => delete */
            if( count($result) > 0 && $quantity == 0)
            {
                $queryUpdate = DB::table(TABLE_PREFIX.TABLE_ORDERS_CONTENT)
                ->where(TABLE_PREFIX.TABLE_ORDERS_CONTENT.".order_id","=",$Route->params->id)
                ->where(TABLE_PREFIX.TABLE_ORDERS_CONTENT.".product_id","=",$product_id)
                ->delete();

                $data = array(
                    "order_id" => $Route->params->id,
                    "product_id" => (int)$product_id,
                    "quantity" =>  (int)$quantity,
                    "product_price" => (int)$Product->get("price")
                );
            }
            /** if product_id have existed then increase its quantity one unit */
            else if( count($result) > 0 && $quantity > 0){
                
                $newQuantity = $quantity;// calculate current quantity with received quantity 
                $newPrice = $newQuantity*$product_price;// new quantity multiple with product_price

                $queryUpdate = DB::table(TABLE_PREFIX.TABLE_ORDERS_CONTENT)
                            ->where(TABLE_PREFIX.TABLE_ORDERS_CONTENT.".order_id","=",$Route->params->id)
                            ->where(TABLE_PREFIX.TABLE_ORDERS_CONTENT.".product_id","=",$product_id)
                            ->update(array(
                                "quantity" => $newQuantity,
                                "price" => $newPrice
                            ));
                $data = array(
                    "order_id" => $Route->params->id,
                    "product_id" =>(int) $product_id,
                    "quantity" =>  (int)$newQuantity,
                    "price" => (int)$newPrice
                );
            }
             /** if product_id doesn't exist then create a new order's content */
            else
            {
                $OrdersContent = Controller::model("OrdersContent");
                $OrdersContent->set("order_id", $Route->params->id)
                            ->set("product_id", $product_id)
                            ->set("price", $product_price*$quantity)
                            ->set("quantity", $quantity)
                            ->save();

                $data = array(
                    "order_id" =>$OrdersContent->get("order_id"),
                    "product_id" =>(int)$OrdersContent->get("product_id"),
                    "quantity" =>(int)$OrdersContent->get("quantity"),
                    "price" =>(int)$OrdersContent->get("price"),
                );
            }


            /**Step 6 - update the "total" field of the order  */
            $queryTotalAmount = DB::table(TABLE_PREFIX.TABLE_ORDERS_CONTENT)
                    ->where(TABLE_PREFIX.TABLE_ORDERS_CONTENT.".order_id","=",$Route->params->id)
                    ->select([
                        DB::raw("SUM( ".TABLE_PREFIX.TABLE_ORDERS_CONTENT.".price) as total_amount")
                    ]);
            
            
            $resultTotalAmount = $queryTotalAmount->get();
            $totalAmount = $resultTotalAmount[0]->total_amount;
           

            $Order->set("update_at", date("Y-m-d H:i:s"))
                ->set("total", $totalAmount)
                ->save();
            try 
            {     
                $this->resp->result = 1;
                $this->resp->msg = "Order content have been modified successfully !";
                $this->resp->total = (int)$Order->get("total");
                $this->resp->update_at = $Order->get("update_at");
                $this->resp->data = $data;
            } 
            catch (\Exception $ex) {
                //throw $th;
                $this->resp->msg = $ex->getMessage();
            }
            $this->jsonecho();
        }


        /**
         * @author Phong-Kaster
         * determine the status of order
         * 
         * Situation 1: if status = "verified" then check products in the order_content, if all quantity of 
         * products > 0, then update product's remaining and update order's status as verified
         * 
         * Situation 2: if status = "cancel" then all quantity of products in the order 
         * content increase equaling with quantity
         * 
         * Step 1: check id
         * Step 2: check order exists or not ? 
         * Step 3: the current order's status must be processing or verified !
         * Step 4: status only accepts 2 value: verified & cancel
         * Step 5a: if change to verified then decrease product's quantity
         * Step 5b: if change to cancel then increase product's quantity
         */
        private function confirmOrder(){
            $this->resp->result = 0;
            $msg = "";
            $Route = $this->getVariable("Route");


            /**Step 1 - check id */
            if( !isset($Route->params->id) ){
                $this->resp->msg = "ID is required !";
                $this->jsonecho();
            }


            /**Step 2 - check order exists or not ?  */
            $Order = Controller::model("Order", $Route->params->id);
            if( !$Order->isAvailable() ){
                $this->resp->msg = "This order is not available !";
                $this->jsonecho();
            }


            /**Step 3 - the current order's status must be processing or verified ! */
            // if( $Order->get("status") != "processing" || $Order->get("status") != "verified"){
            //     $this->resp->msg = "The status of the order is not processing !";
            //     $this->jsonecho();
            // }


            /**Step 4 - status only accepts 2 value: verified & cancel */
            $status = Input::put("status");
            $valid_status = ["verified", "cancel"];
            if( !in_array($status, $valid_status)){
                $this->resp->msg = "There are only 2 status accepted: verified & cancel";
            }


            /**Step 5 - query to get products from the order */
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
            if( count($result) == 0 ){
                $this->resp->msg = "Your order is empty now !";
                $this->jsonecho();
            }

            /**Step 5a - if change from processing to verified then decrease product's quantity */
            if($Order->get("status") == "processing" && $status == "verified"){
                

                $out_of_stock = [];
                /** does product's remaining greater than required quantity, does it ? */
                foreach($result as $element){
                    if( $element->remaining < $element->quantity ){
                        $out_of_stock[] = $element;
                        // $this->resp->msg = "Oops ! ".$element->product_name." is out of stock !";
                        // $this->jsonecho();
                    }
                }

                $message = "Sorry ! ";
                if( count($out_of_stock) > 0 ){
                    foreach( $out_of_stock as $element ){
                        $message .= $element->product_name.",";
                    }

                    $this->resp->msg = $message." are out of stock !";
                    $this->jsonecho();
                }
                

                /** if product's remaining greater than required quantity, update their remaining */
                foreach ($result as $element) {
                    $query = DB::table(TABLE_PREFIX.TABLE_PRODUCTS)
                            ->where(TABLE_PREFIX.TABLE_PRODUCTS.".id", "=", $element->product_id)
                            ->update(array(
                                "remaining" => DB::raw("remaining - ".$element->quantity)
                            ));
                }

                
                /**update order's status from processing to verified */
                $msg = "Your order is verified successfully !";
                $Order->set("status", "verified")
                        ->save();
            }
            /**Step 5b - if change verified to cancel then increase product's quantity */
            else if( $Order->get("status") == "verified" && $status == "cancel"){
                foreach ($result as $element) {
                    $query = DB::table(TABLE_PREFIX.TABLE_PRODUCTS)
                            ->where(TABLE_PREFIX.TABLE_PRODUCTS.".id", "=", $element->product_id)
                            ->update(array(
                                "remaining" => DB::raw("remaining + ".$element->quantity)
                            ));
                }

                /**update order's status from processing to verified */
                $msg = "Your order is cancelled successfully !";
                $Order->set("status", "cancel")
                        ->save();
            }
            else
            {
                //$msg = "Your order's status now is ".$Order->get("status")." & can't do this action !";
                $this->resp->msg = "Your order's status now is ".$Order->get("status")." & can't do this action !";
                $this->jsonecho();
            }

            $this->resp->result = 1;
            $this->resp->msg = $msg;
            $this->jsonecho();
        }
    }
?>