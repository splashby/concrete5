<?php
defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('attribute/categories/user');

class DashboardUsersAttributesController extends Controller {
	
	public $helpers = array('form');
	
	public function __construct() {
		parent::__construct();
		$otypes = AttributeType::getList();
		$types = array();
		foreach($otypes as $at) {
			$types[$at->getAttributeTypeID()] = $at->getAttributeTypeName();
		}
		$this->set('types', $types);
	}
	
	public function delete($akID, $token = null){
		try {
			$ak = UserAttributeKey::getByID($akID); 
				
			if(!($ak instanceof UserAttributeKey)) {
				throw new Exception(t('Invalid attribute ID.'));
			}
	
			$valt = Loader::helper('validation/token');
			if (!$valt->validate('delete_attribute', $token)) {
				throw new Exception($valt->getErrorMessage());
			}
			
			$ak->delete();
			
			$this->redirect("/dashboard/users/attributes", 'attribute_deleted');
		} catch (Exception $e) {
			$this->set('error', $e);
		}
	}
	
	public function select_type() {
		$atID = $this->request('atID');
		$at = AttributeType::getByID($atID);
		$this->set('type', $at);
		$this->set('category', AttributeKeyCategory::getByHandle('user'));
	}
	
	public function view() {
		$attribs = UserAttributeKey::getList();
		$this->set('attribs', $attribs);
	}
	
	public function add() {
		$this->select_type();
		$type = $this->get('type');
		$cnt = $type->getController();
		$e = $cnt->validateKey();
		if ($e->has()) {
			$this->set('error', $e);
		} else {
			$ak = UserAttributeKey::add($this->post('akHandle'), $this->post('akName'), $this->post('akIsSearchable'), $this->post('atID'), $this->post('uakRequired'), $this->post('uakDisplayedOnRegister'), $this->post('uakPrivate'), $this->post('uakHidden'));
			$this->redirect('/dashboard/users/attributes/', 'attribute_created');
		}
	}

	public function attribute_deleted() {
		$this->set('message', t('User Attribute Deleted.'));
	}
	
	public function attribute_created() {
		$this->set('message', t('User Attribute Created.'));
	}

	public function attribute_updated() {
		$this->set('message', t('User Attribute Updated.'));
	}
	
	public function attribute_type_passthru($atID, $method) {
		$args = func_get_args();
		$type = AttributeType::getByID($atID);
		$cnt = $type->getController();
		
		$method = $args[1];
		
		array_shift($args);
		array_shift($args);
		
		call_user_func_array(array($cnt, 'action_' . $method), $args);
	}
	
	public function edit($akID = 0) {
		if ($this->post('akID')) {
			$akID = $this->post('akID');
		}
		$key = UserAttributeKey::getByID($akID);
		$type = $key->getAttributeType();
		$this->set('key', $key);
		$this->set('type', $type);
		$this->set('category', AttributeKeyCategory::getByHandle('user'));
		
		if ($this->isPost()) {
			$cnt = $type->getController();
			$cnt->setAttributeKey($key);
			$e = $cnt->validateKey();
			if ($e->has()) {
				$this->set('error', $e);
			} else {
				$key->update($this->post('akHandle'), $this->post('akName'), $this->post('akIsSearchable'), $this->post('atID'), $this->post('uakRequired'), $this->post('uakDisplayedOnRegister'), $this->post('uakPrivate'), $this->post('uakHidden'));
				$this->redirect('/dashboard/users/attributes', 'attribute_updated');
			}
		}
	}
	
}