<?php 
    class ProfileController extends Controller
    {
        public function process()
        {
            $request_method = Input::method();
            if( $request_method === 'GET' )
            {
                $this->getProfile();
            }
        }


        private function getProfile()
        {
            $this->resp->result = 0;
            $AuthUser = $this->getVariable("AuthUser");


            $query = DB::table(TABLE_PREFIX.TABLE_USERS)
                    ->where(TABLE_PREFIX.TABLE_USERS.".id", "=", $AuthUser->get("id"))
                    ->select("*");

            $result = $query->get();

            if( count($result) < 0){
                $this->resp->msg = "Your email or password is incorrect ! Try again";
                $this->jsonecho();
            }

            $data =[];

            foreach($result as $element){
                $data = array(
                    "id" => $element->id,
                    "email" => $element->email,
                    "first_name" => $element->first_name,
                    "last_name" => $element->last_name,
                    "role" => $element->role,
                    "active" => $element->active,
                    "create_at" => $element->create_at,
                    "update_at" => $element->update_at
                );
            }

            $this->resp->result = 1;
            $this->resp->data = $data;
            $this->jsonecho();
        }
    }
?>