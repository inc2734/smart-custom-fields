=== Smart Custom Fields ===
Contributors: inc2734, toro_unit
Donate link: http://www.amazon.co.jp/registry/wishlist/39ANKRNSTNW40
Tags: plugin, custom field, custom, field, meta, meta field, repeat, repeatable
Requires at least: 3.9
Tested up to: 4.1
Stable tag: 1.1.2
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Smart Custom Fields is a simple plugin that management custom fields.

== Description ==

Smart Custom Fields is a simple plugin that management custom fields.

* This plugin has loop field function.
* Supported metadata revision.
* Supported metadata preview.

https://www.youtube.com/watch?v=WxPZurn0yvI

= Field Types =

* Text
* Textarea
* Radio
* Select
* Checkbox
* Wysiwyg editor
* Image
* File
* Relation
* Color picker
* Date picker

= How to get meta data ? =

* SCF::get( 'field-name' )  
This method can get any meta data.

* SCF::get( 'group-name' )  
This method can get meta data of any group.

* SCF::gets()  
This method can get all meta data.

= GitHub =

https://github.com/inc2734/smart-custom-fields/

== Installation ==

1. Upload `Smart Custom Fields` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. You can setting custom fields in 'Smart Custom Fields' page.

== Screenshots ==

1. Smart Custom Fields settings page.
2. Post edit page.

== Changelog ==

= 1.1.2 =
* Add action hook smart-cf-fields-loaded

= 1.1.1 =
* UX Improvement of settings page.

= 1.1.0 =
* Add date picker field.

= 1.0.3 =
* Fixed a bug that can't be get the correct data when specify a $post_id attribute to SCF::get(). For example SCF::get( 'key', $post_id )

= 1.0.2 =
* Add color picker field.
* Add smart-cf-before-save-post action hook.
* Add smart-cf-after-save-post action hook.
* Add smart-cf-validate-save-post filter hook.

= 1.0.1 =
* Add display condition by post id.
* Fixed bug that is not displayed wysiwyg editor when there are not content editor.
* Textarea does not filter the_content filter in SCF::get() and SCF::gets().

= 1.0.0 =
* Initial release.