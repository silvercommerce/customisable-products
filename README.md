SilverCommerce Customisable Products
====================================

Adds a product that can be customised (via dropdowns, radios or free-text)
to your product catalogue.

**NOTE** By default this module does not require you to install a catalogue
frontend, but it does support the two default ones that can be used with 
SilverCommerce (`silvercommerce/catalogue-frontent` and `silvercommerce/catalogue-page`). 


## Author

This module is created and maintained by:
[ilateral](https://ilateralweb.co.uk)

## Dependancies

* SilverStripe Framework 4.x
* SilverCommerce CatalogueAdmin 1.x

## Installation

Install this module via composer:

    composer reqire silvercommerce/customisable-products

Then run: dev/build/?flush=1

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
