<?php
class MailChimpHelper {
	
	var $mc=null;
	var $listid="";
	
	function MailChimpHelper($apikey,$listid="") {
		require_once dirname(__FILE__).'/mailchimp/Mailchimp.php';
		$this->apikey=$apikey;
		if ($listid) $this->listid = $listid;
		$this->mc = new Mailchimp($apikey);
	}
	
	function updateUser($email,$info=NULL,$send_welcome,$email_type="html",$list="") {
		if (!$list) $list = $this->listid;
		$mcl = new Mailchimp_Lists($this->mc);
		$result=$mcl->UpdateMember($list,$email,$info,$email_type,false);
		if ($result['error']) { $this->error=$result['error']; return false; }
		return true;
	}
	
	function subscribeUser($email,$info=NULL,$double_optin=false,$email_type="html",$list="") {
		if (!$list) $list = $this->listid;
		$mcl = new Mailchimp_Lists($this->mc);
		$result = $mcl->subscribe($list,$email,$info,$email_type,$double_optin,true,false,false);
		if ($result['error']) { $this->error=$result['error']; return false; }
		return true;
		
	}
	
	function unsubscribeUser($email,$list="") {
		if (!$list) $list = $this->listid;
		$mcl = new Mailchimp_Lists($this->mc);
		$result = $mcl->unsubscribe($list,$email,false,false,false);
		if (!$result['error']) return true;
		else {
			$this->error = $result;
			return false;
		}
	}
	
	function subStatus($email,$list="") {
		if (!$list) $list = $this->listid;
		$mcl = new Mailchimp_Lists($this->mc);
		$result = $mcl->memberInfo($list,array(array("email"=>$email)));
		if ($result['error_count'] == 0) {
			if ($result['data'][0]['status'] == "subscribed") return true;
			else return false;
		}
	    else return false;
	}
	
	function getLists($list="") {
		$mcl = new Mailchimp_Lists($this->mc);
		$filters = array();
		if ($list) {
			$filters["list_id"]=$list;
		}
		$result = $mcl->getList($filters,0,100);
		
		if (!$result['error']) return $result['data'];
		else {
			$this->error = $result;
			return false;
		}
	}
	
	function getListInterestGroupings($list="") {
		if (!$list) $list = $this->listid;
		$mcl = new Mailchimp_Lists($this->mc);
		$result=$mcl->interestGroupings($list);
		if (!$result['error']) return (object) $result;
		else {
			$this->error = $result;
			return false;
		}
	}
	
	function getListMergeVars($list="") {
		if (!$list) $list = $this->listid;
		$mcl = new Mailchimp_Lists($this->mc);
		$result=$mcl->mergeVars(array($list));
		if (!$result['error']) {
			$mv = $result['data'][0]['merge_vars'];
			return (object) $mv;
		}
		else {
			$this->error = $result;
			return false;
		}
	}
	
	function updateMergeVar($list="",$tag,$options) {
		if (!$list) $list = $this->listid;
		$mcl = new Mailchimp_Lists($this->mc);
		$result=$mcl->mergeVarUpdate($list,$tag,$options);
		if (!$result['error']) {
			return true;
		}
		else {
			$this->error = $result;
			return false;
		}
	}	
	
	function listBatchSubscribe($batch,$list="") {
		if (!$list) $list = $this->listid;
		$mcl = new Mailchimp_Lists($this->mc);
		$result = $mcl->batchSubscribe($list,$batch,false,true,false);
		if (!$result['error']) return (object) $result;
		else {
			$this->error = $result;
			return false;
		}
	}
	
	function getListWebhooks($list="") {
		if (!$list) $list = $this->listid;
		$mcl = new Mailchimp_Lists($this->mc);
		$result = $mcl->webhooks($list);
		if (!$result['error']) return (object) $result;
		else {
			$this->error = $result;
			return false;
		}
	}
	
	function addListWebhook($list="",$url,$actions,$sources) {
		if (!$list) $list = $this->listid;
		$mcl = new Mailchimp_Lists($this->mc);
		$result = $mcl->webhookAdd($list,$url,$actions,$sources);
		if (!$result['error']) return true;
		else {
			$this->error = $result;
			return false;
		}
	}
	
	function getAccountInfo() {
		$mch = new Mailchimp_Helper($this->mc);
		$result = $mch->accountDetails(array("modules", "orders", "rewards-credits", "rewards-inspections", "rewards-referrals", "rewards-applied", "integrations"));
		if (!$result['error']) return $result;
		else {
			$this->error = $result;
			return false;
		}
	}
	
	public function getError() { return $this->error; }
	
}