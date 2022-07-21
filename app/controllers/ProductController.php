<?php 
    class ProductController extends Controller{

        public function process()
        {
            $Route = $this->getVariable("Route");
            $request_method = Input::method();
            $product_id = $Route->params->id;

            if( $product_id && $request_method === 'GET'){
                $this->getProductById($product_id );
            }
        }


        /**
         * @author Phong-Kaster
         * get product by id
         */
        private function getProductById($product_id){

            /**Step 1 */
            $this->resp->result = 0;



            /**Step 2 - get the product  */
            $Product = Controller::model("Product", $product_id);
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
                    "id" => $element->id,
                    "path"=> UPLOAD_PATH."/".$element->path,
                    "is_avatar"=>(int)$element->is_avatar
                );
            };

            /**Step 4 - return */
            $this->resp->result = 1;
            $this->resp->msg = "Get product by id successfully !";
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
            $this->resp->photos = $photos;

            $this->jsonecho();
        }

    }
?>