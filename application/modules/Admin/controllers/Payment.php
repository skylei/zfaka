<?php

/*
 * 功能：后台中心－支付设置
 * Author:资料空白
 * Date:20180509
 */

class PaymentController extends AdminBasicController
{
	private $m_payment;
    public function init()
    {
        parent::init();
		$this->m_payment = $this->load('payment');
    }

    public function indexAction()
    {
        if ($this->AdminUser==FALSE AND empty($this->AdminUser)) {
            $this->redirect("/admin/login");
            return FALSE;
        }

		$data = array();
		$this->getView()->assign($data);
    }

	//ajax
	public function ajaxAction()
	{
        if ($this->AdminUser==FALSE AND empty($this->AdminUser)) {
            $data = array('code' => 1000, 'msg' => '请登录');
			Helper::response($data);
        }
		
		$where = array();
		
		$page = $this->get('page');
		$page = is_numeric($page) ? $page : 1;
		
		$limit = $this->get('limit');
		$limit = is_numeric($limit) ? $limit : 10;
		
		$total=$this->m_payment->Where($where)->Total();
		
        if ($total > 0) {
            if ($page > 0 && $page < (ceil($total / $limit) + 1)) {
                $pagenum = ($page - 1) * $limit;
            } else {
                $pagenum = 0;
            }
			
            $limits = "{$pagenum},{$limit}";
			$items=$this->m_payment->Where($where)->Limit($limits)->Order(array('id'=>'DESC'))->Select();
			
            if (empty($items)) {
                $data = array('code'=>0,'count'=>0,'data'=>array(),'msg'=>'无数据');
            } else {
                $data = array('code'=>0,'count'=>$total,'data'=>$items,'msg'=>'有数据');
            }
        } else {
            $data = array('code'=>0,'count'=>0,'data'=>array(),'msg'=>'无数据');
        }
		Helper::response($data);
	}
	
    public function editAction()
    {
        if ($this->AdminUser==FALSE AND empty($this->AdminUser)) {
            $this->redirect("/admin/login");
            return FALSE;
        }
		$id = $this->get('id');
		if($id AND $id>0){
			$data = array();
			$item=$this->m_payment->SelectByID('',$id);
			$data['item'] =$item;
			$this->getView()->assign($data);
		}else{
            $this->redirect("/admin/payment");
            return FALSE;
		}
    }
	public function editajaxAction()
	{
		$method = $this->getPost('method',false);
		$id = $this->getPost('id',false);
		$payment = $this->getPost('payment',false);
		$sign_type = $this->getPost('sign_type',false);
		$app_id = $this->getPost('app_id',false);
		$ali_public_key = $this->getPost('ali_public_key',false);
		$rsa_private_key = $this->getPost('rsa_private_key',false);
		$active = $this->getPost('active',false);
		$csrf_token = $this->getPost('csrf_token', false);
		
		$data = array();
		
        if ($this->AdminUser==FALSE AND empty($this->AdminUser)) {
            $data = array('code' => 1000, 'msg' => '请登录');
			Helper::response($data);
        }
		
		if($method AND $payment AND $sign_type AND $app_id AND $ali_public_key AND $rsa_private_key AND is_numeric($active) AND $csrf_token){
			if ($this->VerifyCsrfToken($csrf_token)) {
				$m=array(
					'payment'=>$payment,
					'sign_type'=>$sign_type,
					'app_id'=>$app_id,
					'ali_public_key'=>$ali_public_key,
					'rsa_private_key'=>$rsa_private_key,
					'active'=>$active,
				);
				if($method == 'edit' AND $id>0){
					$u = $this->m_payment->UpdateByID($m,$id);
					if($u){
						//更新缓存 
						$this->m_payment->getConfig(1);
						$data = array('code' => 1, 'msg' => '更新成功');
					}else{
						$data = array('code' => 1003, 'msg' => '更新失败');
					}
				}else{
					$data = array('code' => 1002, 'msg' => '未知方法');
				}
			} else {
                $data = array('code' => 1001, 'msg' => '页面超时，请刷新页面后重试!');
            }
		}else{
			$data = array('code' => 1000, 'msg' => '丢失参数');
		}
		Helper::response($data);
	}
}