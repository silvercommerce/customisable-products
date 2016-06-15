Silverstripe Commerce Customisable Product
==========================================

Extension to silverstripe commerce module that allows you to add
customisable product's to your catalogue.

Customisable products have customisation options such as dropdowns,
textboxes and radiobuttons. 


## Author

This module is created and maintained by
[ilateral](http://www.i-lateral.com)

Contact: morven@i-lateral.com

## Dependancies

* SilverStripe Framework 3.1.x
* Silverstripe Commerce

## Installation

Install this module either by downloading and adding to:

[silverstripe-root]/commerce-customisableproduct

Then run: dev/build/?flush=all

Or alternativly add to your project's composer.json

## Usage

Create a new product and from the list of Product types, select
CustomisableProsuct. Now you will get a "Customisations" tab under a
product.

## Global Customisations

This module allows you to define global customisation lists that can be
assigned to multiple products.

In order to do this, you need to log into the admin and click the "Settings"
tab then click "Customisable Products".

Next add a list and then you can add customisations as normal.

Finally find the products that you want to add customisations to and click
the "Customisations" tab. Now you can select your list from the dropdown.
