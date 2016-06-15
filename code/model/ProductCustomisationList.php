<?php

class ProductCustomisationList extends DataObject
{
    private static $db = array(
        "Title" => "Varchar"
    );

    private static $has_one = array(
        "SiteConfig" => "SiteConfig"
    );

    private static $has_many = array(
        "Customisations" => "ProductCustomisation"
    );
}