<?php

namespace SilverCommerce\CustomisableProducts;

use SilverStripe\Core\Convert;
use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldButtonRow;
use Symbiote\GridFieldExtensions\GridFieldTitleHeader;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldToolbarHeader;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;
use Symbiote\GridFieldExtensions\GridFieldEditableColumns;
use Symbiote\GridFieldExtensions\GridFieldAddNewInlineButton;

class ProductCustomisation extends DataObject
{
    /**
     * Identifier for a dropdown field
     * 
     * @var string
     */
    const DROPDOWN_FIELD = 'Dropdown';

    /**
     * Identifier for a radio button set field
     * 
     * @var string
     */
    const RADIO_FIELD = 'Radio';

    /**
     * Identifier for a checkbox set field
     * 
     * @var string
     */
    const CHECKBOX_FIELD = 'Checkboxes';

    /**
     * Identifier for a text entry
     * 
     * @var string
     */
    const TEXT_FIELD = 'TextEntry';

    /**
     * Table to create in DB
     * 
     * @var    string
     * @config
     */
    private static $table_name = "ProductCustomisation";

    private static $db = [
        'Title'     => 'Varchar',
        'Required'  => 'Boolean',
        'DisplayAs' => "Enum('Dropdown,Radio,Checkboxes,TextEntry','Dropdown')",
        'MaxLength' => "Int",
        'Sort'      => 'Int'
    ];

    private static $has_one = [
        'Parent'    => CustomisableProduct::class,
        'List'      => ProductCustomisationList::class
    ];

    private static $has_many = array(
        'Options'   => ProductCustomisationOption::class
    );

    private static $summary_fields = array(
        'Title',
        'DisplayAs'
    );

    private static $default_sort = 'Sort ASC';

    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(
            function ($fields) {
                $fields->removeByName('Options');
                $fields->removeByName('ParentID');
                $fields->removeByName('Sort');
                $fields->removeByName('MaxLength');

                if ($this->ID && $this->DisplayAs != self::TEXT_FIELD) {
                    $field_types = ProductCustomisationOption::singleton()->getFieldTypes();

                    // Deal with product features
                    $add_button = new GridFieldAddNewInlineButton('toolbar-header-left');
                    $add_button->setTitle('Add Customisation Option');

                    $options_field = GridField::create(
                        'Options',
                        '',
                        $this->Options(),
                        GridFieldConfig::create()
                            ->addComponent(new GridFieldButtonRow('before'))
                            ->addComponent(new GridFieldToolbarHeader())
                            ->addComponent(new GridFieldTitleHeader())
                            ->addComponent(new GridFieldEditableColumns())
                            ->addComponent(new GridFieldDeleteAction())
                            ->addComponent($add_button)
                            ->addComponent(new GridFieldOrderableRows('Sort'))
                    );

                    $fields->addFieldToTab('Root.Main', $options_field);
                }

                if ($this->ID && $this->DisplayAs == self::TEXT_FIELD) {
                    $fields->addFieldToTab("Root.Main", TextField::create("MaxLength"));
                }

                if (!$this->ID) {
                    $fields->addFieldToTab('Root.Main', LiteralField::create('CreateWarning', '<p>You need to create this before you can add options</p>'));
                }
            }
        );

        return parent::getCMSFields();
    }

    /**
     * Get the default options for this customisation
     * 
     * @return SSList
     */
    public function DefaultOptions()
    {
        $options = $this->Options()->filter('Default', 1);

        $this->extend('updateDefaultOptions', $options);

        return $options;
    }

    /**
     * Method that turns this object into a field type, to be loaded into a form
     *
     * @return FormField
     */
    public function Field()
    {
        if ($this->Title && $this->DisplayAs) {
            $name = "customise_{$this->ID}_" . Convert::raw2url($this->Title);
            $title = ($this->Required) ? $this->Title . ' *' : $this->Title;
            $options = $this->Options()->map('Title', 'ItemSummary');
            $defaults = $this->DefaultOptions();
            $default = ($defaults->exists()) ? $defaults->first()->Title : null;

            switch ($this->DisplayAs) {
            case self::DROPDOWN_FIELD:
                $field = DropdownField::create(
                    $name,
                    $title,
                    $options,
                    $default
                )->setEmptyString(
                    _t(
                        'CustomisableProducts.PleaseSelect',
                        'Please Select'
                    )
                );
                break;
            case self::RADIO_FIELD:
                $field = OptionsetField::create(
                    $name,
                    $title,
                    $options,
                    $default
                );
                break;
            case self::CHECKBOX_FIELD:
                $field = CheckboxSetField::create(
                    $name,
                    $title,
                    $options,
                    $defaults->column('ID')
                );
                break;
            case self::TEXT_FIELD:
                $field = TextField::create($name, $title);
                if ($this->MaxLength) {
                    $field->setMaxLength($this->MaxLength);
                }
                break;
            }

            $this->extend('updateField', $field);

            return $field;
        } else {
            return false;
        }
    }

    public function onBeforeDelete()
    {
        // Delete all options when this opbect is deleted
        foreach ($this->Options() as $option) {
            $option->delete();
        }

        parent::onBeforeDelete();
    }

    public function canView($member = false)
    {
        return $this->Parent()->canView($member);
    }

    public function canCreate($member = null, $context = [])
    {
        return $this->Parent()->canCreate($member);
    }

    public function canEdit($member = null)
    {
        return $this->Parent()->canEdit($member);
    }

    public function canDelete($member = null)
    {
        return $this->Parent()->canDelete($member);
    }
}