<?php
/**
 * @package smart-custom-fields
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * Smart_Custom_Fields_Setting class.
 */
class Smart_Custom_Fields_Setting {

	/**
	 * Post ID of custom field settings page.
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * Title of custom field settings page.
	 *
	 * @var title
	 */
	protected $title;

	/**
	 * Array of the saved group objects.
	 *
	 * @var array
	 */
	protected $groups = array();

	/**
	 * __construct
	 *
	 * @param int    $id    Post ID of custom field settings page.
	 * @param string $title Title of custom field settings page.
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
	 * Getting the post ID.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Getting the post title.
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Getting the group objects.
	 *
	 * @return array
	 */
	public function get_groups() {
		return $this->groups;
	}

	/**
	 * Getting together the fields in each group.
	 *
	 * @return array
	 */
	public function get_fields() {
		$groups = $this->get_groups();
		$fields = array();
		foreach ( $groups as $group ) {
			$fields = array_merge( $fields, $group->get_fields() );
		}
		return $fields;
	}

	/**
	 * Adding group to the tail.
	 * If the argument is not, adding an empty group.
	 *
	 * @param string $group_name Gruop name.
	 * @param bool   $repeat     If repeat, set true.
	 * @param array  $fields     Fields.
	 */
	public function add_group( $group_name = null, $repeat = false, array $fields = array() ) {
		$group      = $this->new_group( $group_name, $repeat, $fields );
		$group_name = $group->get_name();
		if ( $group_name ) {
			$this->groups[ $group_name ] = $group;
		} else {
			$this->groups[] = $group;
		}
	}

	/**
	 * Getting group.
	 *
	 * @param string $group_name Gruop name.
	 * @return Smart_Custom_Fields_Group|false
	 */
	public function get_group( $group_name ) {
		$groups = $this->get_groups();
		if ( isset( $groups[ $group_name ] ) && $groups[ $group_name ]->is_repeatable() ) {
			return $groups[ $group_name ];
		}
	}

	/**
	 * Adding group to the head.
	 * If the argument is not, adding an empty group.
	 *
	 * @param string $group_name Gruop name.
	 * @param bool   $repeat     If repeat, set true.
	 * @param array  $fields     Fields.
	 */
	public function add_group_unshift( $group_name = null, $repeat = false, array $fields = array() ) {
		$group = $this->new_group( $group_name, $repeat, $fields );
		array_unshift( $this->groups, $group );
	}

	/**
	 * Getting generated new group.
	 *
	 * @param string $group_name Gruop name.
	 * @param bool   $repeat     If repeat, set true.
	 * @param array  $fields     Fields.
	 */
	protected function new_group( $group_name, $repeat, $fields ) {
		return new Smart_Custom_Fields_Group( $group_name, $repeat, $fields );
	}
}
