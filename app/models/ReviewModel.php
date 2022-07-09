<?php 
	/**
	 * Category Model
	 *
	 * @version 1.0
	 * @author Onelab <hello@onelab.co> 
	 * 
	 */
	
	class ReviewModel extends DataEntry
	{	
		/**
		 * Extend parents constructor and select entry
		 * @param mixed $uniqid Value of the unique identifier
		 */
	    public function __construct($uniqid=0)
	    {
	        parent::__construct();
	        $this->select($uniqid);
	    }



	    /**
	     * Select entry with uniqid
	     * @param  int|string $uniqid Value of the any unique field
	     * @return self       
	     */
	    public function select($uniqid)
	    {
	    	if (is_int($uniqid) || ctype_digit($uniqid)) {
	    		$col = $uniqid > 0 ? "id" : null;
	    	} else if (filter_var($uniqid, FILTER_VALIDATE_EMAIL)) {
	    		$col = "author_email";
	    	}


	    	if ($col) {
		    	$query = DB::table(TABLE_PREFIX.TABLE_REVIEWS)
			    	      ->where($col, "=", $uniqid)
			    	      ->limit(1)
			    	      ->select("*");
		    	if ($query->count() == 1) {
		    		$resp = $query->get();
		    		$r = $resp[0];

		    		foreach ($r as $field => $value)
		    			$this->set($field, $value);

		    		$this->is_available = true;
		    	} else {
		    		$this->data = array();
		    		$this->is_available = false;
		    	}
	    	}

	    	return $this;
	    }


	    /**
	     * Extend default values
	     * @return self
	     */
	    public function extendDefaults()
	    {
	    	$defaults = array(
	    		"product_id" => "",
				"content" => "",
				"parent_id" => NULL,
				"status" => "processing",
				"author_name"=>"",
				"author_email"=>"",
                "create_at" => date("Y-m-d H:i:s"),
				"update_at" => date("Y-m-d H:i:s")
	    	);


	    	foreach ($defaults as $field => $value) {
	    		if (is_null($this->get($field)))
	    			$this->set($field, $value);
	    	}
	    }


	    /**
	     * Insert Data as new entry
	     */
	    public function insert()
	    {
	    	if ($this->isAvailable())
	    		return false;

	    	$this->extendDefaults();

	    	$id = DB::table(TABLE_PREFIX.TABLE_REVIEWS)
		    	->insert(array(
		    		"id" => null,
		    		"product_id" => $this->get("product_id"),
                    "content" => $this->get("content"),
                    "parent_id" => $this->get("parent_id"),
                    "status" => $this->get("status"),
                    "author_name" => $this->get("author_name"),
                    "author_email" => $this->get("author_email"),
                    "create_at" => date("Y-m-d H:i:s"),
					"update_at" => date("Y-m-d H:i:s")
		    	));

	    	$this->set("id", $id);
	    	$this->markAsAvailable();
	    	return $this->get("id");
	    }


	    /**
	     * Update selected entry with Data
	     */
	    public function update()
	    {
	    	if (!$this->isAvailable())
	    		return false;

	    	$this->extendDefaults();

	    	$id = DB::table(TABLE_PREFIX.TABLE_REVIEWS)
	    		->where("id", "=", $this->get("id"))
		    	->update(array(
		    		"product_id" => $this->get("product_id"),
                    "content" => $this->get("content"),
                    "parent_id" => $this->get("parent_id"),
                    "status" => $this->get("status"),
                    "author_name" => $this->get("author_name"),
                    "author_email" => $this->get("author_email"),
                    "create_at" => date("Y-m-d H:i:s"),
					"update_at" => date("Y-m-d H:i:s")
		    	));

	    	return $this;
	    }


	    /**
		 * Remove selected entry from database
		 */
	    public function delete()
	    {
	    	if(!$this->isAvailable())
	    		return false;

	    	DB::table(TABLE_PREFIX.TABLE_REVIEWS)->where("id", "=", $this->get("id"))->delete();
	    	$this->is_available = false;
	    	return true;
	    }
	}
?>