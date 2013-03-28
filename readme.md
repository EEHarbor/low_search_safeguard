# Low Search Safeguard

An ExpressionEngine add-on to add basic anti-spam measures to Low Search.

## Compatibility & Requirements

Low Search Safeguard is compatible with **EE 2.4+** and requires **Low Search 2.2+**.

## Installation

- Download and unzip;
- Copy the `low_search_safeguard` folder to your `system/expressionengine/third_party` directory;
- In your Control Panel, go to Add-Ons &rarr; Extensions and click the Install-link in the Low Search Safeguard row;
- All set!

## Settings

### Allow HTML in keywords

Set to No to disallow any keywords that contains HTML. For example: `<b>hello world</b>`

### Allow URLs in keywords

Set to No to disallow any keywords that contain URLs that start with http:// or https://. For example: `[url=http://domain.com]buy now[/url]`

### Honeypot name

The name of the honeypot field in your search form. Leave empty for none. When you enter a name, you can add an input field to your search form with that name. If that input field is *not empty* when the form is submitted, the search is not allowed. For example, for the honeypot name `ackbar`, create an input field like `<input type="hidden" name="ackbar" value=""/>`.

### Blacklisted search terms

A space-separated list of blacklisted search terms. If the search keywords contain any of these terms, the search is not allowed. This is case-insensitive. For example: `viagra`

### Error message

When the search is not allowed, the user will be redirected back to the search form. The variable `{error_message}` will contain the content of this setting. For example: `Input not allowed`

## Links

- [Low Search](http://gotolow.com/addons/low-search)
- [Download Low Search Safeguard](https://github.com/lodewijk/low_search_safeguard/archive/master.zip)