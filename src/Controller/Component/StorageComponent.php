<?php
namespace App\Controller\Component;

use Cake\Routing\Router;
use Cake\Controller\Component;

class StorageComponent extends Component
{
    public function saveCoverPic($amount1, $amount2)
    {
        return $amount1 + $amount2;
    }

	public function base64ToImg( $base64_string, $output_file ) {
		$ifp = fopen($output_file, "wb");
	    $data = explode(',', $base64_string);
	    fwrite($ifp, base64_decode($data[1]));
	    fclose($ifp);
	    return $output_file;
	}

	public function getUserCoverPath($imageName){
		return Router::url('/',true).'profile_image/'.$imageName;
	}
}
