<?php 
    Class Categories extends DataList
    {
        public function __construct()
        {
            $this->setQuery(DB::table(TABLE_PREFIX.TABLE_CATEGORIES));
        }
    }
?>