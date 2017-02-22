<?php

namespace App\Controller\Api;

use Cake\Event\Event;
use Cake\Auth\DefaultPasswordHasher;

class ContentsController extends AppController {


    public $paginate = [
        'page' => 1,
        'limit' => 10,
        'maxLimit' => 100,
        'fields' => [
            'id', 'name', 'description'
        ],
        'sortWhitelist' => [
            'id','created'
        ]
    ];

    public function beforeFilter(Event $event) {
        parent::beforeFilter($event);
        $this->Auth->allow(['signup', 'test','testPaginate']);
        $this->loadComponent('Storage');
    }

    public function testPaginate(){
        $contents = $this->
        $this->set([
            'success' => TRUE,
            'message' => "Content fetched",
            'contents' => $contents,
            '_serialize' => ['success', 'message', 'contents']
        ]);
    }

    function extractImgOrVideo($text) {
        $header = [];
        $image_url = "";
        $video_url = "";
        preg_match('/https?:\/\/[^ ]+?(?:\.jpg|\.png|\.gif)/', $text, $image_url);
        preg_match('~\S*\bwww\.youtube\.com\S*~', $text, $video_url);
        if (!empty($image_url[0])) {
            $header['image_url'] = $image_url[0];
        }
        if (!empty($video_url[0])) {
            $header['video_url'] = $video_url[0];
        }
        return $header;
    }

    function parseContent($content) {
        if (!empty($content)) {
            $header = $this->extractImgOrVideo($content->text);
        }
        exit();
    }

    public function index($contentType = null) {
        $whereArray =[];
        $limit =25;
        $order = "DESC";
        if($contentType){
            $whereArray['Contents.content_type']=$contentType;
        }

        if($this->request->query('userId')){
            $whereArray['Contents.user_id']=$this->request->query('userId');
        }
        if($this->request->query('limit')){
            $limit = $this->request->query('limit');
        }
        if($this->request->query('order')){
            $order = $this->request->query('order');
        }
        $contents = $this->Contents->find('all', [
            'contain' => [
                'Users' => [
                    'fields' => ['id', 'first_name', 'last_name', 'profile_picture']
                ]
            ]
        ])->where($whereArray)->order(['Contents.id' => $order])->limit($limit);


        foreach ($contents as $content) {
            $content['header'] = $this->extractImgOrVideo($content->text);
			$content['doesThisUserLikesContent']=$this->__doesThisUserLikesContent($content->id,$this->Auth->user('id'));
            if(!empty($content['user']['profile_picture'])){
                $content['user']['profile_picture']=$this->Storage->getUserProfileImagePath($content['user']['profile_picture']);
            }
        }

        $this->set([
            'success' => TRUE,
            'message' => "Content fetched",
            'contents' => $contents,
            '_serialize' => ['success', 'message', 'contents']
        ]);
    }

    public function view($postId, $contentType = 'post') {
        $content = $this->Contents->find('all', [
                    'contain' => [
                        'Users' => [
                            'fields' => ['id', 'first_name', 'last_name', 'profile_picture']
                        ],
                        'Comments' => [
                            'Users' => [
                                'fields' => ['id', 'first_name', 'last_name', 'profile_picture']
                            ]
                        ]
                    ]
                ])->where([
                    'content_type' => $contentType,
                    'Contents.id' => $postId
                ])->first();
        $content['header'] = $this->extractImgOrVideo($content->text);
        if(!empty($content['user']['profile_picture'])){
            $content['user']['profile_picture']=$this->Storage->getUserProfileImagePath($content['user']['profile_picture']);
        }
		$content['doesThisUserLikesContent']=$this->__doesThisUserLikesContent($content->id,$this->Auth->user('id'));		
        $this->set([
            'success' => TRUE,
            'message' => "Content fetched",
            'content' => $content,
            '_serialize' => ['success', 'message', 'content']
        ]);
    }

    public function add() {
        $data = $this->request->data;
        $data['user_id'] = $this->Auth->user('id');
        $content = $this->Contents->newEntity($data);
        //pr($content);exit();
        $this->Contents->save($content);
        $this->set([
            'success' => TRUE,
            'message' => "Content fetched",
            'content' => $content,
            '_serialize' => ['success', 'message', 'content']
        ]);
    }

    public function signup() {
        if ($this->request->is('post')) {
            $user = $this->Users->newEntity();
            $data = $this->request->data;
            $user = $this->Users->patchEntity($user, $data);
            if ($this->Users->save($user)) {
                $this->set([
                    'success' => TRUE,
                    'message' => "Signup Successfull",
                    '_serialize' => ['success', 'message']
                ]);
            } else {
                $this->set([
                    'success' => FALSE,
                    'message' => "Signup Failed",
                    '_serialize' => ['success', 'message']
                ]);
            }
        }
    }

}
