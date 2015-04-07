<?php
namespace Admin\Form;

use Velacolib\Utility\Utility;
use Zend\Form\Form;
use Zend\Form\Element;

class categoryForm extends Form{

    public function __construct($name = null){
        $translator = Utility::translate();
        parent::__construct('AdminCategories');
        $this->setAttribute('method','post');
        $this->add(array(
            'name'=> 'name',
            'attributes' =>array(
                'type'=>'Text'
            ),
        ));
        $this->add(array(
            'name'=> 'id',
            'attributes' =>array(
                'type'=>'Hidden'
            ),
        ));
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => $translator->translate('Save'),
                'id' => 'submitbutton',
                'class' => 'btn btn-primary'
            ),
        ));
    }
}
