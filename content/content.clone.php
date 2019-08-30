<?php
/**
 * Copyrights: Deux Huit Huit 2019
 * LICENCE: MIT http://deuxhuithuit.mit-license.org;
 */

if(!defined("__IN_SYMPHONY__")) die("<h2>Error</h2><p>You cannot directly access this file</p>");

class contentExtensionEntry_deep_duplicatorClone extends AdministrationPage
{

    private $duplications = array();

    public function __construct()
    {
        parent::__construct();
    }

    private function duplicate($id)
    {
        $entry = EntryManager::select()->entry($id)->execute()->next();

        $entry = EntryManager::select()
                    ->section($entry->get('section_id'))
                    ->includeAllFields()
                    ->entry($entry->get('id'))
                    ->execute()
                    ->next();

        $fields = FieldManager::select()
                    ->where(['parent_section' => '26'])
                    ->execute()
                    ->rows();

        $newEntry = EntryManager::create();
        $newEntry->set('section_id', $entry->get('section_id'));
        $newEntry->set('author_id', Symphony::Author()->get('id'));
        $newEntry->set('modification_author_id', Symphony::Author()->get('id'));
        $newEntry->commit();

        $this->duplications[$entry->get('id')] = $newEntry->get('id');

        foreach ($fields as $field) {
            $computedData = $entry->getData($field->get('id'));

            if ($field->get('type') === 'entry_relationship') {

                $relations = array_filter(explode(',', $entry->getData($field->get('id'))['entries']));
                $newRelations = array();

                foreach ($relations as $relation) {
                    if (!empty($relation)) {
                        if (isset($this->duplications[$relation])) {
                            $newRelations[] = $this->duplications[$relation];
                        } else {
                            $dup = $this->duplicate($relation);

                            if (!empty($dup) && !empty($dup->get('id'))) {
                                $newRelations[] = $dup->get('id');
                            }
                        }
                    }
                }

                $computedData['entries'] = implode(',', $newRelations);
            } else if ($field->get('type') === 'upload' || $field->get('type') === 'image_upload') {
                $destination = DOCROOT . $field->get('destination');
                $filename = $entry->getData($field->get('id'))['file'];
                $newfilename = $newEntry->get('id') . '-' . $filename;

                if (!empty($filename)) {
                    copy($destination . '/' . $filename, $destination . '/' . $newfilename);
                    $computedData['file'] = $newfilename;
                }

            } else if ($field->get('type') === 'multilingual_image_upload' || $field->get('type') === 'multilingual_upload') {
                $destination = DOCROOT . $field->get('destination');
                $filename = $entry->getData($field->get('id'))['file'];
                $newfilename = $newEntry->get('id') . '-' . $entry->getData($field->get('id'))['file'];

                if (!empty($filename)) {
                    copy($destination . '/' . $filename, $destination . '/' . $newfilename);
                    $computedData['file'] = $newfilename;
                }

                foreach (FLang::getLangs() as $lang) {
                    $filename = $entry->getData($field->get('id'))['file-' . $lang];
                    $newfilename = $newEntry->get('id') . '-' . $entry->getData($field->get('id'))['file-' . $lang];

                    if (!empty($filename)) {
                        copy($destination . '/' . $filename, $destination . '/' . $newfilename);
                        $computedData['file-' . $lang] = $newfilename;
                    }
                }

            }

            $newEntry->setData($field->get('id'), $computedData);
        }

        $newEntry->commit();

        return $newEntry;
    }

    public function __viewIndex()
    {
        $entryId = (int) General::sanitize($_GET['entry']);

        if (empty($entryId)) {
            Administration::instance()->throwCustomError(
                __('No entry id provided'),
                __('Bad Request'),
                Page::HTTP_STATUS_BAD_REQUEST
            );
        }

        $entry = EntryManager::select()->where(['id' => $entryId])->execute()->next();

        if (empty($entry)) {
            Administration::instance()->throwCustomError(
                __('Entry not found'),
                __('Not found'),
                Page::HTTP_STATUS_NOT_FOUND
            );
        }

        $newEntry = $this->duplicate($entry->get('id'));

        $section = SectionManager::select()->section($newEntry->get('section_id'))->execute()->next();

        redirect(SYMPHONY_URL . '/publish/' . $section->get('handle') . '/edit/' . $newEntry->get('id') . '/');
    }

}
