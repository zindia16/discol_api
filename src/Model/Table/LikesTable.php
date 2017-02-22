<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class LikesTable extends Table
{
    public function initialize(array $config) {
        $this->addBehavior('Timestamp');
        $this->addBehavior('CounterCache', [
            'Contents' => [
                'like_count' => [
                    
                ]
            ]
        ]);
        $this->belongsTo('Users');
        $this->belongsTo('Contents');
    }

}
