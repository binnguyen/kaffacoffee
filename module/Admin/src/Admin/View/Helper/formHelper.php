<?php
/**
 * Created by PhpStorm.
 * User: tristria
 * Date: 9/26/2014
 * Time: 10:03 AM
 */

namespace Admin\View\Helper;

use Zend\Session\Container;
use Zend\View\Helper\AbstractHelper;


//example helper
//config in module getViewHelperConfig
class formHelper extends AbstractHelper {
    public function __invoke($form,$title =array(),$extraText = '',$extraNote = array()) {
        $titles = $title['title'];
        $form->prepare();
        $extraNoteHtml = $this->renderExtraNote($extraNote);
        $html1 = $this->view->form()->openTag($form) . PHP_EOL;
        $html1 .= $this->renderFieldsets($form->getFieldsets());
        $html1 .= $this->renderElements($form->getElements());
        $html1 .= $this->view->form()->closeTag($form) . PHP_EOL;
        $html = '<div class="row-fluid">
        <div class="span12 box">
            <div class="box-header blue-background">
                <div class="title">
                    <div class="icon-edit">

</div>
                    '.$titles.'
                     </div>
                     <div></div>
                <div class="actions">
                    <a href="#" class="btn box-remove btn-mini btn-link"><i class="icon-remove"></i>
                    </a>
                    <a href="#" class="btn box-collapse btn-mini btn-link"><i></i>
                    </a>
                </div>
            </div>
            <div class="box-content">
            '.$extraNoteHtml.'
            '.$extraText.'
              '.$html1.'
            </div>
        </div>
    </div>';

        return $html;
    }

    public function renderExtraNote($notes){
        $html = '<div class="row-fluid">';
        $html .= '<div class="span12 ">';
        $html .= '<div class="">';
        foreach($notes as $k => $value){
            $class = "text-success";
            $message = 'yes';
            if(!$value){
                $class = "text-error";
                $message = 'no';
            }
            $html .= '<p class="'.$class.'"> Make '.$k.' writeable ( '.$message.' ) </p>';
        }
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

    public function renderFieldsets($fieldsets) {
        $html = '';
        foreach ($fieldsets as $fieldset) {
            if (count($fieldset->getFieldsets()) > 0) {
                $html = $this->renderFieldsets($fieldset->getFieldsets());
            } else {
                $html = '<fieldset>';
                // You can use fieldset's name for the legend (if that's not inappropriate)
                $html .= '<legend>' . ucfirst($fieldset->getName()) . '</legend>';
                // or it's label (if you had set one)
                // $html .= '<legend>' . ucfirst($fieldset->getLabel()) . '</legend>';
                $html .= $this->renderElements($fieldset->getElements());
                $html .= '</fieldset>';
                // I actually never use the <fieldset> html tag.
                // Feel free to use anything you like, if you do have to
                // make grouping certain elements stand out to the user
            }
        }

        return $html;
    }

    public function renderElements($elements) {
        $html = '';
        foreach ($elements as $element) {
            $html .= $this->renderElement($element);
        }
        return $html;
    }

    public function renderElement($element) {

        // FORM ROW
        $html = '<div class="form-group">';

        // LABEL
        $html .= '<label class="form-label" for="' . $element->getAttribute('id') . '">' . $element->getLabel() . '</label>'; # add translation here

        // ELEMENT
        /*
         - Check if element has error messages
         - If it does, add my error-class to the element's existing one(s),
           to style the element differently on error
        */
        if (count($element->getMessages()) > 0) {
            $classAttribute = ($element->hasAttribute('class') ? $element->getAttribute('class') . ' ' : '');
            $classAttribute .= 'input-error';

            $element->setAttribute('class', $classAttribute);
        }
        $html .= $this->view->formElement($element);

        // ERROR MESSAGES
        $html .= $this->view->FormElementErrors($element, array('class' => 'form-validation-error'));


        $html .= '</div>'; # /.row
        $html .= '<div class="clearfix" style="height: 15px;"></div>';

        return $html . PHP_EOL;
    }
}
//end example helper