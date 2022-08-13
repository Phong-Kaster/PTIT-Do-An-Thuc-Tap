<?php 
    class AdminOrdersContentController extends Controller{
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
                $this->getOrderContent();
            }
            else if( $request_method === 'POST'){
                $this->save();
            }
            else if( $request_method === 'DELETE'){
                $this->delete();
            }
        }


        /**
         * @author Phong-Kaster
         * get content of an order
         */
        private function getOrderContent(){
            /**Step 1 */
            $this->resp->result = 0;
            $Route = $this->getVariable("Route");


            if( !$Route->params->id ){
                $this->resp->msg = "ID is required !";
                $this->jsonecho();
            }

            /**Step 2 - get the product  */
            $Order = Controller::model("Order", $Route->params->id);
            if( !$Order->isAvailable() ){
                $this->resp->msg = "This order is not available !";
                $this->jsonecho();
            }


            /**Step 3 */
            try 
            {
                //code...
                $query = DB::table(TABLE_PREFIX.TABLE_ORDERS_CONTENT)
                        ->join(TABLE_PREFIX.TABLE_PRODUCTS,
                                TABLE_PREFIX.TABLE_PRODUCTS.".id",
                                "=",
                                TABLE_PREFIX.TABLE_ORDERS_CONTENT.".product_id")
                        ->where(TABLE_PREFIX.TABLE_ORDERS_CONTENT.".order_id", "=", $Route->params->id )
                        ->select([
                            TABLE_PREFIX.TABLE_ORDERS_CONTENT.".*",
                            DB::raw(TABLE_PREFIX.TABLE_PRODUCTS.".name as product_name"),
                            DB::raw(TABLE_PREFIX.TABLE_PRODUCTS.".price as product_price"),
                            DB::raw(TABLE_PREFIX.TABLE_ORDERS_CONTENT.".price as price")
                        ]);


                $result = $query->get();

                $data = [];

                foreach($result as $element){
                    $data[] = array(
                        "id" => (int)$element->id,
                        "product_id" => (int)$element->product_id,
                        "product_name" => $element->product_name,
                        "product_avatar" => $this->getAvatar($element->id),
                        "product_price" => (int)$element->product_price,
                        "quantity" => (int)$element->quantity,
                        "price" => (int)$element->price
                    );
                }

                $this->resp->result = 1;
                $this->resp->order = array(
                    "id" => $Order->get("id"),
                    "user_id" => (int)$Order->get("user_id"),
                    "receiver_phone" => $Order->get("receiver_phone"),
                    "receiver_address" => $Order->get("receiver_address"),
                    "receiver_name" => $Order->get("receiver_name"),
                    "description" => $Order->get("description"),
                    "status" => $Order->get("status"),
                    "total" => $Order->get("total"),
                    "create_at" => $Order->get("create_at"),
                    "update_at" => $Order->get("update_at")
                );
                $this->resp->data = $data;
            } 
            catch (\Exception $ex) 
            {
                //throw $th;
                $this->resp->msg = $ex->getMessage();
            }
            $this->jsonecho();
        }


        /**
         * @author Phong-Kaster
         * modify order's content
         * price is calculated by product_price * quantity;
         * this price is the amount of money with one product
         * Step 1: declare local variable
         * Step 2: check the order exists or not ?
         * Step 3: check the product exists or not ?
         * Step 4: declare product_id, quantity, product_price to calculate total & quantity
         * Step 5: check product_id exists in current order ?
         *      Situation 1: if product_id have existed then increase its quantity one unit
         *      Situation 2: if product_id exits but quantity = 0 => delete
         *      Situation 3: if product_id doesn't exist then create a new order's content
         */
        private function save(){
            /**Step 1 */
            $Route = $this->getVariable("Route");
            $this->resp->result = 0;

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


            /**Step 2.1 - order is able to be modified when it's processing or packed */
            $invalid_status = ["being transported", "delivered", "cancel"];
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
            $quantity = Input::post("quantity") ? Input::post("quantity") : 0;
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
         * delete a order content
         */
        private function delete(){
             /**Step 1 */
             $Route = $this->getVariable("Route");
             $this->resp->result = 0;
 
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
 
 
             /**Step 2.1 - order is able to be modified when it's processing or packed */
             $invalid_status = ["being transported", "delivered", "cancel"];
             $current_status = $Order->get("status");
             if( in_array($current_status, $invalid_status)){
                 $this->resp->msg = "This order can't be modified because it's ".$current_status;
                 $this->jsonecho();
             }
 
 
             $required_fields = ["product_id"];
             foreach($required_fields as $field){
                 if(!Input::delete($field)){
                     $this->resp->msg = "Missing field ".$field;
                     $this->jsonecho();
                 }
             }

             /**Step 3*/
            $Product = Controller::model("Product", Input::delete("product_id"));
            if( !$Product->isAvailable() ){
                $this->resp->msg = "Product doesn't exist !";
                $this->jsonecho();
            }



            /**Step 4 - declare product_id, quantity, product_price to calculate total & quantity  */
            $product_id = Input::delete("product_id");
            $product_price = (int)$Product->get("price");
            $data = [];//store returned data;

            /**Step 5 - get quantity out to prepare update order total amount again */
            $query = DB::table(TABLE_PREFIX.TABLE_ORDERS_CONTENT)
                    ->where(TABLE_PREFIX.TABLE_ORDERS_CONTENT.".order_id","=",$Route->params->id)
                    ->where(TABLE_PREFIX.TABLE_ORDERS_CONTENT.".product_id","=",$product_id)
                    ->select([
                       DB::raw(TABLE_PREFIX.TABLE_ORDERS_CONTENT.".quantity as productQuantity")
                    ]);

            $result = $query->get();
            if(count($result) > 0)
            {
                $quantity = $result[0]->productQuantity;
            }
            else{
                $this->resp->msg = "The order doesn't have this product";
                $this->jsonecho();
            }
            



            try 
            {
                /**Step 5 - delete product from the order */
                $query = DB::table(TABLE_PREFIX.TABLE_ORDERS_CONTENT)
                    ->where(TABLE_PREFIX.TABLE_ORDERS_CONTENT.".order_id","=",$Route->params->id)
                    ->where(TABLE_PREFIX.TABLE_ORDERS_CONTENT.".product_id","=",$product_id)
                    ->delete();

                /**Step 6 - update order's total amount */
                $totalAmount = (int)($Order->get("total") - $quantity*$product_price);
                $Order = Controller::model("Order", $Route->params->id);
                $Order->set("update_at", date("Y-m-d H:i:s"))
                      ->set("total", $totalAmount)
                      ->save();

                      
                $this->resp->result = 1;
                $this->resp->msg = "Product have been removed from order successfully";
                $this->resp->total = $Order->get("total");
                $this->resp->update_at = $Order->get("update_at");
            } 
            catch (\Exception $ex) 
            {
                $this->resp->msg = $ex->getMessage();
            }
            $this->jsonecho();
        } 

        /**
         * @author Phong-Kaster
         * this function is used to get avatar for products
         */
        private function getAvatar($id){
            if( !$id ){
                return;
            }

            $query = DB::table(TABLE_PREFIX.TABLE_PRODUCTS_PHOTO)
            ->join(TABLE_PREFIX.TABLE_PRODUCTS,
                        TABLE_PREFIX.TABLE_PRODUCTS.".id",
                        "=",
                        TABLE_PREFIX.TABLE_PRODUCTS_PHOTO.".product_id")
            ->where(TABLE_PREFIX.TABLE_PRODUCTS_PHOTO.".is_avatar","=", 1)
            ->where(TABLE_PREFIX.TABLE_PRODUCTS.".id","=", $id)
            ->select([
                TABLE_PREFIX.TABLE_PRODUCTS_PHOTO.".path"
            ]);
            $res = $query->limit(1)->get();
            
            $tempres = [];
            $result = "";

            if( count($res) > 0 ){
                foreach($res as $r){
                    $tempres[] = $r->path;
                }

                $result = UPLOAD_PATH."/".$tempres[0];
            }
            else{
                $result = UPLOAD_PATH."/default.png";
            }



            return $result;
        }
    }
?>