<?php

namespace App\Controller\Api;

use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;

class AppController extends Controller{

    public function initialize(){
        parent::initialize();

        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');
        $this->loadComponent('Auth',[
            'storage' => 'Memory',
            'authenticate' => [
                'Form','Basic'
            ],
            'unauthorizedRedirect' => false
        ]);
    }

    public function beforeRender(Event $event)
    {
        if (!array_key_exists('_serialize', $this->viewVars) &&
            in_array($this->response->type(), ['application/json', 'application/xml'])
        ) {
            $this->set('_serialize', true);
        }
    }

	public function __doesThisUserLikesContent($contentId,$userId){
		$likesTable = TableRegistry::get('Likes');
		$likes = $likesTable->find('all')->where([
				'content_id'=>$contentId,
				'user_id'=>$userId
			])->count();
		if($likes){
			return true;
		}
		return false;
	}
}
