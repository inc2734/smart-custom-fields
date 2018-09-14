# Smart Custom Fields
Contributors: inc2734, toro_unit, mimosafa, hideokamoto, hisako-isaka, kurudrive, hanamura, justinticktock, designhehe, mayukojpn, hogetan, robssanches, mirucon    
Donate link: http://www.amazon.co.jp/registry/wishlist/39ANKRNSTNW40  
Tags: plugin, custom field, custom, field, meta, meta field, repeat, repeatable  
Requires at least: 3.9  
Tested up to: 4.9.8  
Stable tag: 4.0.2  
License: GPLv2 or later  
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Smart Custom Fields is a simple plugin for managing custom fields.

## Description

Smart Custom Fields is a simple plugin for managing custom fields.

### Features

* Field group loop support.
* Meta data revision support.
* Meta data preview support.

https://www.youtube.com/watch?v=WxPZurn0yvI

### Field Types

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

### How to get meta data?

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

### Create custom options page

`SCF::add_options_page( $page_title, $menu_title, $capability, $menu_slug, $icon_url = '', $position = null );`

### Register custom fields by the code.

https://gist.github.com/inc2734/9f6d65c7473d060d0fd6

### Translators

* Japanese(ja) - [JOTAKI Taisuke](https://profiles.wordpress.org/tai/)

You can translate this plugin into your language by using [GlotPress](https://translate.wordpress.org/projects/wp-plugins/smart-custom-fields).

## Installation

1. Upload `Smart Custom Fields` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. You can setting custom fields in 'Smart Custom Fields' page.

## Changelog

### 4.0.2 =
* Some updates by [@robssanches](https://github.com/robssanches)

### 4.0.0
* Add message field. #64 (by [@robssanches](https://github.com/robssanches))
* Fix boolean field bug with `smart-cf-register-fields` filter hook.
* Refactoring displaying options process of each fields.

See full changelog on [readme.txt](/readme.txt)