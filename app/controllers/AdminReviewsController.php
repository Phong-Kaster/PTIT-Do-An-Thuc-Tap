<?php 
    /**
     * there are 2 status of reviews: published and removed
     * the table contains every reviews include questions, feedback,..
     */
    class AdminReviewsController extends Controller
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
                $this->getAll();
            }
            else if( $request_method === 'POST'){
                $this->reply();
            }
            else if( $request_method === 'DELETE'){
                $this->delete();
            }
            else if( $request_method === 'PUT'){
                $this->restore();
            }
        }

        /**
         * @author Phong-Kaster
         * list all comments from database
         */
        private function getAll(){
            /**Step 1 */
            $this->resp->result = 0;
            $AuthUser = $this->getVariable("AuthUser");


            /**Step 1.1 - get basic filter condition */
            $order          = Input::get("order");
            $search         = Input::get("search");
            $length         = Input::get("length") ? (int)Input::get("length") : 10;
            $start          = Input::get("start") ? (int)Input::get("start") : 0;


            /**Step 2 */
            $data = [];
            try 
            {
                $query = DB::table(TABLE_PREFIX.TABLE_REVIEWS)
                    ->select([
                        TABLE_PREFIX.TABLE_REVIEWS.".*"
                    ]);


                /**Step 2.1 - search filter - name | manufacturer | cpu | demand | graphic_card*/
                $search_query = trim( (string)$search );
                if($search_query){
                    $query->where(function($q) use($search_query)
                    {
                        $q->where(TABLE_PREFIX.TABLE_REVIEWS.".content", 'LIKE', $search_query.'%')
                        ->orWhere(TABLE_PREFIX.TABLE_REVIEWS.".author_name", 'LIKE', $search_query.'%')
                        ->orWhere(TABLE_PREFIX.TABLE_REVIEWS.".author_email", "LIKE", $search_query.'%');
                    }); 
                }


                /**Step 2.2 - orderBy filter */
                if( $order && isset($order["column"]) && isset($order["dir"]))
                {
                    $type = $order["dir"];
                    $validType = ["asc","desc"];
                    $sort =  in_array($type, $validType) ? $type : "desc";


                    $column_name = trim($order["column"]) != "" ? trim($order["column"]) : "id";
                    $column_name = str_replace(".", "_", $column_name);
    

                    if(in_array($column_name, ["author_name","author_email"])){
                        $query->orderBy(DB::raw($column_name. " * 1"), $sort);
                    }else{
                        $query->orderBy($column_name, $sort);
                    }
                } 
                else 
                {
                    $query->orderBy("id","desc");
                }


                /**Step 2.3 - length filter * start filter*/
                $query->limit($length ? $length : 10)->offset($start ? $start : 0);
                $result = $query->get();


                foreach ($result as $element) {
                    $data[] = array(
                        "id" => $element->id,
                        "product_id" => $element->product_id,
                        "content" => $element->content,
                        "parent_id" => $element->parent_id,
                        "status" => $element->status,
                        "author_name" => $element->author_name,
                        "author_email" => $element->author_email,
                        "create_at" => $element->create_at,
                        "update_at" => $element->update_at
                    );
                }

                /**Step 2.4 */
                $this->resp->result = 1;
                $this->resp->quantity = count($result);
                $this->resp->data = $data;
            } 
            catch (\Exception $ex) 
            {
                $this->resp->msg = $ex->getMessage();
            }
            $this->jsonecho();
        }


        /**
         * @author Phong-Kaster
         * create or reply a comment in a product
         */
        private function reply(){
            /**Step 1 */
            $this->resp->result = 0;
            $AuthUser = $this->getVariable("AuthUser");


            /**Step 2 */
            $required_fields = ["product_id","content"];
            foreach($required_fields as $field){
                if( !Input::post($field) ){
                    $this->resp->msg = "Missing field ".$field;
                    $this->jsonecho();
                }
            }


            /**Step 3 */
            $product_id = Input::post("product_id");
            $content = Input::post("content");
            $parent_id = Input::post("parent_id") ? Input::post("parent_id") : NULL ;
            $status = Input::post("status") ? Input::post("status") : "processing";
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
            if( $AuthUser->isAdmin() ){
                $author_name = $AuthUser->get("first_name")." ".$AuthUser->get("last_name");
                $author_email = $AuthUser->get("email"); 
            }
            else
            {
                $this->resp->msg = "Author name & author email could not be empty !";
                $this->jsonecho();
            }
            

            /**Step 4.4 - status */
            $valid_status = ["processing","published","removed"];
            if( !in_array($status, $valid_status) ){
                $this->resp->msg = "Review's status is not valid. Only processing, published & removed";
                $this->jsonecho();
            }
            if( $AuthUser->isAdmin() && $AuthUser->get("active") ){
                $status = "published";
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
         * change review's status from 'removed' to 'published'
         */
        private function restore(){
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
                    ->update(array(
                        "status"=>"published"
                    ));

                $this->resp->result = 1;
                $this->resp->msg = "Review is restored successfully !";
            } 
            catch (\Exception $ex) 
            {
                $this->resp->msg = $ex->getMessage();
            }
            $this->jsonecho();
        }
    }
?>