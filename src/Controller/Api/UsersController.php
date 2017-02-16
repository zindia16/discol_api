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
			//$fileName = time().'.'.$data['file_ext'];
			$fileName = $this->Auth->user('username').'_cover_image.'.$data['file_ext'];
			$file_path =  WWW_ROOT.'profile_image'.DS.$fileName;

			$image = $this->Storage->base64ToImg( $data['file'], $file_path );
			$user = $this->Users->get($this->Auth->user('id'));
			$user->cover_image= $fileName;
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
		$this->set([
                    'success'=>TRUE,
                    'user'=>$user,
                    '_serialize'=>['success','user']
                ]);
	}

	public function getUser($userId){
		$user = $this->Users->get($userId);
		if($user && !empty($user['cover_image'])){
			$user['cover_image']=$this->Storage->getUserCoverPath($user['cover_image']);
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
