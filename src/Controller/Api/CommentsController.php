<?php

namespace App\Controller\Api;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Auth\DefaultPasswordHasher;

class CommentsController extends AppController {

    public function beforeFilter(Event $event) {
        parent::beforeFilter($event);
        $this->Auth->allow(['index', 'logout', 'signup', 'test']);
        $this->loadComponent('Storage');
    }

    public function index($contentId) {
        $comments = $this->Comments->find('all', [
                    'contain' => [
                        'Users' => [
                            'fields' => [
                                'first_name', 'last_name', 'id', 'profile_picture'
                            ]
                        ]
                    ]
                ])->where(['Comments.content_id' => $contentId])->order(['Comments.id DESC']);
        foreach ($comments as $comment){
            if(!empty($comment['user']['profile_picture'])){
                $comment['user']['profile_picture']=$this->Storage->getUserProfileImagePath($comment['user']['profile_picture']);
            }
        }
        $this->set([
            'success' => TRUE,
            'message' => "Comments Fetched",
            'comments' => $comments,
            '_serialize' => ['success', 'message', 'comments']
        ]);
    }

    public function add() {
        $data = $this->request->data;
        $ndata['user_id'] = $this->Auth->user('id');
        $ndata['content_id'] = $data['contentId'];
        $ndata['text'] = $data['text'];
        $ndata['is_published'] = 1;
        $comment = $this->Comments->newEntity($ndata);
        //pr($content);exit();
        if ($this->Comments->save($comment)) {
            $this->set([
                'success' => TRUE,
                'message' => "Comment Saved",
                'comment' => $comment,
                '_serialize' => ['success', 'message', 'comment']
            ]);
        } else {
            $this->set([
                'success' => FALSE,
                'message' => "Comment Saving failed",
                'comment' => $comment,
                '_serialize' => ['success', 'message', 'comment']
            ]);
        }
    }

}
