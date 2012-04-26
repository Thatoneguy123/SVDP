<?php
//Service File for Member Controller
//Authored by: Matthew Tieman
class App_Service_MemberService {
	protected $db;
	function __construct(){
		$this->db = Zend_Db_Table::getDefaultAdapter();
	}
        //Returns basic information of all of user's 'Open' cases
	//Called: Automatically called when building the Memeber landing page
        //@param:$user_id = user_id of the current member
	public function GetUserOpenCases($user_id){
		$select = $this->db->select()
			->from(array('cn' => 'case_need'),
					array('clientID' => 'c.client_id',
					'firstName' => 'c.first_name',
					'lastName' => 'c.last_name',
					'homePhone' => 'c.home_phone',
					'cellPhone' => 'c.cell_phone',
					'workPhone' => 'c.work_phone',
					'caseID' => 'cc.case_id',
					'needs' => new Zend_Db_Expr("GROUP_CONCAT( cn.need SEPARATOR', ')"),
					'totalAmount' => new Zend_Db_Expr('SUM(cn.amount)'), 'openedDate' => 'cc.opened_date',
					'status' => 'cc.status'))
			->joinInner(array('cc' => 'client_case'), 'cn.case_id = cc.case_id')
			->joinLeft(array('h' => 'household'), 'cc.household_id = h.household_id')
			->joinLeft(array('c' => 'client'), 'c.client_id = h.mainclient_id')
			->joinLeft(array('u' => 'user'), 'cc.opened_user_id = u.user_id')
			->group('cc.case_id')
			->where('cc.opened_user_id = ?', $user_id);
		$results = $this->db->fetchAll($select);
		return $this->BuildOpenCases($results);
	}
	
	public function GetClientById($client_id){
		$select = $this->db->select()
			->from(array('c' => 'client'),
				     array('clientID' => 'c.client_id',
					'createdBy' => 'c.created_user_id',
					'firstName' => 'c.first_name',
					'lastName' => 'c.last_name',
					'otherName' => 'c.other_name',
					'marriage' => 'c.marriage_status',
					'birthday' => 'c.birthdate',
					'ssn4' => 'c.ssn4',
					'cellPhone' => 'c.cell_phone',
					'homePhone' => 'c.home_phone',
					'workPhone' => 'c.work_phone',
					'createdDate' => 'c.created_date',
					'memParish' => 'c.member_parish',
					'vetFlag' => 'c.veteran_flag',
					'street' => 'a.street',
					'aptID' => 'a.apt',
					'city' => 'a.city',
					'state' => 'a.state',
					'zipcode' => 'a.zipcode',
					'doNotHelp' => 'd.client_id'))
			->joinInner(array('h' => 'household'), 'h.mainclient_id = c.client_id AND h.current_flag = 1')
			->joinLeft(array('e' => 'employment'), 'e.client_id = c.client_id')
			->joinLeft(array('u'=> 'user'), 'u.user_id = c.created_user_id')
			->joinLeft(array('a' => 'address'), 'h.address_id = a.address_id')
			->joinLeft(array('d' => 'do_not_help'), 'c.client_id = d.client_id')
			->where('c.client_id = ?', $client_id);
			$results = $this->db->fetchRow($select);
			return $this->BuildClientDossier($results);
	}
	
	public fucntion GetClientCases($client_id){
		$select = $this->db->select()
			->from()
	}
	//Builds an array of Case objects populated with basic information about each case
	//Includes a Client object to hold basic client information with the appropriate  case
	private function BuildOpenCases($results){
		$cases = array();
		foreach($results as $row){
			$case = new Application_Model_Case();
			$client = new Application_Model_Client();

			$client
			->setId($row['clientID'])
			->setFirstName($row['firstName'])
			->setLastName($row['lastName'])
			->setCellPhone($row['cellPhone'])
			->setHomePhone($row['homePhone'])
			->setWorkPhone($row['workPhone']);
			
			$case
			->setId($row['caseID'])
			->setOpenedDate($row['openedDate'])
			->setStatus($row['status'])
			->setNeedList($row['needs'])
			->setTotalAmount($row['totalAmount'])
			->setClient($client);

			$cases[] = $case;
		}
		return $cases;
	}
	
	private function BuildClientDossier($results){
		$client = new Application_Model_Client();
		$address = new Application_Model_Addr();
		$address
			->setStreet($results['street'])
			->setApt($results['aptID'])
			->setCity($results['city'])
			->setState($results['state'])
			->setZip($results['zipcode']);
		$client
			->setId($results['clientID'])
			->setUserId($results['createdBy'])
			->setFirstName($results['firstName'])
			->setLastName($results['lastName'])
			->setOtherName($results['otherName'])
			->setMarried($results['marriage'])
			->setBirthDate($results['birthday'])
			->setSsn4($results['ssn4'])
			->setCellPhone($results['cellPhone'])
			->setHomePhone($results['homePhone'])
			->setWorkPhone($results['workPhone'])
			->setCreatedDate($results['createdDate'])
			->setParish($results['memParish'])
			->setVeteran($results['vetFlag'])
			->setDoNotHelp($results['doNotHelp'])
			->setCurrentAddr($address);
		return $client;
	}
	
	private function BuildClientCases($results){
		$cases = array();
		foreach($results as $row){
			
		}
	}
}