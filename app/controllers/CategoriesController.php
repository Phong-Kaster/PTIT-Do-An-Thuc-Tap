<?php 
    class CategoriesController extends Controller{
        /**
         * Process
         */
        public function process()
        {   
            $Route = $this->getVariable("Route");
            $request_method = Input::method();

            if( $request_method === 'GET')
            {
                $this->getAll();
            }

        }

        /**
         * @author Phong-Kaster
         * get all categories
         */
        private function getAll()
        {
            $this->resp->result = 0;
            $data = [];
            $quantity = 0;

            
            try 
            {
                //code...
                $query = DB::table(TABLE_PREFIX.TABLE_CATEGORIES)
                            ->select("*")
                            ->orderBy(TABLE_PREFIX.TABLE_CATEGORIES.".id", "asc");

                $result   = $query->get();
                $quantity = count($result);

                foreach( $result as $element)
                {
                    $data[] = array(
                        "id"            => (int)$element->id,
                        "name"          => $element->name,
                        "description"   => $element->description,
                        "position"      => (int)$element->position,
                        "parent_id"     => (int)$element->parent_id,
                        "slug"          => $element->slug
                    );
                }

                $this->resp->result = 1;
                $this->resp->msg = "Categories are gotten successfully !";
                $this->resp->quantity = $quantity;
                $this->resp->data = $data;
            } 
            catch (Exception $ex) 
            {
                $this->resp->msg = $ex->message;
            }
            $this->jsonecho();
        }
    }
?>