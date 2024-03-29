<?php 
	/**
	 * Product Model
	 *
	 * @version 1.0
	 * @author Onelab <hello@onelab.co> 
	 * 
	 */
	
	class ProductModel extends DataEntry
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
	    	} else {
	    		$col = "name";
	    	}

	    	if ($col) {
		    	$query = DB::table(TABLE_PREFIX.TABLE_PRODUCTS)
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
	    		"name"=>"",
                "remaining" => "0",
                "manufacturer" => "Dell",
                "price" => "12500000",
                "screen_size" => "15.6",
                "cpu" => "",
                "ram" => "8GB",
                "graphic_card" => "",
                "rom" => "512GB",
                "demand" => "",
                "content" =>"",
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

	    	$id = DB::table(TABLE_PREFIX.TABLE_PRODUCTS)
		    	->insert(array(
		    		"id" => null,
		    		"name"=> $this->get("name"),
                    "remaining" => $this->get("remaining"),
                    "manufacturer" => $this->get("manufacturer"),
                    "price" => $this->get("price"),
                    "screen_size" => $this->get("screen_size"),
                    "cpu" => $this->get("cpu"),
                    "ram" => $this->get("ram"),
                    "graphic_card" => $this->get("graphic_card"),
                    "rom" => $this->get("rom"),
                    "demand" => $this->get("demand"),
                    "content" => $this->get("content"),
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

	    	$id = DB::table(TABLE_PREFIX.TABLE_PRODUCTS)
	    		->where("id", "=", $this->get("id"))
		    	->update(array(
		    		"name"=> $this->get("name"),
                    "remaining" => $this->get("remaining"),
                    "manufacturer" => $this->get("manufacturer"),
                    "price" => $this->get("price"),
                    "screen_size" => $this->get("screen_size"),
                    "cpu" => $this->get("cpu"),
                    "ram" => $this->get("ram"),
                    "graphic_card" => $this->get("graphic_card"),
                    "rom" => $this->get("rom"),
                    "demand" => $this->get("demand"),
                    "content" => $this->get("content"),
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

	    	DB::table(TABLE_PREFIX.TABLE_PRODUCTS)->where("id", "=", $this->get("id"))->delete();
	    	$this->is_available = false;
	    	return true;
	    }
	}
?>