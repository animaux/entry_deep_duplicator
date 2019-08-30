<?php
/*
Copyright: Deux Huit Huit 2019
License: MIT, see the LICENCE file
http://deuxhuithuit.mit-license.org/
*/

if(!defined("__IN_SYMPHONY__")) die("<h2>Error</h2><p>You cannot directly access this file</p>");

class extension_entry_deep_duplicator extends Extension {

    const DB_TABLE = 'tbl_sections';

    protected $errors = array();
    
    public function getSubscribedDelegates(){
        return array(
            array(
                'page'		=> '/backend/',
                'delegate'	=> 'InitaliseAdminPageHead',
                'callback'	=> 'initaliseAdminPageHead'
            ),
            array(
                'page' => '/blueprints/sections/',
                'delegate' => 'AddSectionElements',
                'callback' => 'dAddSectionElements'
            ),
        );
    }

    public function install() {
        return Symphony::Database()
            ->alter(self::DB_TABLE)
            ->add([
                'duplicate_prevent_copy' => [
                    'type' => 'enum',
                    'values' => ['yes','no'],
                    'default' => 'no',
                ],
            ])
            ->after('hidden')
            ->execute()
            ->success();
    }

    public function uninstall() {
        return Symphony::Database()
                    ->alter(self::DB_TABLE)
                    ->drop('duplicate_prevent_copy')
                    ->execute()
                    ->success();
    }

    public function dAddSectionElements($context)
    {
        $fieldset = new XMLElement('fieldset', null, array('class' => 'settings'));
        $legend = new XMLElement('legend', __('Entry Deep Duplicator'));
        $label = Widget::Label();
        $label->appendChild(Widget::Checkbox('meta[duplicate_prevent_copy]', $context['meta']['duplicate_prevent_copy'], __('Prevent copy of entries in this section (will only link instead)')));
        $fieldset->appendChild($legend);
        $fieldset->appendChild($label);
        $context['form']->appendChild($fieldset);
    }

    public function initaliseAdminPageHead($context) {
        $page = Administration::instance()->Page;

        if ($page instanceof contentPublish) {
            $callback = Administration::instance()->getPageCallback();

            if($callback['context']['page'] !== 'edit') {
                return;
            }

            $page->addScriptToHead(URL.'/extensions/entry_deep_duplicator/assets/publish.js');
            $page->addStylesheetToHead(URL.'/extensions/entry_deep_duplicator/assets/publish.css');
        }

    }

}
