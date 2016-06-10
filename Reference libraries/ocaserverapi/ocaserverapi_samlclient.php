<?php

/**
 * Performs a CURL request to the OCA Server SAML login screen with type=app, which will redirect to the idP.
 * The idP will then redirect back to OCA with the response. When recieving the response, OCA will try to 
 * redirect to oca://login?username={username}&sessionID={sessionid} something. We intercept that session id
 * and pass it back for subsequent use.
 */

class OCAServerApi_Saml {

	public static function assertSession($request) {
		// Check for session Id
		// If no session then return 401
		HTTP::setStatus(401);
		exit;
	}
	
	/**
	 * Performs a CURL request to the OCA Server with type=app, which will start a session and a location header
	 * to the idP. Returns the PHP Session token and the location of the idP.
	 */
	static public function login($request) {
		HTTP::redirect('/login.html?op=saml_login&type=rcviewrp_dev');
		exit;
	}
	
	static public function samlResponse($request) {
	
		self::startSession();
	
		// Set cookie with sessionId
		// Cookie to have an expiry of say 60 seconds
		// Close the opened window
		// The client must take the cookie and use in a header to protect against CSRF, then ideally delete the cookie
		// Maybe we could look up the contact Id at this point, combine with the sessionId, and hash such that we
		// can verify the contact id is for that session later. Then it uses basic auth headers to send it through.
		// This way we guarantee the current contact without having to look it up.
		print '<html><head><script>setCookie();window.close();</script></head><body></body></html>';
	}
	
	
	/**
	 * Performs as many CURL requests to the OCA Server with new session to get past any terms & conditions
	 * requirements.
	 */
	public function startSession() {
		// 
	}
	
	/**
	 * Expects the 
	*/
	


    /*public function login() {
        $response = $this->call(
            'POST',
            '/api/v1/session',
            array('Username' => $this->username, 'Password' => $this->password),
            false
        );
        if ($response['isOk']) {
            $this->sessionId = $response['payload'][0]['SessionID'];
            if ($this->session) {
                // Log out old session
                if ($s = $this->session->getSessionIDForced()) {
                }
                // Store new session
                $this->session->setNewSessionID($this->sessionId);
                $this->session->setSessionTTL(0, 0, 0, 60);
                $this->session->save();
            }
            switch ($response['payload'][0]['StateCode']) {
                case 'WAIT_TERMS_AND_CONDITIONS':
                    $this->ackTerms($response['payload'][0]['TermsAckCode']);
                    break;
                case 'AUTHENTICATED':
                    // store the payload returned by OCA
                    $this->sessionPayload = $response['payload'][0];
                    return true;
            }
        } else {
            throw new Exception('Unable to authenticate: ' . $response['statusCode']);
        }
    }*/
}