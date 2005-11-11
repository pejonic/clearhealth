<?php

/**
 * Patient Dashboard
 */
class C_PatientDashboard extends Controller {

	var $number_id = 0;
	var $address_id = 0;
	var $insured_relationship_id = 0;
	var $note_id = 0;

	/**
	 * Summary view showing patients forms, reports, encounters, summary
	 * demographics, prescriptions documents
	 *
	 */
	function actionView($patient_id = '') {
		$p =& $this->_loadPatient($patient_id);
		
		// If we don't have a valid Patient, display an error message and stop.
		if (!$p->isPopulated()) {
			$this->assign('NO_PATIENT', true);
			$this->messages->addMessage('There is no currently selected patient or an invalid patient number was supplied.');
			return $this->view->render("view.html");
		}
		
		// todo: determine what this is doing
		$this->set('external_id',$p->get('id'));
		
		// todo: determine why this are using $this->value_id
		$number =& Celini::newORDO('PersonNumber', array($this->number_id, $p->get('id')));
		$address =& Celini::newORDO('PersonAddress', array($this->address_id, $p->get('id')));
		
		// Setup insured relationships block
		$insuredRelationship =& Celini::newORDO('InsuredRelationship', array($this->insured_relationship_id, $p->get('id')));
		$insuredRelationshipGrid =& new cGrid($p->loadDatasource('InsuredRelationshipList'));
		$insuredRelationshipGrid->name = "insuredRelationshipGrid";
		$insuredRelationshipGrid->indexCol = false;
		$insuredRelationshipGrid->setExternalId($p->get('person_id'));

		// Setup encounter block
		$encounterGrid =& new cGrid($p->loadDatasource('EncounterList'));
		$encounterGrid->name = "encounterGrid";
		$encounterGrid->registerTemplate('date_of_treatment','<a href="'.Celini::link('edit', 'encounter').'id={$encounter_id}">{$date_of_treatment}</a>');
		$encounterGrid->pageSize = 5;
		$encounterGrid->setExternalId($p->get('id'));

		// Setup form data block
		$formDataGrid =& new cGrid($p->loadDatasource('FormDataList'));
		$formDataGrid->name = "formDataGrid";
		$formDataGrid->registerTemplate('name','<a href="'.Celini::link('data','Form').'id={$form_data_id}">{$name}</a>');
		$formDataGrid->pageSize = 10;
		$formDataGrid->setExternalId($p->get('id'));
		
		// todo: determine what this is doing and label it correctly
		$menu = Menu::getInstance();
		$tmp = $menu->getMenuData('patient',90);

		$formList = array();
		if (isset($tmp['forms'])) {
			foreach($tmp['forms'] as $form) {
				$formList[$form['form_id']] = $form['title'];
			}	
		}
		
		// Setup report data block
		$report =& Celini::newORDO("Report");
		$reportGrid = new cGrid($report->loadDatasource('ConnectedList', 89));
		$reportGrid->name = "reportGrid";
		$reportGrid->registerTemplate("title",'<a href="'.Celini::link('report').'report_id={$report_id}&template_id={$report_template_id}">{$title}</a>');
		$reportGrid->setExternalId(89);

		// Setup note data block
		$note =& Celini::newORDO('PatientNote');
		$noteGrid =& new cGrid($p->loadDatasource('NoteList'));
		$noteGrid->pageSize = 10;
		$noteGrid->indexCol = false;
		$noteGrid->setExternalId($p->get('id'));
		
		// todo: determine what this is doing and label it appropriately
		$clearhealth_claim = Celini::newORDO("ClearhealthClaim");
		$accountStatus = $clearhealth_claim->accountStatus($p->get('id'));

		$appointmentDS =& $p->loadDatasource('Appointment');
		$appointmentGrid =& new cGrid($appointmentDS);
		$appointmentGrid->pageSize = 10;
		$appointmentGrid->setExternalId($p->get('id'));
		
		$this->assign_by_ref("person",$p);
		$this->assign_by_ref('number',$number);
		$this->assign_by_ref('address',$address);
		$this->assign_by_ref('insuredRelationship',$insuredRelationship);
		$this->assign_by_ref('insuredRelationshipGrid',$insuredRelationshipGrid);
		$this->assign_by_ref('encounterGrid',$encounterGrid);
		$this->assign_by_ref('formDataGrid',$formDataGrid);
		$this->assign_by_ref('reportGrid',$reportGrid);
		$this->assign_by_ref('accountStatus',$accountStatus);
		$this->assign_by_ref('noteGrid',$noteGrid);
		$this->assign_by_ref('depnoteGrid',$depnoteGrid);
		$this->assign_by_ref('note',$note);
		$this->assign_by_ref('appointmentGrid',$appointmentGrid);

		$this->assign('formList',$formList);

		$this->assign('ENCOUNTER_ACTION',Celini::link('add','Encounter') . 'patient_id=' . $p->get('id'));
		$this->assign('ACCOUNT_ACTION',Celini::link('history','account',true, $p->get('id')));
		$this->assign('FORM_FILLOUT_ACTION',Celini::link('fillout','Form'));
		$this->assign('EDIT_ACTION',Celini::link('edit','Patient',true,$p->get('id')));
		$this->assign('NO_PATIENT', false);			
		$this->assign('NOTE_ACTION',Celini::managerLink('note',$p->get('id')));
		$this->assign('DELETE_NUMBER_ACTION',Celini::managerLink('deleteNumber',$p->get('id')));
		$this->assign('DELETE_ADDRESS_ACTION',Celini::managerLink('deleteAddress',$p->get('id')));
		
		return $this->view->render("view.html");
	}
	
	
	/**
	 * 
	 * @access private
	 */
	function &_loadPatient($patient_id) {
		if (is_numeric($patient_id) && $patient_id > 0) {
			if ($this->get('patient_id', 'c_patient') != $patient_id) {
				$this->set("encounter_id", false, 'c_patient');	
			}
			$this->set("patient_id", $patient_id, 'c_patient');	
		} 
		
		$patient_id = $this->_enforcer->int($this->get('patient_id', 'c_patient'));
		$p =& Celini::newORDO('Patient', $patient_id);
		return $p;
	}		

}
?>