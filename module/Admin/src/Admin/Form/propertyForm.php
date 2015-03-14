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


class propertyForm extends Form
{
    protected $translator;

    public function __construct($name = null)
    {

        parent::__construct('propertyForm');
        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'form-horizontal');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('action', '/admin/property/add');
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
                'label' => $translator->translate('Quantity')
            ),
            'attributes' => array(
                'type' => 'text',
                'class' => 'input-xlarge'
            ),
        ));

        $this->add(array(
            'name' => 'unit',
            'options' => array(
                'label' => $translator->translate('Unit')
            ),
            'attributes' => array(
                'type' => 'text',
                'class' => 'input-xlarge'
            ),
        ));

        $this->add(array(
            'name' => 'des',
            'options' => array(
                'label' => $translator->translate('Description')
            ),
            'attributes' => array(
                'type' => 'text',
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