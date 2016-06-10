<?php

class OCAServerApi_NGCmsUserSource extends NGCmsUsersource {

    public $ocapayload;
    
	public function authenticate($username) {
		if (func_num_args() < 2) return FALSE;
        $password = func_get_arg(1);
        $oca = new OCAServerApi_Client(CFG_OCAAPI_HOST, $username, $password);
        try {
            $oca->login();
            $this->ocapayload = $oca->getSessionPayload();
            $this->load_user($username);
            return TRUE;
        } catch (Exception $e) {}
		return parent::authenticate(FALSE);
	}

	public function load_user($username) {
        global $db;
		parent::load_user($username);
        if ($tmp = $db->get('userrightsgroup', array('DefineSymbol' => $db->quote('WWCMSFORMS_USER')))) {
            $this->userrightsgroups[] = $tmp->id;
        }
		$this->update_attrib_cache();
		return TRUE;
	}
    
    public function logout($usersource = NULL) {
        return parent::logout($usersource);
    }
    
    public function getOCAPayload() {
        return $this->ocapayload;
    }
}
