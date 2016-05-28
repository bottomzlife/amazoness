# Amazoness

A WordPress plugin which provides shortcodes for Amazon Associates tags.

## Usage

In posting blog article pages (or any other shortcode-enabled context),
type like this:

````
Blah blah blah [asin]4063827216[/asin] blah blah blah.
````

shows you:

>Blah blah blah  
>  *:*  
>  *Amazon Affiliate banner for ASIN:4063827216*  
>  *:*   
>blah blah blah.

Or you can use Amazon's full URL for products instead of pure ASIN.
Amazoness captures ASIN from the URL like this:

````
[asin]http://www.amazon.co.jp/dp/4063827747[/asin]
[asin]http://www.amazon.co.jp/exec/obidos/ASIN/4063827747[/asin]
````

But this capturing feature does not work properly always.
You'd better use pure ASINs.

## Unique Point

* Merits
    * No need for obtaining Amazon API key, nor knowing about it!
    * Amazoness shows affiliate banners with specifying only ASIN code
    * The product name, information, and an image are automatically crawled and displayed
    * Users can fully customize how it can be seen by CSS and HTML
    * Uses cache for fetching product data, so speedy  
* Demerits
    * Crawls Amazon pages without any permissions ;-( I expect they were tender people...
    * Not suitable for people who don't know what ASIN is

ASIN code is a universal product code in Amazon
which has 9-13 digits.
It seems to be same with EAN/JAN/ISBN.
Visit the Amazon's product page and check if ASIN is there.
Or you can see it in browser's URL bar also.

## Installation

Choose a method which you prefer:

* Search `Amazoness` at [Plugins] -> [Install] in WordPress menu, then click [Install] and [Enable]
* Or, download ZIP archived file and drop into the page at [Plugins] -> [Install] -> [Upload] in WordPress menu, then click [Install] and [Enable]

## Environment

Amazoness works properly in environments like below:

* PHP 5.6 and greater versions   
  Using Closures, Classes, Name Spaces, const arrays
* PHP dom, xml, xmlreader extensions   
  Using DOMDocument, DOMXPath

Tested under:

* CentOS 6.x, Alpine Linux 3.3.x

## Settings

Visit WordPress dashboard, select [Amazoness] in [Settings] menu (at the leftside if you are using PC).
You can configure these entries:

<a name="setting_associate_id">&nbsp;</a>
### Amazon Associate ID 

**!MANDATORY!**  
Set your own Amazon Associate ID.
Visit Amazon Associate page to purchase the ID:
it looks like "netspin-22".
By default, Amazoness uses the plugin developer's ID (mine :)

<a name="setting_image_size">&nbsp;</a>
### Image Size Descriptor 

The descriptor which determines product's image size.
But it has none-sense almost always because image sizes
determined by CSS.
By default, largest(`LZZZZZZZ`). Available options are:

* `THUMBZZZ` :   thumbnail
* `TZZZZZZZ` :   tiny
* `MZZZZZZZ` :   middle
* `LZZZZZZZ` :   large

<a name="setting_css_definition">&nbsp;</a>
### CSS Definition

CSS for Amazoness' output.

Consult <a href="#setting_html_template">Template HTML</a>
to know what kind of id/classes used.

<a name="setting_html_template">&nbsp;</a>
### Template HTML

Template HTML for displaying product's information.
Miss-configuration in this entry may cause 
some security hall so pay much attention.

Some variables and filters are available inside double `%%`:

* Variables:
    * `PRODUCT_URL`  
      URL for Amazon's product page. Contains Amazon Associate ID
    * `PRODUCT_IMAGE_URL`  
      URL for product's image size. Contains an image size descriptor
    * `PRODUCT_TITLE`  
      Product's name
    * `PRODUCT_DESCRIPTION`  
      Product's description
    * `IS_CACHED`
      Show which this HTML fragment uses cache or not
* Filters:
    * `html`  
      Escape danger characters with HTML
    * `url`
      Escape danger characters with URL encoding

Use variables like this:

````
...blah blah blah %%PRODUCT_URL%% blah blah...
````

Filters work like this:

````
...blah blah blah %%PRODUCT_URL|url%% blah blah...
````

## Q&A

* Is ASIN Voodoo magic? What is it?
    * I'm sorry but you should use other WordPress plugins or Web services...
      They are convinience more than Amazoness for your purpose.
      Or consider is as 'URLs for Amazon products pages'
* Amazoness got insane after I made changes in configuration page
    * Click `Reset Configuration` button to reset your configuration
* I have just found some bugs
    * Contact me via GitHub or any other ways 

## Author, Donation & Copyright

This WordPress plugin is written by [bottomzlife](http://netsp.in/).
I know little about PHP and WordPress, so codes might be very strange.

You can use / re-distribute this plugin without any permissions.
But I am very poor. Thank you for kindness if would give me any donation.

* [bottomzlife's Amazon wishlist](http://www.amazon.co.jp/registry/wishlist/35RWBK7ZZQ8PF/ref=cm_sw_r_tw_ws_z.arxbD4ZYFG5)
* [bottomzlife's Amazon associate link](http://www.amazon.co.jp/?_encoding=UTF8&camp=247&creative=1211&linkCode=ur2&tag=netspin-22)

## Contact

All of my mail addresses are dummies for avoiding abuse.

* [GitHub repository](http://)

## History

* v0.9.5
    * 2016/05/29
        * First release for public
