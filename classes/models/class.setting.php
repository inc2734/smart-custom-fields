<?php
/**
 * Smart_Custom_Fields_Setting
 * Version    : 1.0.0
 * Author     : inc2734
 * Created    : September 23, 2014
 * Modified   : February 27, 2015
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Setting {

	/**
	 * Post ID of custom field settings page
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * Title of custom field settings page
	 *
	 * @var title
	 */
	protected $title;

	/**
	 * Array of the saved group objects
	 *
	 * @var array
	 */
	protected $groups = array();

	/**
	 * __construct
	 *
	 * @param int $post_id
	 */
	public function __construct( $id, $title ) {
		$this->id    = $id;
		$this->title = $title;
		$post_meta   = get_post_meta(
			$this->get_id(),
			SCF_Config::PREFIX . 'setting',
			true
		);
		if ( is_array( $post_meta ) ) {
			foreach ( $post_meta as $group ) {
				$group = shortcode_atts(
					array(
						'group-name' => '',
						'repeat'     => false,
						'fields'     => array(),
					),
					$group
				);
				$this->add_group(
					$group['group-name'],
					$group['repeat'],
					$group['fields']
				);
			}
		}
	}

	/**
	 * Getting the post ID
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Getting the post title
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Getting the group objects
	 *
	 * @return array
	 */
	public function get_groups() {
		return $this->groups;
	}

	/**
	 * Getting together the fields in each group
	 *
	 * @return array
	 */
	public function get_fields() {
		$groups = $this->get_groups();
		$fields = array();
		foreach ( $groups as $Group ) {
			$fields = array_merge( $fields, $Group->get_fields() );
		}
		return $fields;
	}

	/**
	 * Adding group to the tail
	 * If the argument is not, adding an empty group
	 *
	 * @param string $group_name
	 * @param bool   $repeat
	 * @param array  $_fields
	 */
	public function add_group( $group_name = null, $repeat = false, array $fields = array() ) {
		$Group      = $this->new_group( $group_name, $repeat, $fields );
		$group_name = $Group->get_name();
		if ( $group_name ) {
			$this->groups[ $group_name ] = $Group;
		} else {
			$this->groups[] = $Group;
		}
	}

	/**
	 * Getting group
	 *
	 * @param string $group_name
	 * @return Smart_Custom_Fields_Group|false
	 */
	public function get_group( $group_name ) {
		$groups = $this->get_groups();
		if ( isset( $groups[ $group_name ] ) && $groups[ $group_name ]->is_repeatable() ) {
			return $groups[ $group_name ];
		}
	}

	/**
	 * Adding group to the head
	 * If the argument is not, adding an empty group
	 *
	 * @param string $group_name
	 * @param bool   $repeat
	 * @param array  $_fields
	 */
	public function add_group_unshift( $group_name = null, $repeat = false, array $fields = array() ) {
		$Group = $this->new_group( $group_name, $repeat, $fields );
		array_unshift( $this->groups, $Group );
	}

	/**
	 * Getting generated new group
	 *
	 * @param string $group_name
	 * @param bool   $repeat
	 * @param array  $_fields
	 */
	protected function new_group( $group_name, $repeat, $fields ) {
		return new Smart_Custom_Fields_Group( $group_name, $repeat, $fields );
	}
}
