=== Smart Custom Fields ===
Contributors: inc2734, toro_unit, mimosafa, hideokamoto, hisako-isaka, kurudrive, hanamura, justinticktock, designhehe, mayukojpn, hogetan, robssanches, mirucon, sysbird
Donate link: http://www.amazon.co.jp/registry/wishlist/39ANKRNSTNW40
Tags: plugin, custom field, custom, field, meta, meta field, repeat, repeatable
Requires at least: 3.9
Tested up to: 5.5.0
Stable tag: 4.1.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Smart Custom Fields is a simple plugin for managing custom fields.

== Description ==

Smart Custom Fields is a simple plugin for managing custom fields.

= Features =

* Field group loop support.
* Meta data revision support.
* Meta data preview support.

https://www.youtube.com/watch?v=WxPZurn0yvI

= Field Types =

* Text
* Textarea
* Radio
* Select
* Checkbox
* WYSIWYG editor
* Image
* File
* Related Posts
* Related Terms
* Color picker
* Date picker
* Datetime picker
* Boolean
* Message

= How to get meta data ? =

**Post meta data**

This method can get any meta data.

`SCF::get( 'field-name' )`

This method can get meta data of any group.

`SCF::get( 'group-name' )`

This method can get all meta data.

`SCF::gets()`

**User meta data**

This method can get any user meta data.

`SCF::get_user_meta( $user_id, 'field-name' )`

This method can get user meta data of any group.

`SCF::get_user_meta( $user_id, 'group-name' )`

This method can get all user meta data.

`SCF::get_user_meta( $user_id )`

**Term meta data**

This method can get any term meta data.

`SCF::get_term_meta( $term_id, $taxonomy 'field-name' )`

This method can get term meta data of any group.

`SCF::get_term_meta( $term_id, $taxonomy, 'group-name' )`

This method can get all term meta data.

`SCF::get_term_meta( $term_id, $taxonomy )`

**Custom options page meta data**

This method can get any custom options page meta data.

`SCF::get_option_meta( $menu_slug, 'field-name' )`

This method can get custom options page meta data of any group.

`SCF::get_option_meta( $menu_slug, 'group-name' )`

This method can get all custom options page meta data.

`SCF::get_option_meta( $menu_slug )`

= Create custom options page =

`SCF::add_options_page( $page_title, $menu_title, $capability, $menu_slug, $icon_url = '', $position = null );`

= Register custom fields by the code. =

https://gist.github.com/inc2734/9f6d65c7473d060d0fd6

= GitHub =

https://github.com/inc2734/smart-custom-fields/

= Translators =

