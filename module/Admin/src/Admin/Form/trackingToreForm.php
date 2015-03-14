<?php
/**
 * Created by PhpStorm.
 * User: MrHung
 * Date: 11/6/14
 * Time: 9:37 AM
 */

namespace Admin\Form;

use Zend\Code\Scanner\Util;
use Zend\Form\Form;
use Zend\Form\Element;
use Velacolib\Utility\Utility;
use Admin\Model\trackingToreModel;


class trackingToreForm extends Form{

    protected $translator;
    protected   $modelTracking;
    protected $inputFilter;
    public function __construct($name = null){
        parent::__construct('trackingToreForm');
        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'form-horizontal');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('action', '/admin/tracking-tore/add');
        $this->prepareElements();

    }



    public function exchangeArray($data)
    {
        $this->profilename  = (isset($data['profilename']))  ? $data['profilename']     : null;
        $this->fileupload  = (isset($data['fileupload']))  ? $data['fileupload']     : null;
    }

    public function prepareElements(){

        $translator = Utility::translate();

        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type' => 'hidden',

            ),
        ));

        $this->add(array(
            'name' => 'name',
            'options' => array(
                'label' => $translator->translate('Name'),
            ),
            'attributes' => array(
                'type' => 'text',
                'class' => 'input-xlarge'
            ),
        ));

        $this->add(array(
            'name' => 'quantity',
            'options' => array(
                'label' => $translator->translate('Quantity'),
            ),
            'attributes' => array(
                'type' => 'text',
                'class' => 'input-xlarge'
            ),
        ));


        $supArray = Utility::getAllSuplyItemsArray();
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id'=>'supplierItemId'
            ),
            'name' => 'supplierItemId',
            'options' => array(
                'label' => $translator->translate('Supplier item'),
                'value_options' =>   $supArray  ,
                'selected'=>true
            ),
        ));

        $this->add(array(
            'name' => 'supplierItemName',
            'options' => array(
                'label' => $translator->translate('Supplier Item Name'),
            ),
            'attributes' => array(
                'type' => 'text',
                'class' => 'input-xlarge'   ,
                'id'=> 'supplierItemName',
                'readonly'=>'readonly'
            ),
        ));

        $this->add(array(
            'name' => 'note',
            'options' => array(
                'label' => $translator->translate('Note'),
            ),
            'attributes' => array(
                'type' => 'textarea',
                'class' => 'input-xlarge'
            ),
        ));

        $this->add(array(
            'name' => 'time',
            'options' => array(
                'label' => $translator->translate('Time'),
            ),
            'attributes' => array(
                'type' => 'text',
                'class' => 'input-xlarge date-picker'
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' =>$translator->translate('Save') ,
                'id' => 'submitbutton',
                'class' => 'btn btn-primary span3 typeahead'
            ),
        ));
    }



} 