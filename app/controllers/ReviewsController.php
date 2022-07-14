<?php 
    /**
     * there are 3 status of reviews: processing published and removed
     * the table contains every reviews include questions, feedback,..
     * 
     * because this controller is for users so that their review's status is published by default.
     */
    class ReviewsController extends Controller
    {
        public function process()
        {            

            $request_method = Input::method();

            if( $request_method === 'POST'){
                $this->reply();
            }
            else if( $request_method === 'DELETE'){
                $this->delete();
            }
            else if( $request_method === 'PUT'){
                $this->edit();
            }
        }




        /**
         * @author Phong-Kaster
         * for users
         * default status of review is published
         * create or reply a comment in a product
         */
        private function reply(){
            /**Step 1 */
            $this->resp->result = 0;
            $AuthUser = $this->getVariable("AuthUser");


            /**Step 2 */
            $required_fields = ["product_id","content","author_name","author_email"];
            foreach($required_fields as $field)
            {
                if( !Input::post($field) ){
                    $this->resp->msg = "Missing field ".$field;
                    $this->jsonecho();
                }
            }


            /**Step 3 */
            $product_id = Input::post("product_id");
            $content = Input::post("content");
            $parent_id = Input::post("parent_id") ? Input::post("parent_id") : NULL ;
            // $status = Input::post("status") ? Input::post("status") : "published";
            $status = "published";
            $author_name = (String)Input::post("author_name");
            $author_email = (String)Input::post("author_email");
            $create_at = date("Y-m-d H:i:s");
            $update_at = date("Y-m-d H:i:s");


            /**Step 4 - check input data */
            /**Step 4.1 - Product_id */
            $Product = Controller::model("Product", $product_id);
            if( !$Product->isAvailable() ){
                $this->resp->msg = "Product doesn't exist";
                $this->jsonecho();
            }


            /**Step 4.1 - content */
            if( !strlen($content) > 0 ){
                $this->resp->msg = "Review's content can not be empty !";
                $this->jsonecho();
            }

            /**Step 4.2 - parent_id */
            if( Input::post("parent_id"))
            {
                $Review = Controller::model("Review", $parent_id);
                if( !$Review->isAvailable() ){
                    $this->resp->msg = "Parent review doesn't exist !";
                    $this->jsonecho();
                }
            }
            


            /**Step 4.3 - author_name & author_email */
            if( $AuthUser ){
                $author_name = $AuthUser->get("first_name")." ".$AuthUser->get("last_name");
                $author_email = $AuthUser->get("email"); 
            }


            /**Step 4.3.1 - is author_email valid ? */
            if (!filter_var($author_email, FILTER_VALIDATE_EMAIL)) {
                $this->resp->msg = __("Email is not valid!");
                $this->jsonecho();
            }


            /**Step 4.3.2 - is author_name valid ? */
            $name_validation = isVietnameseName($author_name);
            if( $name_validation != 1 ){
                $this->resp->msg = "Receiver name only has letters and space";
                $this->jsonecho();
            }

            /**Step 4.4 - status */
            $valid_status = ["processing","published","removed"];
            if( !in_array($status, $valid_status) ){
                $this->resp->msg = "Review's status is not valid. Only processing, published & removed";
                $this->jsonecho();
            }
            


            /**Step 5 */
            try 
            {
                $Review = Controller::model("Review");
                $Review->set("product_id", $product_id)
                    ->set("content", $content)
                    ->set("parent_id", $parent_id )
                    ->set("status", $status)
                    ->set("author_name", (String)$author_name)
                    ->set("author_email", $author_email)
                    ->set("create_at", $create_at)
                    ->set("update_at", $update_at)
                    ->save();

                $this->resp->result = 1;
                $this->resp->msg = "Review is created successfully !";
                $this->resp->data = array(
                    "id" => $Review->get("id"),
                    "content" => $Review->get("content"),
                    "parent_id" => $Review->get("parent_id"),
                    "status" => $Review->get("status"),
                    "author_name" => $Review->get("author_name"),
                    "author_email" => $Review->get("author_email"),
                    "create_at" => $Review->get("create_at"),
                    "update_at" => $Review->get("update_at")
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
         * change review's status and sub-reviews' status from 'published' to 'removed'
         */
        private function delete(){
            /**Step 0 */
            $this->resp->result = 0;
            $AuthUser = $this->getVariable("AuthUser");
            $Route = $this->getVariable("Route");

            /**Step 1 */
            if( !$Route->params->id ){
                $this->resp->msg = "Review ID is required !";
                $this->jsonecho();
            }

            /**Step 2 */
            $Review = Controller::model("Review",  $Route->params->id);
            if( !$Review->isAvailable() ){
                $this->resp->msg = "Review doesn't exits";
                $this->jsonecho();
            }

            $id = $Route->params->id;

            /**Step 3 */
            try 
            {
                $query = DB::table(TABLE_PREFIX.TABLE_REVIEWS)
                    ->where(TABLE_PREFIX.TABLE_REVIEWS.'.id', "=", $id)
                    ->orWhere(TABLE_PREFIX.TABLE_REVIEWS.'.parent_id', "=", $id)
                    ->update(array(
                        "status"=>"removed"
                    ));

                $this->resp->result = 1;
                $this->resp->msg = "Review is removed successfully !";
            } 
            catch (\Exception $ex) 
            {
                $this->resp->msg = $ex->getMessage();
            }
            $this->jsonecho();
        }


        /**
         * @author Phong-Kaster
         * 
         * edit an existing review's content
         * this function is available for account user only
         * 
         * Step 1 - declare local variable
         * Step 2 - check does AuthUser exist ?
         * Step 3 - check required fields
         * Step 4 - deprecated - does product_id find an available product ?
         * Step 5 - declare referenced variables to easily use
         * Step 6 - check input data
         * Step 7 - save changes
         */
        private function edit(){
            /**Step 1 - declare local variable */
            $this->resp->result = 0;
            $AuthUser = $this->getVariable("AuthUser");
            $Route = $this->getVariable("Route");

            
            if( !$AuthUser ){
                $this->resp->msg = "Edit reviews is available for user having account only !";
                $this->jsonecho();
            }

            
            /**Step 2 - does the review exist ? */
            $Review = Controller::model("Review", $Route->params->id);
            if( !$Review->isAvailable() ){
                $this->resp->msg = "Review does not exist !";
                $this->jsonecho();
            }


            /**Step 3 - check required fields */
            $required_fields = ["content"];
            foreach($required_fields as $field)
            {
                if( !Input::put($field) ){
                    $this->resp->msg = "Missing field ".$field;
                    $this->jsonecho();
                }
            }

            /**Step 4 - does product_id find an available product ? */
            // $Product = Controller::model("Product", Input::put("product_id"));
            // if( !$Product->isAvailable() ){
            //     $this->resp->msg = "Product doesn't exist !";
            //     $this->jsonecho();
            // }


            /**Step 5 - declare referenced variables to easily use */
            //$product_id = (int)Input::put("product_id");
            $content = Input::put("content");
            $parent_id = Input::put("parent_id") ? (int)Input::put("parent_id") : NULL ;
            // $status = Input::put("status") ? Input::put("status") : "published";
            $status = "published";
            //$author_name = (String)Input::put("author_name");
            //$author_email = (String)Input::put("author_email");
            $create_at = date("Y-m-d H:i:s");
            $update_at = date("Y-m-d H:i:s");


            /**Step 6 - check input data */
            /**Step 6.0 - Product_id */
            // $Product = Controller::model("Product", $product_id);
            // if( !$Product->isAvailable() ){
            //     $this->resp->msg = "Product doesn't exist";
            //     $this->jsonecho();
            // }


            /**Step 6.1 - content */
            if( !strlen($content) > 0 ){
                $this->resp->msg = "Review's content can not be empty !";
                $this->jsonecho();
            }


            /**Step 6.2 - parent_id */
            if( Input::put("parent_id"))
            {
                $Review = Controller::model("Review", $parent_id);
                if( !$Review->isAvailable() ){
                    $this->resp->msg = "Parent review doesn't exist !";
                    $this->jsonecho();
                }
            }
            


            /**Step 6.3 - author_name & author_email */
            if( $AuthUser ){
                $author_name = $AuthUser->get("first_name")." ".$AuthUser->get("last_name");
                $author_email = $AuthUser->get("email"); 
            }


            /**Step 6.3.1 - is author_email valid ? */
            if (!filter_var($author_email, FILTER_VALIDATE_EMAIL)) {
                $this->resp->msg = __("Email is not valid!");
                $this->jsonecho();
            }


            /**Step 6.3.2 - is author_name valid ? */
            $name_validation = isVietnameseName($author_name);
            if( $name_validation != 1 ){
                $this->resp->msg = "Receiver name only has letters and space";
                $this->jsonecho();
            }


            /**Step 6.4 - status */
            $valid_status = ["processing","published","removed"];
            if( !in_array($status, $valid_status) ){
                $this->resp->msg = "Review's status is not valid. Only processing, published & removed";
                $this->jsonecho();
            }
            


            /**Step 7 - save changes */
            try 
            {
                $Review = Controller::model("Review",$Route->params->id);
                $Review->set("content", $content)
                    ->set("parent_id", $parent_id )
                    ->set("status", $status)
                    ->set("create_at", $create_at)
                    ->set("update_at", $update_at)
                    ->save();

                $this->resp->result = 1;
                $this->resp->msg = "Review have been modified successfully !";
                $this->resp->data = array(
                    "id" => $Review->get("id"),
                    "content" => $Review->get("content"),
                    "parent_id" => $Review->get("parent_id"),
                    "status" => $Review->get("status"),
                    "author_name" => $Review->get("author_name"),
                    "author_email" => $Review->get("author_email"),
                    "create_at" => $Review->get("create_at"),
                    "update_at" => $Review->get("update_at")
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