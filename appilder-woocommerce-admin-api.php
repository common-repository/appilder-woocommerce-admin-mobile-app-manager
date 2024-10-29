<?php

/**
 * Created by PhpStorm.
 * User: vanee
 * Date: 9/7/2015
 * Time: 3:41 PM
 */
class Appilder_woocommerce_admin_api extends WC_API_Resource{
	var $base = '/appilder_admin_app';
	public function register_routes($routes){
		$routes[ $this->base ] = array(
			array( array( $this, 'base' ),     WC_API_Server::READABLE ),
		);
		$routes[ $this->base.'/register_push' ] = array(
			array( array( $this, 'register_push' ),     WC_API_Server::METHOD_POST ),
		);
		$routes[ $this->base.'/delete_push' ] = array(
			array( array( $this, 'delete_push' ),     WC_API_Server::METHOD_POST ),
		);
		return $routes;
	}

	public function base(){
		return array(
			"version" => 1.0,
			"status" => 1
		);
	}

	public function register_push($gcm_id){
		$users = get_option('appilder_woo_admin_gcm_users',array());
		$users[$gcm_id] = get_current_user_id();
		update_option('appilder_woo_admin_gcm_users',$users);
		return array(
			"status" => 1,
		);
	}

	public function delete_push($gcm_id){
		$users = get_option('appilder_woo_admin_gcm_users',array());
		unset($users[$gcm_id]);
		update_option('appilder_woo_admin_gcm_users',$users);
		return array(
			"status" => 1,
		);
	}

}