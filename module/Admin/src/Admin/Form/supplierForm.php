<?php
/**
 * Created by PhpStorm.
 * User: tristria
 * Date: 10/1/2014
 * Time: 2:16 PM
 */

namespace Admin\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use Velacolib\Utility\Utility;


class supplierForm extends Form
{
    protected $translator;

    public function __construct($name = null)
    {

        parent::__construct('propertyForm');
        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'form-horizontal');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('action', '/admin/supplier/add');
        $this->prepareElements();
    }

    public function prepareElements()
    {
        // add() can take either an Element/Fieldset instance,
        // or a specification, from which the appropriate object
        // will be built.
        $translator = Utility::translate();

        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type' => 'hidden',
            ),
        ));

        $this->add(array(
            'name' => 'company',
            'options' => array(
                'label' => $translator->translate('Company')
            ),
            'attributes' => array(
                'type' => 'text',
                'class' => 'input-xlarge'
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


//        $this->add(array(
//            'name' => 'supply_for',
//            'options' => array(
//                'label' => $translator->translate('Supply for'),
//            ),
//            'attributes' => array(
//                'type' => 'text',
//                'class' => 'input-xlarge'
//            ),
//        ));
        $supArray = Utility::getAllSuplyItemsArray();
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'multiple' => 'multiple',
            ),
            'name' => 'supply_for',
            'options' => array(
                'label' => $translator->translate('Product'),
                'value_options' => $supArray
            ),
        ));


        $this->add(array(
            'name' => 'phone',
            'options' => array(
                'label' => $translator->translate('Phone')
            ),
            'attributes' => array(
                'type' => 'text',
                'class' => 'input-xlarge'
            ),
        ));

        $this->add(array(
            'name' => 'mobile',
            'options' => array(
                'label' => $translator->translate('Mobile')
            ),
            'attributes' => array(
                'type' => 'text',
                'class' => 'input-xlarge'
            ),
        ));


        $this->add(array(
            'name' => 'email',
            'options' => array(
                'label' => $translator->translate('Email')
            ),
            'attributes' => array(
                'type' => 'text',
                'class' => 'input-xlarge'
            ),
        ));

        $this->add(array(
            'name' => 'addr',
            'options' => array(
                'label' => $translator->translate('addr')
            ),
            'attributes' => array(
                'type' => 'textarea',
                'class' => 'input-xlarge'
            ),
        ));


        $this->add(array(
            'name' => 'send',
            'attributes' => array(
                'type' => 'submit',
                'value' => $translator->translate('Save'),
            ),
        ));


        // We could also define the input filter here, or
        // lazy-create it in the getInputFilter() method.
    }
}