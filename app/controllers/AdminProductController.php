<?php
    class AdminProductController extends Controller
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
                $this->getProductById();
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
        private function getProductById(){

            /**Step 1 */
            $Route = $this->getVariable("Route");
            $this->resp->result = 0;

            if( !isset($Route->params->id) ){
                $this->resp->msg = "ID is required !";
                $this->jsonecho();
            }


            /**Step 2 - get the product  */
            $Product = Controller::model("Product", $Route->params->id);
            if( !$Product->isAvailable() ){
                $this->resp->msg = "This product is not available !";
                $this->jsonecho();
            }


            /**Step 3 - get its photos */
            $photos = [];
            $queryPhotos = DB::table(TABLE_PREFIX.TABLE_PRODUCTS_PHOTO)
                        ->where(TABLE_PREFIX.TABLE_PRODUCTS_PHOTO.".product_id", "=", $Product->get("id"));
            
            $result = $queryPhotos->get();

            foreach($result as $element){
                $photos[] = array(
                    "id" => (int)$element->id,
                    "path"=> $element->path,
                    "is_avatar"=>(int)$element->is_avatar
                );
            };

            /**Step 4 - return */
            $this->resp->result = 1;
            $this->resp->msg = "Get product by id successfully !";
            $this->resp->data =  array(
                "id"   =>         (int)$Product->get("id"),
                "name" =>         $Product->get("name"),
                "remaining" =>    (int)$Product->get("remaining"),
                "manufacturer" => $Product->get("manufacturer"),
                "price" =>        (int)$Product->get("price"),
                "screen_size" =>  $Product->get("screen_size"),
                "cpu" =>          $Product->get("cpu"),
                "ram" =>          $Product->get("ram"),
                "graphic_card" => $Product->get("graphic_card"),
                "rom" =>          $Product->get("rom"),
                "demand" =>       $Product->get("demand"),
                "content" =>      $Product->get("content"),
                "create_at" =>    $Product->get("create_at"),
                "update_at" =>    $Product->get("update_at")
            );
            $this->resp->photos = $photos;

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


            /**Step 2 - get the product  */
            $Product = Controller::model("Product", $Route->params->id);
            if( !$Product->isAvailable() ){
                $this->resp->msg = "This product is not available !";
                $this->jsonecho();
            }


            /**Step 3 - check if some mandatory fields is missed */
            $required_fields = [
                "name","price",
                "screen_size","cpu",
                "ram","graphic_card",
                "rom"
            ];

            foreach($required_fields as $field){
                if( !Input::put($field))
                {
                    $this->resp->msg = "Missing required field ".$field;
                    $this->jsonecho(); 
                }
            }

            /**Step 4 - verification */
            /**Step 4.1 - name */
            $name = Input::put("name");
            // if($name){
            //     $Product = Controller::model("Product", $name);
            //     if( $Product->isAvailable() ){
            //         $this->resp->msg = "There is another product having the same name !";
            //         $this->jsonecho();
            //     }
            // }
            
            $remaining = Input::put("remaining");



            /**Step 4.2 - manufacturer */
            $manufacturer = Input::put("manufacturer");
            if( is_numeric($manufacturer) ){
                $this->resp->msg = "Manufacturer can not be a number !";
                $this->jsonecho();
            }


            /**Step 4.3 - 0 < price < 200.000.000 */
            $price = (int)Input::put("price");
            if( $price < 0 || $price > 200000000 ){
                $this->resp->msg = "Price range is 0 < price < 200.000.000";
                $this->jsonecho(); 
            }


            /**Step 4.4 - screen_size */
            $screen_size = (float)Input::put("screen_size");
            if( $screen_size < 10){
                $this->resp->msg = "Screen size must greater than 10 inch !";
                $this->jsonecho();
            }


            /**Step 4.5 - cpu */
            $cpu = Input::put("cpu");


            /**Step 4.6 - ram */
            $ram = Input::put("ram");


            /**Step 4.7 - graphic card */
            $graphic_card = Input::put("graphic_card");

            
            /**Step 4.8 - rom */
            $rom = Input::put("rom");


            /**Step 4.9 - demand */
            $valid_demand = ["", "gaming","design","office","student","business","lightweight"];
            $demand = Input::put("demand");

            if( !in_array($demand, $valid_demand) ){
                $this->resp->msg = "There are 5 types of valid demand: gaming, design, office, student, business, lightweight";
                $this->jsonecho();
            }

            /**Step 4.10 - content */
            (string)$content = (String)Input::put("content");
            if( !$content ){
                $content = __("Laptop ".(string)$name." từ nhà sản xuất ".
                (string)$manufacturer." là một laptop hiện đại, đa dụng & rất thời trang !");
            }


            $Product = Controller::model("Product", $Route->params->id);

            /**Step 5 */
            try 
            {        
                $Product->set("name", $name)
                        ->set("remaining", $remaining)
                        ->set("manufacturer", $manufacturer)
                        ->set("price", $price)
                        ->set("screen_size", $screen_size)
                        ->set("cpu", $cpu)
                        ->set("ram", $ram)
                        ->set("graphic_card", $graphic_card)
                        ->set("rom", $rom)
                        ->set("demand", $demand)
                        ->set("content", $content)
                        ->set("update_at", date("Y-m-d H:i:s"))
                        ->save();
                    
                $this->resp->result = 1;
                $this->resp->msg = __("Product is updated successfully !");
                $this->resp->data =  array(
                        "id"   =>         $Product->get("id"),
                        "name" =>         $Product->get("name"),
                        "remaining" =>    $Product->get("remaining"),
                        "manufacturer" => $Product->get("manufacturer"),
                        "price" =>        $Product->get("price"),
                        "screen_size" =>  $Product->get("screen_size"),
                        "cpu" =>          $Product->get("cpu"),
                        "ram" =>          $Product->get("ram"),
                        "graphic_card" => $Product->get("graphic_card"),
                        "rom" =>          $Product->get("rom"),
                        "demand" =>       $Product->get("demand"),
                        "content" =>      $Product->get("content"),
                        "create_at" =>    $Product->get("create_at"),
                        "update_at" =>    $Product->get("update_at")
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
                $Product = Controller::model("Product", $Route->params->id);
                if( !$Product->isAvailable() ){
                    $this->resp->msg = "This product is not on hand !";
                    $this->jsonecho();
                }

                /**Step 3 -  */
                try {
                    // $query = DB::table(TABLE_PREFIX.TABLE_ORDERS_CONTENT)
                    //             ->join(TABLE_PREFIX.TABLE_PRODUCTS,
                    //                     TABLE_PREFIX.TABLE_PRODUCTS.".id",
                    //                     "=",
                    //                     TABLE_PREFIX.TABLE_ORDERS_CONTENT.".product_id");

                    // $result = $query->get();

                    // $resultSize = count($result);

                    // if( $resultSize > 0){
                    //     $this->resp->msg = "This product can't be removed because it was reference in OrdersContent table";
                    //     $this->jsonecho();
                    // }


                    // $Product->delete();

                    $Product->set("remaining", 0)
                            ->save();

                    $this->resp->result = 1;
                    $this->resp->msg = "The product's remaining is set to 0 !";
                } catch (\Exception $ex) {
                    $this->resp->msg = $ex->getMessage();
                }
                $this->jsonecho();
            }
    }
?>