* Japanese(ja) - [JOTAKI Taisuke](https://profiles.wordpress.org/tai/)

You can translate this plugin into your language by using [GlotPress](https://translate.wordpress.org/projects/wp-plugins/smart-custom-fields).

== Installation ==

1. Upload `Smart Custom Fields` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. You can setting custom fields in 'Smart Custom Fields' page.

== Screenshots ==

1. Smart Custom Fields settings page.
2. Post edit page.

== Changelog ==

= 4.1.5 =
* Fix ajax bug.

= 4.1.4 =
* WordPress 5.5 support
* Changed so that the reusable block post type is not displayed in the conditional judgment.

= 4.1.3 =
* Activate datetimepicker in repeat group #80

= 4.1.2 =
* Fix PHP syntax error.

= 4.1.0 =
* feat: Implement new field datetime picker
* feat: Add filters for related posts fields with name and post types

= 4.0.2 =
* Some updates by [@robssanches](https://github.com/robssanches)

= 4.0.0 =
* Add message field. #64 (by [@robssanches](https://github.com/robssanches))
* Fix boolean field bug with `smart-cf-register-fields` filter hook.
* Refactoring displaying options process of each fields.

= 3.1.7 =
* Fixing issues and adding support for the Brazilian Portuguese language #63 (by [@robssanches](https://github.com/robssanches))
* Spelling fixes in Readme #62 (by [@garrett-eclipse](https://github.com/garrett-eclipse))

= 3.1.6 =
* Set any on related post status. #60 (by [@mayukojpn](https://github.com/mayukojpn))
* Changed that file names can be known when uploading files. #58 (by [@shodoi](https://github.com/shodoi))

= 3.1.5 =
* Fixed a bug that disappeared layout when introducing description in relation field. #56 (by [@mayukojpn](https://github.com/mayukojpn))
* Update item delete button style in relation field.
* Fix bug when using smart-cf-register-fields hook.

= 3.1.4 =
* Remove no used codes.
* Fixed a bug that name disappears when opening / closing a field. #51 (by [@yousan](https://github.com/yousan))

= 3.1.3 =
* Fix Selectable number bug

= 3.1.2 =
* Update readme.txt

= 3.1.1 =
* Fixed a bug of limit attribute at relation post types and taxonomies field.

= 3.1.0 =
* Added limit attribute at relation post types and taxonomies field.

= 3.0.1 =
* Fixed a bug that icon and display position of created option page are not reflected #47 (by [@designhehe](https://github.com/designhehe))

= 3.0.0 =
* Support multiple user roles.
* Update filter hook smart-cf-register-fields

= 2.3.0 =
* Support displayed thumbnail when value of file and image field is file url.

= 2.2.3 =
* Fix get_post_metadata hooked only preview #43 (by [@wireframeslayout](https://github.com/wireframeslayout))

= 2.2.2 =
* Fix #37 #38

= 2.2.1 =
* Fix bug boolean field in repeatable group #39
* Fix bug datepicker and colorpicker in repeatable group #41

= 2.2.0 =
* Refactoring tests.
* Changed behavior of the default value of new field of the already saved object. Using the default value.

= 2.1.1 =
* Fix revision lines duplication (by [@hanamura](https://github.com/hanamura))
* Fixed a bug that relation fields don't work on the options page.

= 2.1.0 =
* Support separated key and value in select, checkbox, radio.
* Added switching editor mode tab in wysiwyg field.
* Added instruction of field option.

= 2.0.0 =
* Refactoring
* Added meta data of custom options page.

= 1.7.0 =
* Added taxonomy relation field.
* Added textarea rows setting.
* Fixed a bug that tinymce js error when disabled rich editing.

= 1.6.7 =
* Removed console.log in a js file.

= 1.6.6 =
* Fixed a bug that warning is out when the array isn't returned in the smart-cf-register-fields.

= 1.6.5 =
* Fixed a bug that multi value in the loop is broken.
* In setting screen, if the field is closed, display the field name.

= 1.6.4 =
* Fixed a bug that wysiwyg fields became tinymce default format when content editor mode is text.
* Change the comment in English.

= 1.6.3 =
* Fixed a bug that metadata that isn't defined by Smart Custom Fields can't get in preview.

= 1.6.2 =
* Fixed a bug that sometimes can't get data when there are multiple Smart Custom Fields settings.

= 1.6.1 =
* Fixed a bug that custom field settings vanished when saved.

= 1.6.0 =
* Added search feature in the relation field.
* Changed when the object isn't saved, default value is active.
* Remove filter hook smart-cf-is_use_default_when_not_saved.
* Fixed a bug that isn't displayed meta data in preview when using custom fields settings with post id.

= 1.5.3 =
* Fixed a wysiwyg field bug.

= 1.5.2 =
* Fixed a wysiwyg field bug.

= 1.5.1 =
* Fixed a relation field bug.

= 1.5.0 =
* Update wysiwyg field.

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
* Changed that original the_content filter does not apply to wysiwyg field.
* Add post_id attribute to smart-cf-register-fields.

= 1.2.1 =
* Fixed a bug that post id filtering incorrect.

= 1.2.0 =
* refactoring. A lot of changes in all.
* Renewed the Smart_Custom_Fields_Field_Base.
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
