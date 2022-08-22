<?php
    class AdminProductsPhotosController extends Controller
    {
        public function process()
        {
            $AuthUser = $this->getVariable("AuthUser");
            $Route = $this->getVariable("Route");
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
            if($request_method === 'GET')
            {
                $this->getAll();
            }
            else if( $request_method === 'POST')
            {
                $this->save();
            }
            else if( $request_method === 'PUT')
            {
                $this->update();
            }
            else if( $request_method === 'DELETE'){
                $this->delete();
            }
        }


        /**
         * @author Phong-Kaster
         * get photos of a product
         */
        private function getAll(){
            /**Step 1 */
            $this->resp->result = 0;
            $Route = $this->getVariable("Route");


            /**Step 2 */
            if( !$Route->params->product_id ){
                $this->resp->msg = "ID is required !";
                $this->jsonecho();
            }


            /**Step 3 */
            $Product = Controller::model("Product", $Route->params->product_id);
            if( !$Product->isAvailable()){
                $this->resp->msg = "The product doesn't exist !";
                $this->jsonecho();
            }

            
            /**Step 4 */
            try 
            {
                $query = DB::table(TABLE_PREFIX.TABLE_PRODUCTS_PHOTO)
                            ->where(TABLE_PREFIX.TABLE_PRODUCTS_PHOTO.".product_id", 
                                    "=", $Product->get("id") );

                $result = $query->get();

                if( count($result) == 0){
                    $ProductsPhoto = Controller::model("ProductsPhoto");
                    $ProductsPhoto->set("product_id", $Route->params->product_id)
                                  ->set("path", "default.png")
                                  ->set("is_avatar",1)
                                  ->save();
                }

                $data = [];

                /**"path" => UPLOAD_PATH."/".$element->path, */
                foreach($result as $element){
                    $data[] = array(
                        "id" => (int)$element->id,
                        "path" => $element->path,
                        "is_avatar" => (int)$element->is_avatar
                    );
                }

                $this->resp->result = 1;
                $this->resp->product_id = (int)$Product->get("id");
                $this->resp->data = $data;
            } 
            catch (\Exception $ex) {
                $this->resp->msg = $ex->getMessage();
            }
            $this->jsonecho();
        }

        
        /**
         * @author Phong-Kaster
         * add photo to a product
         */
        private function save(){
            /**Step 1 */
            $this->resp->result = 0;
            $Route = $this->getVariable("Route");

            if( !Input::post("product_id") )
            {
                $this->resp->msg = "Product ID is required !";
                $this->jsonecho();
            }


            $is_avatar = Input::post("is_avatar") ? Input::post("is_avatar") : 0;
            $productId = Input::post("product_id");

            $Product = Controller::model("Product", $productId);
            if( !$Product->isAvailable()){
                $this->resp->msg = "The product doesn't exist !";
                $this->jsonecho();
            }

            /**Step 2 - check if file is received or not */
            if (empty($_FILES["file"]) || $_FILES["file"]["size"] <= 0) 
            {
                $this->resp->msg = "Photo is not received !";
                $this->jsonecho();
            }

            
            /**Step 3 - check filename extension */
            $ext = strtolower(pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION));
            $allow = ["jpeg", "jpg", "png"];
            if (!in_array($ext, $allow)) 
            {
                $this->resp->msg = __("Only ".join(",", $allow)." files are allowed");
                $this->jsonecho();
            }


            /**Step 4 - upload file */
            $date = new DateTime();
            $timestamp = $date->getTimestamp();
            $tempname = "product_".$Product->get("id")."_".$timestamp;
            $temp_dir = UPLOAD_PATH;
            if (!file_exists($temp_dir)) {
                mkdir($temp_dir);
            }
            
            $filepath = $temp_dir . "/" . $tempname . "." .$ext;
            if (!move_uploaded_file($_FILES["file"]["tmp_name"], $filepath)) {
                $this->resp->msg = __("Oops! An error occured. Please try again later!");
                $this->jsonecho();
            }

            /**Step 5 - if the uploaded photo is set as avatar, other photos will be not avatar */
            if($is_avatar == 1){
                $query = DB::table(TABLE_PREFIX.TABLE_PRODUCTS_PHOTO)
                        ->where(TABLE_PREFIX.TABLE_PRODUCTS_PHOTO.".product_id", "=", $productId)
                        ->update(array(
                            "is_avatar" => 0
                        ));
            }
            
            /**Step 6 - create a record to the product */
            $ProductsPhoto = Controller::model("ProductsPhoto" );
            $ProductsPhoto->set("product_id", $productId)
                        ->set("path", $tempname.".".$ext)
                        ->set("is_avatar", $is_avatar )
                        ->save();



            $this->resp->result = 1;
            $this->resp->msg = __("Upload successful");
            $this->jsonecho();
        }


        /**
         * @author Phong-Kaster
         * set default avatar
         * 
         * update photo's status (is_avatar)
         * is_avatar = 1 means the default avatar of product
         * is_avatar = 0 means not the default avatar of product
         */
        private function update(){
            /**Step 1 */
            $this->resp->result = 0;
            $Route = $this->getVariable("Route");

            
            if( !$Route->params->product_id )
            {
                $this->resp->msg = "Product ID is required !";
                $this->jsonecho();
            }


            if( !$Route->params->photo_id )
            {
                $this->resp->msg = "Photo id is required !";
                $this->jsonecho();
            }


            /**Step 2 */
            $Product = Controller::model("Product", $Route->params->product_id);
            if( !$Product->isAvailable())
            {
                $this->resp->msg = "The product doesn't exist !";
                $this->jsonecho();
            }


            $ProductsPhoto = Controller::model("ProductsPhoto", $Route->params->photo_id);
            if( !$ProductsPhoto->isAvailable()){
                $this->resp->msg = "Photo id doesn't exist !";
                $this->jsonecho();
            }

            /**Step 3 */
            try 
            {
                $is_avatar = Input::put("is_avatar") ? Input::put("is_avatar") : 0;
                
                /**set photo_id as default avatar */
                $query = DB::table(TABLE_PREFIX.TABLE_PRODUCTS_PHOTO)
                        ->where(TABLE_PREFIX.TABLE_PRODUCTS_PHOTO.".product_id", "=", $Route->params->product_id)
                        ->where(TABLE_PREFIX.TABLE_PRODUCTS_PHOTO.".id", "=", $Route->params->photo_id)
                        ->update(array(
                            "is_avatar" => 1
                        ));
                
                /**set pther photos as not default avatar */
                $query = DB::table(TABLE_PREFIX.TABLE_PRODUCTS_PHOTO)
                    ->where(TABLE_PREFIX.TABLE_PRODUCTS_PHOTO.".product_id", "=", $Route->params->product_id)
                    ->where(TABLE_PREFIX.TABLE_PRODUCTS_PHOTO.".id", "!=", $Route->params->photo_id)
                    ->update(array(
                        "is_avatar" => 0
                    ));

                $this->resp->result = 1;
                $this->resp->msg = "Set default avatar successfully !";
            } 
            catch (\Exception $ex) 
            {
                $this->resp->msg = $ex->getMessage();
            }
            $this->jsonecho();
        }
        

        /**
         * @author Phong-Kaster
         * delete a photo's status
         * Step 1: check if product_id & photo_id is passed
         * Step 2: check Product record & photo record exist or not ?
         * Step 3: delete photo
         * Step 4: if there is no any photo left, create a default photo,
         * Step 5: if deleted photo is avatar, set another one as default
         */
         private function delete(){
            /**Step 1 */
            $this->resp->result = 0;
            $Route = $this->getVariable("Route");

            
            if( !$Route->params->product_id )
            {
                $this->resp->msg = "Product ID is required !";
                $this->jsonecho();
            }


            if( !$Route->params->photo_id )
            {
                $this->resp->msg = "Photo ID is required !";
                $this->jsonecho();
            }


            /**Step 2 */
            $Product = Controller::model("Product", $Route->params->product_id);
            if( !$Product->isAvailable())
            {
                $this->resp->msg = "The product doesn't exist !";
                $this->jsonecho();
            }


            $ProductsPhoto = Controller::model("ProductsPhoto", $Route->params->photo_id);
            if( !$ProductsPhoto->isAvailable()){
                $this->resp->msg = "Photo id doesn't exist !";
                $this->jsonecho();
            }

            /**Step 3 */
            $ProductsPhoto->delete();

            /**Step 4 - check if the photo is deleted & there isn't any photo left */
            try 
            {
                $query = DB::table(TABLE_PREFIX.TABLE_PRODUCTS_PHOTO)
                        ->where(TABLE_PREFIX.TABLE_PRODUCTS_PHOTO.".product_id",
                                "=",
                                $Route->params->product_id);
                $result = $query->get();

                /**There is no any photo left, create a default photo */
                if( count($result) == 0 ){
                    $query = DB::table(TABLE_PREFIX.TABLE_PRODUCTS_PHOTO)
                                ->insert(array(
                                    "id" => null,
                                    "product_id" => $Product->get("id"),
                                    "path" => UPLOAD_PATH."/default.png",
                                    "is_avatar" => 1
                                ));
                }
                /**Get latest photo & set it as default photo */
                else
                {
                    $query = DB::table(TABLE_PREFIX.TABLE_PRODUCTS_PHOTO)
                                ->where(TABLE_PREFIX.TABLE_PRODUCTS_PHOTO.".product_id",
                                    "=",
                                    $Product->get("id"))
                                ->limit(1)
                                ->orderBy("id","desc")
                                ->update(array(
                                    "is_avatar" => 1
                                ));
                }

                $this->resp->result = 1;
                $this->resp->msg = "Photo is deleted successfully !";
            } catch (\Exception $ex) 
            {
                $this->resp->msg = $ex->getMessage();
            }
            $this->jsonecho();
         }
    }
?>