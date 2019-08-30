<?php
/*
Copyright: Deux Huit Huit 2019
License: MIT, see the LICENCE file
http://deuxhuithuit.mit-license.org/
*/

if(!defined("__IN_SYMPHONY__")) die("<h2>Error</h2><p>You cannot directly access this file</p>");

class extension_entry_deep_duplicator extends Extension {

    protected $errors = array();
    
    public function getSubscribedDelegates(){
        return array(
            array(
                'page'		=> '/backend/',
                'delegate'	=> 'InitaliseAdminPageHead',
                'callback'	=> 'initaliseAdminPageHead'
            )
        );
    }

    public function install() {
        return true;
    }

    public function uninstall() {
        return true;
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
