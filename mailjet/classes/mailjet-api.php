<?php
 
 /**
 * This is Api Strategy Interface
 * @author		Pavel Tashev  
 * @author		Mailjet
 * @link		http://www.mailjet.com/
 */
 
 # ============================================== Interface ============================================== #
 interface SPIP_Mailjet_Api_Interface 
 {
 	public function createLists($params);
    public function listsAddContacts($params);
    public function listsRemoveContacts($params);
    public function updateLists($params);
    public function getListsContacts($params);
    public function deleteLists($params);
    public function listsStats($params);
 	public function getSenders($params);
 	public function getContactLists($params);
 	public function addContact($params);
	public function removeContact($params);
	public function unsubContact($params);
	public function subContact($params);	
	public function getToken($params);	
	public function validateEmail($email);
 }
 
 
 
 
 
 # ============================================== Strategy ============================================== #
 # Strategy ApiV1
 class SPIP_Mailjet_Api_Strategy_V1 extends SPIP_Mailjet_Api_V1 implements SPIP_Mailjet_Api_Interface
 {
     
    public function createLists($params)
	{
		$response = $this->listsCreate($params);
        return $response;
	}
	
    
    
    
    
    public function listsAddContacts($params)
	{
		$response = $this->listsAddManyContacts($params);
        return $response;
	}
	
    
    
    
    public function listsRemoveContacts($params)
	{
		$response = $this->listsRemoveManyContacts($params);
        return $response;
	}
	
    
    
    
    public function updateLists($params)
	{
		$response = $this->listsUpdate($params);
        return $response;
	}
	
    
        
      
    public function getListsContacts($params)
	{
		// Get the list
		$response = $this->listsContacts($params);

		// Check if the list exists
		if(isset($response->result)) {
			return $response;
		}		
		
		return (object) array('status' => 'ERROR');
	}
	
    
    
    public function listsStats($params)
	{
		// Get the list
		$response = $this->listsStatistics($params);

		// Check if the list exists
		if($response->status == 'OK') {
            return (object) array('status' => 'OK','lists' => $response->statistics);
		}		
		
		return (object) array('status' => 'ERROR');
	}
	
     
    public function deleteLists($params)
	{
        if(!is_numeric($params['listId'])) {
            return (object) array('Status' => 'ERROR');	
        }
        $params = array('method' => 'POST', 'id' => $params['listId']);
        
        // Remove the contact list
        $response = $this->listsDelete($params);

        if($response->status == 'OK') {
            return $response;
		}		
		
		return (object) array('status' => 'ERROR');
	}
    
    
 	/**
     * Validates if given email adres is among the allowed senders for current API account
     * 
     * @param type $params array('email' =>)
     * @return boolean
     */
    public function validateSenderEmail($params)
	{
        if(!isset($params['email']) || !$this->validateEmail($params['email'])) {
            return false;	
        }
		// Get the list
		$response = $this->getSenders($params);
		// Check if the list exists
		 if ($response->Status != 'ERROR') {
            $sendersInfo = $response;
            $domainArr = explode('@', $params['email']);
            if (in_array($params['email'], $sendersInfo['email']) || in_array($domainArr[1], $sendersInfo['domain'])) {
                return true;
            }
		}		
		return false;
	}
 	/**
	 * Get full list of senders
	 * 
	 * @param (array) $param = array('limit', ...) 
	 * @return (object)
	 */
 	public function getSenders($params)
	{

		// Get the list
		$response = $this->userSenderList()->senders;
		
		// Check if the list exists
		if(isset($response))
		{
			$senders = array();
			$senders['domain'] = array();
			$senders['email'] = array();
			
			foreach ($response as $sender)
			{
				if($sender->status == 'active')
				{
					if(substr($sender->email, 0, 2) == '*@') 
						$senders['domain'][] = substr($sender->email, 2, strlen($sender->email)); // This is domain
					else						
						$senders['email'][] = $sender->email; // This is email
				}
			}
			return $senders;
		}		
		
		return (object) array('Status' => 'ERROR');
	}
	
 	/**
	 * Get full list of contact lists
	 * 
	 * @param (array) $param = array('limit', ...) 
	 * @return (object)
	 */
 	public function getContactLists($params)
	{
		// Set input parameters
		$input = array();
		if(isset($params['limit'])) $input['limit'] = $params['limit'];
		
		// Get the list
		$response = $this->listsAll($input);

		// Check if the list exists
		if(isset($response->status) && $response->status == 'OK')
		{
			$lists = array();
			foreach ($response->lists as $list)
			{
				$lists[] = array(
					'id' => $list->id,
					'label' => $list->label,
                    'subscribers' => $list->subscribers,
					'created_at' => $list->created_at,
				);
			}
			return $lists;
		}		
		
		return (object) array('Status' => 'ERROR');
	}
	
	/**
	 * Add a contact to a contact list with ID = ListID
	 * 
	 * @param (array) $param = array('Email', 'ListID', ...) 
	 * @return (object)
	 */
 	public function addContact($params)
	{
		// Check if the input data is OK
		if(!is_numeric($params['ListID']) || !$this->validateEmail($params['Email']))
			return (object) array('Status' => 'ERROR');	
		
		// Add the contact
		$response = $this->listsAddContact(array(
			'method'	=> 'POST',
			'contact'	=> $params['Email'],
			'id'		=> $params['ListID']
		));
				
		// Check if the contact is added 
		if($response)
			return (object) array('Status' => 'OK');
		
		return (object) array('Status' => 'ERROR');
	}
	
	/**
	 * Remove a contact from a contact list with ID = ListID
	 * 
	 * @param (array) $param = array('Email', 'ListID', ...) 
	 * @return (object)
	 */
	public function removeContact($params)
	{
		// Check if the input data is OK
		if(!is_numeric($params['ListID']) || !$this->validateEmail($params['Email']))
			return (object) array('Status' => 'ERROR');	
		
		// Unsubscribe the contact
		$response = $this->listsRemoveContact(array(
			'method'	=> 'POST',
			'contact'	=> $params['Email'],
			'id'		=> $params['ListID']
		));
		
		// Check if the contact is added 
		if($response)
			return (object) array('Status' => 'OK');
		
		return (object) array('Status' => 'OK');
	}
	
	/**
	 * Unsubscribe a contact from a contact list with ID = ListID
	 * 
	 * @param (array) $param = array('Email', 'ListID', ...) 
	 * @return (object)
	*/
	public function unsubContact($params)
	{
		// Check if the input data is OK
		if(!is_numeric($params['listId']) || !$this->validateEmail($params['email']))
			return (object) array('status' => 'ERROR');	
			
		// Unsubscribe the contact
		$response = $this->listsUnsubContact(array(
			'method'	=> 'POST',
			'contact'	=> $params['email'],
			'id'		=> $params['listId']
		));
		
		// Check if the contact is added 
		if($response)
			return (object) array('status' => 'OK');
		
		return (object) array('status' => 'OK');
	}
	
	/**
	 * Subscribe a contact to a contact list with ID = ListID
	 *
	 * @param (array) $param = array('Email', 'ListID', ...) 
	 * @return (object)
	 */
	public function subContact($params)
	{
		// Check if the input data is OK
		if(!is_numeric($params['listId']) || !$this->validateEmail($params['email']))
			return (object) array('status' => 'ERROR');	
		
		// Subscribe the user
		$response = $this->listsAddContact(array(
			'method'	=> 'POST',
			'id'		=> $params['listId'],
			'contact'	=> $params['email'],
			'force'		=> 1,
		));
		
		// Check if the contact is added 
		if($response)
			return (object) array('status' => 'OK');
		
		return (object) array('status' => 'OK');
	}
	
	/**
	 * Get the authentication token for the iframes
	 * 
	 * @param (array) $param = array('APIKey', 'SecretKey', 'MailjetToken', ...) 
	 * @return (object)
	*/
	public function getToken($params)
	{   
		// Check if the input data is OK
		if(strlen(trim($params['apikey'])) == 0) {  
            return (object) array('status' => 'ERROR');	
        }
		
        $response = $this->apiKeyAuthenticate($params);
     
		return (object) $response; 
	}	

	/**
	 * Validate if $email is real email
	 * 
	 * @param (string) $email 
	 * @return (boolean) TRUE|FALSE 
	 */
	public function validateEmail($email) {
		return (preg_match("/(@.*@)|(\.\.)|(@\.)|(\.@)|(^\.)/", $email) || !preg_match("/^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/", $email)) ? FALSE : TRUE;
	}
 }


 # Strategy ApiV3
 class SPIP_Mailjet_Api_Strategy_V3 extends SPIP_Mailjet_Api_V3 implements SPIP_Mailjet_Api_Interface
 {
    
    public function createLists($params)
	{
        
        $params = array(
			'method' => 'JSON',
			'Name' => $params['name'],
            //'Label' => $params['label']
		);

		$newList = $this->contactslist($params);
		
        if ($newList) {
            $listId = $newList->Data[0]->ID;
            return (object) array('status' => 'OK', 'listId' => $listId);
        }
        
		return (object) array('status' => 'ERROR');
	}
	
    
    public function listsAddContacts($params)
	{
        if(!is_numeric($params['id'])) {
            return (object) array('status' => 'ERROR');	
        }
       
		$add_params = array(
			'method'  	=> 'JSON',
			'Action'  	=> 'Add',
			'Force'  	=> true,
			'Addresses' => $params['contactsArray'],
			'ListID'  	=> $params['id']
		);

		$response = $this->manycontacts($add_params);

		if ($response && $response->Count > 0) {
            return (object) array('status' => 'OK');
        }
        
		return (object) array('status' => 'ERROR');
	}
	
    
    public function listsRemoveContacts($params)
	{
        if(!is_numeric($params['id'])) {
            return (object) array('status' => 'ERROR');	
        }
       
		$add_params = array(
			'method'  	=> 'JSON',
			'Action'  	=> 'Remove',
			'Force'  	=> true,
			'Addresses' => $params['contactsArray'],
			'ListID'  	=> $params['id']
		);

		$response = $this->manycontacts($add_params);

		if ($response && $response->Count > 0) {
            return (object) array('status' => 'OK');
        }
        
		return (object) array('status' => 'ERROR');
        
	}
	
    
    public function updateLists($params)
	{
        if(!is_numeric($params['id'])) {
            return (object) array('status' => 'ERROR');	
        }
        $params = array(
			'ID' => $params['id'],
			'method' => 'JSON',
			'Name' => $params['name'],
            //'Label' => $params['label']
		);

		$updatedList = $this->contactslist($params);
		
        if ($updatedList) {
            $listId = $updatedList->Data[0]->ID;
            return (object) array('status' => 'OK', 'listId' => $listId);
        }
        
		return (object) array('status' => 'ERROR');
	}
	
    
    public function getListsContacts($params)
	{
        if(!is_numeric($params['id'])) {
            return (object) array('status' => 'ERROR');	
        }
        
		// Get the list
        $params = array(
            'ContactsList' => $params['id'],
            'method' => 'GET',
            'limit' => '0'
        );
        
		// Get the list
		$response = $this->contact($params);
		// Check if the list exists
		if(isset($response->Data))
		{
			$contacts = array();
			foreach ($response->Data as $contact)
			{ 
				$contacts[] = array(
					'id' => $contact->ID,
					'last_activity' => strtotime($contact->LastActivityAt),
                    'sent' => $contact->DeliveredCount,
                    'email' => $contact->Email,
					'active' => $contact->IsActive,
					'created_at' => strtotime($contact->CreatedAt),
				);
			}
			return (object) array('status' => 'OK', 'total_cnt' => $response->Total, 'result' => $contacts);
		}		
		
		return (object) array('status' => 'ERROR');
	}
	
    
    public function listsStats($params)
	{
        if(!is_numeric($params['id'])) {
            return (object) array('status' => 'ERROR');	
        }
        
		// Get the list
        $params['ID'] = $params['id'];
        
		$response = $this->liststatistics($params);

		// Check if the list exists
		if(isset($response->Data))
		{
			$lists = array();
			foreach ($response->Data as $list)
			{
				$lists[] = array(
					'id' => $list->ID,
					'label' => $list->Name,
                    'name' => $list->Name,
                    'subscribers' => $list->ActiveCount,
					'created_at' => strtotime($list->CreatedAt),
				);
			}
			return (object) array('status' => 'OK','lists' => $lists[0]);
		}		
		
		return (object) array('status' => 'ERROR');
	}
	
    
    public function deleteLists($params)
	{
        if(!is_numeric($params['listId'])) {
            return (object) array('Status' => 'ERROR');	
        }
        
        $params = array(
            'ID'		=> $params['listId'],
            'method' 	=> 'DELETE'
        );

        // Remove the contact list
        $response = $this->contactslist($params);
//			
//        // Check if the unsubscribe is done correctly
//        if(isset($response->Data[0]->ID))
//            return (object) array('Status' => 'OK');

		return (object) array('Status' => 'ERROR');
	}
    

	/**
     * Validates if given email adres is among the allowed senders for current API account
     * 
     * @param type $params array('email' => )
     * @return boolean
     */
    public function validateSenderEmail($params)
	{
        if(!isset($params['email']) || !$this->validateEmail($params['email'])) {
            return false;	
        }
		// Get the list
		$response = $this->getSenders($params);
		// Check if the list exists
		 if ($response->Status != 'ERROR') {
            $sendersInfo = $response;
            $domainArr = explode('@', $params['email']);
            if (in_array($params['email'], $sendersInfo['email']) || in_array($domainArr[1], $sendersInfo['domain'])) {
                return true;
            }
		}		
		return false;
	}
	/**
	 * Get full list of senders
	 * 
	 * @param (array) $param = array('limit', ...) 
	 * @return (object)
	 */
 	public function getSenders($params)
	{

		// Get the list
		$response = $this->sender($params);

		// Check if the list exists
		if(isset($response->Data))
		{
			$senders = array();
			$senders['domain'] = array();
			$senders['email'] = array();
			
			foreach ($response->Data as $sender)
			{
				if($sender->Status == 'Active')
				{
					if(substr($sender->Email, 0, 2) == '*@') 
						$senders['domain'][] = substr($sender->Email, 2, strlen($sender->Email)); // This is domain
					else						
						$senders['email'][] = $sender->Email; // This is email
				}
			}
			return $senders;
		}		
		
		return (object) array('Status' => 'ERROR');
	}
	
 	/**
	 * Get full list of contact lists
	 * 
	 * @param (array) $param = array('limit', ...) 
	 * @return (object)
	 */
 	public function getContactLists($params)
	{
		// Set input parameters
		$input = array(
			//'akid'	=> $this->_akid
		);
		if(isset($params['limit'])) $input['limit'] = $params['limit'];
		
		// Get the list
		$response = $this->liststatistics($input);

		// Check if the list exists
		if(isset($response->Data))
		{
			$lists = array();
			foreach ($response->Data as $list)
			{
				$lists[] = array(
					'id' => $list->ID,
					'name' => $list->Name,
                    'label' => $list->Name,
                    'subscribers' => $list->ActiveCount,
					'created_at' => strtotime($list->CreatedAt),
				);
			}
			return $lists;
		}		
		
		return (object) array('Status' => 'ERROR');
	}
	
	/**
	 * Add a contact to a contact list with ID = ListID
	 * 
	 * @param (array) $param = array('Email', 'ListID', ...) 
	 * @return (object)
	 */
 	public function addContact($params)
	{
		// Check if the input data is OK
		if(!is_numeric($params['listId']) || !$this->validateEmail($params['email']))
			return (object) array('status' => 'ERROR');	
		
		// Add the contact
		$result = $this->manycontacts(array(
			'method'			=> 'POST',
			'Action'			=> 'Add',
			'Addresses'			=> array($params['email']),
			'ListID'			=> $params['listId'],
		));
		
		// Check if any error
		if(isset($result->Data['0']->Errors->Items)) {
			if( strpos($result->Data['0']->Errors->Items[0]->ErrorMessage, 'duplicate') !== FALSE )
				return (object) array('status' => 'DUPLICATE');
			else
				return (object) array('status' => 'ERROR');	
		}		
		
		$this->subContact($params);
		return (object) array('status' => 'OK');
	}
	
	/**
	 * Remove a contact from a contact list with ID = ListID
	 * 
	 * @param (array) $param = array('Email', 'ListID', ...) 
	 * @return (object)
	 */
	public function removeContact($params)
	{
		// Check if the input data is OK
		if(!is_numeric($params['ListID']) || !$this->validateEmail($params['Email']))
			return (object) array('Status' => 'ERROR');	
			
		// Get the contact	
		$result = $this->listrecipient(array(
			//'akid'          => $this->_akid,
			'method'        => 'GET',
			'ListID'		=> $params['ListID'],
			'ContactEmail'  => $params['Email']
        ));
        if($result->Count > 0) 
        {
            foreach($result->Data as $contact) 
			{
				// Remove the contact
				$response = $this->listrecipient(array(
					//'akid'				=> $this->_akid,
					'method'			=> 'delete',
					'ID'				=> $contact->ID
				));
            }
			
			// Check if the unsubscribe is done correctly
			if(isset($response->Data[0]->ID))
				return (object) array('Status' => 'OK');
        }

		return (object) array('Status' => 'ERROR');
	}
	 
	/**
	 * Unsubscribe a contact from a contact list with ID = ListID
	 * 
	 * @param (array) $param = array('Email', 'ListID', ...) 
	 * @return (object)
	*/
	public function unsubContact($params)
	{
		// Check if the input data is OK
		if(!is_numeric($params['listId']) || !$this->validateEmail($params['email']))
			return (object) array('status' => 'ERROR');	
		
		// Get the contact	
		$result = $this->listrecipient(array(
			//'akid'          => $this->_akid,
			'method'        => 'GET',
			'ListID'		=> $params['listId'],
			'ContactEmail'  => $params['email']
        ));
        if($result->Count > 0) 
        {
            foreach($result->Data as $contact) 
            {
                if($contact->IsUnsubscribed !== TRUE)
                {
                      $response = $this->listrecipient(array(
                            //'akid'    			=> $this->_akid,
                            'method'   			=> 'PUT',
                            'ID'       			=> $contact->ID,
                            'IsUnsubscribed' 	=> 'true',
                            'UnsubscribedAt' 	=> date("Y-m-d\TH:i:s\Z", time()),
                      ));
                } 
            }
			
			// Check if the unsubscribe is done correctly
			if(isset($response->Data[0]->ID))
				return (object) array('status' => 'OK');
        }
		
		return (object) array('status' => 'ERROR');
	}
	
	/**
	 * Subscribe a contact to a contact list with ID = ListID
	 *
	 * @param (array) $param = array('Email', 'ListID', ...) 
	 * @return (object)
	 */
	public function subContact($params)
	{
		// Check if the input data is OK
		if(!is_numeric($params['listId']) || !$this->validateEmail($params['email']))
			return (object) array('status' => 'ERROR');	
		
		// Get the contact	
		$result = $this->listrecipient(array(
			//'akid'          => $this->_akid,
			'method'        => 'GET',
			'ListID'		=> $params['listId'],
			'ContactEmail'  => $params['email']
        ));		
		
        if($result->Count > 0) 
        {
            foreach($result->Data as $contact) 
            {
                if($contact->IsUnsubscribed === TRUE)
                {
	                  $response = $this->listrecipient(array(
	                        //'akid'    			=> $this->_akid,
	                        'method'   			=> 'PUT',
	                        'ID'       			=> $contact->ID,
	                        'IsUnsubscribed' 	=> 'false',	                        
	                  ));
                } 
            }
			
			// Check if the subscribe is done correctly
			if(isset($response->Data[0]->ID))
				return (object) array('status' => 'OK');
        }
		
		return (object) array('status' => 'ERROR');
	}
	
	/**
	 * Get the authentication token for the iframes
	 * 
	 * @param (array) $param = array('APIKey', 'SecretKey', 'MailjetToken', ...) 
	 * @return (object)
	*/
	public function getToken($params)
	{
		// Check if the input data is OK
		if(strlen(trim($params['apikey'])) == 0) { 
            return (object) array('status' => 'ERROR');	
        }

		// Get the ID of the Api Key
	 	$api_key_response = $this->apikey(array(
			'method' => 'GET',
			'APIKey' => $params['apikey']
		));

		// Check if the response contains data
		if(!isset($api_key_response->Data[0]->ID)) {
            return (object) array('status' => 'ERROR');
        }

		// Get token
		$response = $this->apitoken(array(
			'AllowedAccess' =>  'campaigns,contacts,stats,preferences',
			'method' 		=> 'POST',
			'APIKeyID' 		=> $api_key_response->Data[0]->ID,
			'TokenType' 	=> 'page',
			'CatchedIp'  	=> $_SERVER['REMOTE_ADDR'],
			'log_once' 		=> TRUE,
			'IsActive'		=> TRUE,
			'SentData'		=> serialize(array('plugin' => 'spip-3.0')),
		));

        // Get and return the token
		if(isset($response->Data) && count($response->Data) > 0) {
            return (object) array('status' => 'OK', 'token' => $response->Data[0]->Token);
        }
		
		return (object) array('status' => 'ERROR');
	}	
	
	/**
	 * Validate if $email is real email
	 * 
	 * @param (string) $email 
	 * @return (boolean) TRUE|FALSE 
	 */
	public function validateEmail($email) {
		return (preg_match("/(@.*@)|(\.\.)|(@\.)|(\.@)|(^\.)/", $email) || !preg_match("/^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/", $email)) ? FALSE : TRUE;
	}
 }
 
 
 
 
 
 # ============================================== Context ============================================== #
 class SPIP_Mailjet_Api
 {
 	private $context;  
	
	public function __construct($mailjet_username, $mailjet_password)
  	{
  		# Check the type of the user and set the corresponding Context/Strategy
  		// Set API V3 context and get the user and check if it's V3   		
		$this->setContext(new SPIP_Mailjet_Api_Strategy_V3($mailjet_username, $mailjet_password));
		$response = $this->context->getSenders(array('limit' => 1));
		if(isset($response->Status) && $response->Status == 'ERROR')
		{
			// Set API V1 context and get the contact lists of this user and check if it's V1
			$this->setContext(new SPIP_Mailjet_Api_Strategy_V1($mailjet_username, $mailjet_password));	
			$response = $this->context->getSenders(array('limit' => 1));
			if(isset($response->Status) && $response->Status == 'ERROR')
			{				
				$this->clearContext();			
                return false;
			} 
		} 	
	}
	
	/**
	 * Set the context of the Api - V1 or V3 
	 *
     * @param SPIP_Mailjet_Api_Interface $context
     * @return void
     */
	private function setContext(SPIP_Mailjet_Api_Interface $context)
    {
        $this->context = $context;
    }
	
	/**
	 * Clear the context
	 *
     * @param void
     * @return void
     */
	private function clearContext()
    {
        $this->context = FALSE;
    }
	

    public function getContext()
    {
        return isset($this->context) ? $this->context : falses;
    }
    public function createLists($params)
	{	
		// Check if we have context, if no, return error
        if($this->context === FALSE)
			return (object) array('status' => 'ERROR');
			
		return $this->context->createLists($params);
	}
    
    
    
    public function listsAddContacts($params)
	{	
		// Check if we have context, if no, return error
        if($this->context === FALSE)
			return (object) array('status' => 'ERROR');
			
		return $this->context->listsAddContacts($params);
	}
    
    
    
    public function listsRemoveContacts($params)
	{	
		// Check if we have context, if no, return error
        if($this->context === FALSE)
			return (object) array('status' => 'ERROR');
			
		return $this->context->listsRemoveContacts($params);
	}
    
    
    
    public function updateLists($params)
	{	
		// Check if we have context, if no, return error
        if($this->context === FALSE)
			return (object) array('status' => 'ERROR');
			
		return $this->context->updateLists($params);
	}
    
    
    
    public function getListsContacts($params)
	{	
		// Check if we have context, if no, return error
        if($this->context === FALSE)
			return (object) array('status' => 'ERROR');
			
		return $this->context->getListsContacts($params);
	}
    
    
    
    public function listsStats($params)
	{	
		// Check if we have context, if no, return error
        if($this->context === FALSE)
			return (object) array('status' => 'ERROR');
			
		return $this->context->listsStats($params);
	}
    
    
    public function deleteLists($params)
	{	
		// Check if we have context, if no, return error
        if($this->context === FALSE)
			return (object) array('Status' => 'ERROR');
			
		return $this->context->deleteLists($params);
	}
    public function validateSenderEmail($params)
	{	
		// Check if we have context, if no, return error
        if($this->context === FALSE) {
            return false;
        }
		return $this->context->validateSenderEmail($params);
	}
    
	/**
	 * Get full list of senders
	 * 
	 * @param (array) $param = array('limit', ...) 
	 * @return (object)
	 */
	public function getSenders($params)
	{	
		// Check if we have context, if no, return error
        if($this->context === FALSE)
			return (object) array('Status' => 'ERROR');
			
		return $this->context->getSenders($params);
	}

	/**
	 * Get full list of contact lists
	 * 
	 * @param (array) $param = array('limit', ...) 
	 * @return (object)
	 */
	public function getContactLists($params)
	{	
		// Check if we have context, if no, return error
        if($this->context === FALSE)
			return (object) array('Status' => 'ERROR');
			
		return $this->context->getContactLists($params);
	}
	
	/**
	 * Add a contact to a contact list with ID = ListID
	 * 
	 * @param (array) $param = array('Email', 'ListID', ...) 
	 * @return (object)
	 */
	 public function addContact($params)
	 {
	 	// Check if we have context, if no, return error
        if($this->context === FALSE)
			return (object) array('Status' => 'ERROR');
		
	 	return $this->context->addContact($params);
	 }
	 
	 /**
	 * Remove a contact from a contact list with ID = ListID
	 * 
	 * @param (array) $param = array('Email', 'ListID', ...) 
	 * @return (object)
	 */
	 public function removeContact($params)
	 {
	 	// Check if we have context, if no, return error
        if($this->context === FALSE)
			return (object) array('Status' => 'ERROR');
		
	 	return $this->context->removeContact($params);
	 }
	 
	 /**
	 * Unsubscribe a contact from a contact list with ID = ListID
	 * 
	 * @param (array) $param = array('Email', 'ListID', ...) 
	 * @return (object)
	*/
	  public function unsubContact($params)
	  {
	  	// Check if we have context, if no, return error
        if($this->context === FALSE)
			return (object) array('status' => 'ERROR');
		
	  	return $this->context->unsubContact($params);
	  }
	  
	 /**
	 * Subscribe a contact to a contact list with ID = ListID
	 *
	 * @param (array) $param = array('Email', 'ListID', ...) 
	 * @return (object)
	 */
	  public function subContact($params)
	  {
	  	// Check if we have context, if no, return error
        if($this->context === FALSE)
			return (object) array('status' => 'ERROR');
		
	  	return $this->context->subContact($params);
	  }
	  
	  /**
		* Get the authentication token for the iframes
		* 
		* @param (array) $param = array('APIKey', 'SecretKey', 'MailjetToken', ...) 
		* @return (object)
	  */
	  public function getToken($params)
	  {
	  	// Check if we have context, if no, return error
        if($this->context === FALSE) {
            return (object) array('status' => 'ERROR');
        }
		
	  	return $this->context->getToken($params);
	  }	
	  
	  /**
	  * Validate if $email is real email
	  * 
	  * @param (string) $email 
	  * @return (boolean) TRUE|FALSE 
	  */
	  public function validateEmail($email) {
	  	// Check if we have context, if no, return error
        if($this->context === FALSE)
			return (object) array('Status' => 'ERROR');
		
		return $this->context->validateEmail($email);
	  }
 }