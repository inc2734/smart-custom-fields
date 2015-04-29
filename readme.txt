=== Smart Custom Fields ===
Contributors: inc2734, toro_unit
Donate link: http://www.amazon.co.jp/registry/wishlist/39ANKRNSTNW40
Tags: plugin, custom field, custom, field, meta, meta field, repeat, repeatable
Requires at least: 3.9
Tested up to: 4.2.1
Stable tag: 1.4.0
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
* Boolean

= How to get meta data ? =

* SCF::get( 'field-name' )  
This method can get any meta data.
* SCF::get( 'group-name' )  
This method can get meta data of any group.

* SCF::gets()  
This method can get all meta data.

* SCF::get_user_meta( $user_id, 'field-name' )  
This method can get any user meta data.
* SCF::get_user_meta( $user_id, 'group-name' )  
This method can get user meta data of any group.
* SCF::get_user_meta( $user_id )  
This method can get all user meta data.

* SCF::get_term_meta( $term_id, $taxonomy 'field-name' )  
This method can get any term meta data.
* SCF::get_term_meta( $term_id, $taxonomy, 'group-name' )  
This method can get term meta data of any group.
* SCF::get_term_meta( $term_id, $taxonomy )  
This method can get all term meta data.

= Register custom fields by the code. =

https://gist.github.com/inc2734/9f6d65c7473d060d0fd6

= GitHub =

https://github.com/inc2734/smart-custom-fields/

= Translators =

* Japanese(ja) - [JOTAKI Taisuke ](https://profiles.wordpress.org/tai/)

You can send your own language pack to me.

== Installation ==

1. Upload `Smart Custom Fields` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. You can setting custom fields in 'Smart Custom Fields' page.

== Screenshots ==

1. Smart Custom Fields settings page.
2. Post edit page.

== Changelog ==

= 1.4.0 =
* refactoring controllers.
* Add term custom fields.
* Add filter hook smart-cf-is_use_default_when_not_saved
* Changed to the default value is used if the value has not been saved. If you want to revert to the previous behavior, return false in smart-cf-is_use_default_when_not_saved.

= 1.3.2 =
* Add preview size setting in the image field.
* Add display direction setting in the checkbox and radio field.
* Changed the upload field that displayed uploaded to this post first.

= 1.3.1 =
* Fixed a wysiwyg field bug.
* Add boolean field.

= 1.3.0 =
* refactoring.
* Add profile custom fields.
* Add filter hook smart-cf-validate-get-value
* Add method SCF::get_user_meta( $user_id, $name = null )
* Fixed a revision bug.
* Fixed a bug that thumbnail is not displayed correctly in preview.
* Fixed a relation field bug.
* Changed return value of SCF::get with multiple data in loop.
* Changed revision screen format.

= 1.2.2 =
* Fixed a bug that can not get the correct data when the posts use post id filtering.
* Changed that original the_content filter does not apply to wisywig field.
* Add post_id attribute to smart-cf-register-fields.

= 1.2.1 =
* Fixed a bug that post id filtering incorrect.

= 1.2.0 =
* refactoring. A lot of changes in all.
* Renewd the Smart_Custom_Fields_Field_Base.
* Add filter hook smart-cf-register-fields. If You use this hook, you can define custom fields by the code.
* Add action hook smart-cf-before-editor-enqueue-scripts
* Add action hook smart-cf-after-editor-enqueue-scripts
* Add action hook smart-cf-before-settings-enqueue-scripts
* Add action hook smart-cf-after-settings-enqueue-scripts

= 1.1.3 =
* Change method SCF::get_field to SCF::get_value_by_field
* Change method SCF::get_sub_field to SCF::get_values_by_group
* Add method SCF::get_field
* Add method SCF::choices_eol_to_array
* remove method Smart_Custom_Fields_Field_Base::get_choices

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