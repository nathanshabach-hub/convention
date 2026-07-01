<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class SchedulingsTable extends Table {

    public function initialize(array $config): void
    {	
		/* $this->belongsTo('Schools', [
            'className' => 'Users',
            'foreignKey' => 'school_id',
            'propertyName' => 'Schools'
        ]);
		
		*/
    }
	
	      

}

?>