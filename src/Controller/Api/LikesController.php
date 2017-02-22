<?php
namespace App\Controller\Api;
use Cake\Event\Event;

class LikesController extends AppController {

    public function beforeFilter(Event $event) {
        parent::beforeFilter($event);
        $this->Auth->allow(['test']);
        $this->loadComponent('Storage');
    }

    public function index($contentId) {
		$limit=20;
		$order="DESC";
		$userId = $this->Auth->user('id');
        $likes = $this->Likes->find('all', [
                    'contain' => [
                        'Users' => [
                            'fields' => [
                                'first_name', 'last_name', 'id', 'profile_picture'
                            ]
                        ]
                    ]
                ])->where(['Likes.content_id' => $contentId])->order(['Likes.id '.$order])->limit($limit);
        foreach ($likes as $like){
            if(!empty($like['user']['profile_picture'])){
                $like['user']['profile_picture']=$this->Storage->getUserProfileImagePath($like['user']['profile_picture']);
            }
        }
        $this->set([
            'success' => TRUE,
            'message' => "Likes Fetched",
            'likes' => $likes,
			'doesThisUserLikesContent'=>$this->__doesThisUserLikesContent($contentId,$userId),
            '_serialize' => ['success', 'message', 'likes','doesThisUserLikesContent']
        ]);
    }



    public function add() {
        $data = $this->request->data;
        $ndata['user_id'] = $this->Auth->user('id');
        $ndata['content_id'] = $data['contentId'];
        $like = $this->Likes->newEntity($ndata);
        //pr($content);exit();
        if ($this->Likes->save($like)) {
            $this->set([
                'success' => TRUE,
                'message' => "You have liked this!!",
                'like' => $like,
                '_serialize' => ['success', 'message', 'like']
            ]);
        } else {
            $this->set([
                'success' => FALSE,
                'message' => "Couldn't Like this!!",
                'like' => $like,
                '_serialize' => ['success', 'message', 'like']
            ]);
        }
    }

}
