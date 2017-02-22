<?php
namespace App\Controller\Api;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Auth\DefaultPasswordHasher;

class UsersController extends AppController
{

    public function beforeFilter(Event $event){
        parent::beforeFilter($event);
        $this->Auth->allow(['login','logout','signup','test','isEmailExists','isUserExists']);
        $this->loadComponent('Storage');
    }

    public function updateProfile(){
        $res['success'] = false;
        $res['message'] = 'expecting post data';
        if($this->request->is('post')){
            $data = $this->request->data();
            $user = $this->Users->get($this->Auth->user('id'));
            if(!empty($data['file'])){
                $fileName = $this->Auth->user('username').'_cover_image.'.$data['file_ext'];
                $file_path =  WWW_ROOT.'profile_image'.DS.$fileName;
                $image = $this->Storage->base64ToImg( $data['file'], $file_path );
                $user->cover_image= $fileName;
            }
            if(!empty($data['ProfilePic'])){
                $fileName = $this->Auth->user('username').'_profile_image.'.$data['profile_pic_file_ext'];
                $file_path =  WWW_ROOT.'profile_image'.DS.$fileName;
                $image = $this->Storage->base64ToImg( $data['ProfilePic'], $file_path );
                $user->profile_picture= $fileName;
            }
            $this->Users->save($user);
            $res['success'] = true;
            $res['message'] = 'your profile updated successfully!';
        }
        $this->set([
            'success'=>$res['success'],
            'message'=>$res['message'],
            '_serialize'=>['success','message']
        ]);
    }
    public function getAuthUser(){
        $user=$this->Auth->user();
        if($user && !empty($user['cover_image'])){
            $user['cover_image']=$this->Storage->getUserCoverPath($user['cover_image']);
        }
        if($user && !empty($user['profile_picture'])){
            $user['profile_picture']=$this->Storage->getUserProfileImagePath($user['profile_picture']);
        }
        $this->set([
            'success'=>TRUE,
            'user'=>$user,
            '_serialize'=>['success','user']
        ]);
    }

    public function getUserContents($userId){
        $contents = $this->Users->Contents->find('all',[
            'contain'=>[
                'Users'
            ]
        ])->where([
            'Contents.user_id'=>$userId
        ])->order([
            'Contents.id DESC'
        ])->limit(10);
        $this->set([
            'success'=>TRUE,
            'contents'=>$contents,
            '_serialize'=>['success','contents']
        ]);
    }


    public function getUser($userId){
        $user = $this->Users->get($userId);
        if($user && !empty($user['cover_image'])){
            $user['cover_image']=$this->Storage->getUserCoverPath($user['cover_image']);
        }
        if($user && !empty($user['profile_picture'])){
            $user['profile_picture']=$this->Storage->getUserProfileImagePath($user['profile_picture']);
        }
        $this->set([
            'success'=>TRUE,
            'user'=>$user,
            '_serialize'=>['success','user']
        ]);
    }

    public function isEmailExists(){
        $email=$this->request->data['value'];
        $user=$this->Users->find('all')->where(['email'=>$email])->count();

        //pr($user);
        if($user){
            $this->set([
                'isValid'=>FALSE,
                'value'=>'Email exists!',
                '_serialize'=>['isValid','value']
            ]);
        }else{
            $this->set([
                'isValid'=>TRUE,
                'value'=>'Email OK!',
                '_serialize'=>['isValid','value']
            ]);
        }
    }
    public function isUserExists(){
        $username=$this->request->data['value'];
        $user=$this->Users->find('all')->where(['username'=>$username])->count();

        //pr($user);
        if($user){
            $this->set([
                'isValid'=>FALSE,
                'value'=>'Username exists!',
                '_serialize'=>['isValid','value']
            ]);
        }else{
            $this->set([
                'isValid'=>TRUE,
                'value'=>'Username OK!',
                '_serialize'=>['isValid','value']
            ]);
        }
    }

    public function test(){
        $this->set([
            'success'=>TRUE,
            'message'=>'test was successful',
            '_serialize'=>['success','message']
        ]);
    }
    public function updateName(){
      if ($this->request->is('post')) {
          $data = $this->request->data();
          $res['success'] = FALSE;
          $res['message'] = 'Internal server error.';
          $user = $this->Users->get($this->Auth->user('id'));
          $user->first_name = $data['first_name'];
          $user->last_name = $data['last_name'];
          if($this->Users->save($user)){
            $res['success'] = TRUE;
            $res['message'] = 'Your name updated successully.';
          }
          echo json_encode($res);
          exit();
      }
    }
	public function changePassword(){
        if ($this->request->is('post')) {
            $data = $this->request->data();
            $res['success'] = FALSE;
            $user = $this->Users->get($this->Auth->user('id'))->toArray();
            if ((new DefaultPasswordHasher)->check($data['oldPassword'], $user['password'])) {
                if($data['newPassword'] == $data['confPassword']){
                    $userEntity = $this->Users->get($this->Auth->user('id'));
                    $userEntity->password = $data['newPassword'];
                    if($this->Users->save($userEntity)){
                        $res['success'] = TRUE;
                        $res['message'] = 'Password Changed Successfully.';
                    }
                }else{
					$res['success'] = FALSE;
                    $res['message'] = 'Confirm password is not same as new password. please enter both password again!!';
				}

            }else{
                 $res['success'] = FALSE;
                 $res['message'] = 'Your old password is wrong!';
            }
            echo json_encode($res);
            exit();
        }
    }
    public function index(){
        $this->set('users', $this->Users->find('all'));
    }

    public function signup(){
        if ($this->request->is('post')) {
            $user = $this->Users->newEntity();
            $data = $this->request->data;
            $user = $this->Users->patchEntity($user, $data);
            if ($this->Users->save($user)) {
                $this->set([
                    'success' => TRUE,
                    'message'=> "Signup Successfull",
                    '_serialize'=>['success','message']
                ]);
            }else{
                $this->set([
                    'success' => FALSE,
                    'message'=> "Signup Failed",
                    '_serialize'=>['success','message']
                ]);
            }
        }
    }

    public function login(){
        if ($this->request->is('post')) {
            //$user = $this->Auth->identify();
            $data = $this->request->data;
            if(empty($data['username']) || empty($data['password'])){
                $this->set([
                    'success' => FALSE,
                    'message'=> "Invalid username or password, try again",
                    '_serialize'=>['success','message']
                ]);
            }else{
                /*
                $hasher = new DefaultPasswordHasher();
                $hashedPassword = $hasher->hash($data['password']);
                pr($hashedPassword);exit();
                $user = $this->Users->find('all')->where([
                'username'=>$data['username'],
                'password'=>  $hashedPassword
                ])->first();
                *
                */
                $user=$this->Auth->identify();
                if ($user) {
                    $token=  base64_encode($data['username'].":".$data['password']);
                    $this->set([
                        'success' => TRUE,
                        'message'=> "Welcome",
                        'token'=>$token,
                        'user'=>$user,
                        '_serialize'=>['success','message','token','user']
                    ]);
                }else{
                    $this->set([
                        'success' => FALSE,
                        'message'=> "Invalid username or password, try again",
                        '_serialize'=>['success','message']
                    ]);
                }
            }
        }
    }

}
