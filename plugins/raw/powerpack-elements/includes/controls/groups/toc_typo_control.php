<?php
namespace PowerpackElements;

use Elementor\Group_Control_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Custom typography control for Table of Contents Widget.
 *
 * @since 1.4.14.x
 */
class Group_Control_Toc extends Group_Control_Base {

	protected static $fields;

	/**
	 * @since 1.4.0
	 * @access public
	 */
	public static function get_type() {
		return 'pp-toc-typography-control';
	}

	/**
	 * @since 1.4.0
	 * @access protected
	 */
	protected function init_fields() {
		$controls = [];

		$controls['heading_level_0'] = [
			'label'			=> _x( 'Level 1', 'TOC Typography Control', 'powerpack' ),
			'type' 			=> Controls_Manager::SLIDER,
			'size_units'	=> ['px','em','rem'],
			'selectors' => [
				'{{WRAPPER}} .pp-toc__list-item.level-0' => 'font-size: {{SIZE}}{{UNIT}}',
			],
			'responsive' => true,
		];

		$controls['heading_level_1'] = [
			'label'			=> _x( 'Level 2', 'TOC Typography Control', 'powerpack' ),
			'type' 			=> Controls_Manager::SLIDER,
			'size_units'	=> ['px','em','rem'],
			'selectors' => [
				'{{WRAPPER}} .pp-toc__list-item.level-1' => 'font-size: {{SIZE}}{{UNIT}}',
			],
			'responsive' => true,
		];

		$controls['heading_level_2'] = [
			'label'			=> _x( 'Level 3', 'TOC Typography Control', 'powerpack' ),
			'type' 			=> Controls_Manager::SLIDER,
			'size_units'	=> ['px','em','rem'],
			'selectors' => [
				'{{WRAPPER}} .pp-toc__list-item.level-2' => 'font-size: {{SIZE}}{{UNIT}}',
			],
			'responsive' => true,
		];

		$controls['heading_level_3'] = [
			'label'			=> _x( 'Level 4', 'TOC Typography Control', 'powerpack' ),
			'type' 			=> Controls_Manager::SLIDER,
			'size_units'	=> ['px','em','rem'],
			'selectors' => [
				'{{WRAPPER}} .pp-toc__list-item.level-3' => 'font-size: {{SIZE}}{{UNIT}}',
			],
			'responsive' => true,
		];

		$controls['heading_level_4'] = [
			'label'			=> _x( 'Level 5', 'TOC Typography Control', 'powerpack' ),
			'type' 			=> Controls_Manager::SLIDER,
			'size_units'	=> ['px','em','rem'],
			'selectors' => [
				'{{WRAPPER}} .pp-toc__list-item.level-4' => 'font-size: {{SIZE}}{{UNIT}}',
			],
			'responsive' => true,
		];

		$controls['heading_level_5'] = [
			'label'			=> _x( 'Level 6', 'TOC Typography Control', 'powerpack' ),
			'type' 			=> Controls_Manager::SLIDER,
			'size_units'	=> ['px','em','rem'],
			'selectors' => [
				'{{WRAPPER}} .pp-toc__list-item.level-5' => 'font-size: {{SIZE}}{{UNIT}}',
			],
			'responsive' => true,
		];

		return $controls;
	}

	/**
	 * @since 1.4.0
	 * @access protected
	 */
	protected function get_default_options() {
		return [
			'popover' => [
				'starter_name' 	=> 'toc-typography-control',
				'starter_title' => _x( 'Heading Typography', 'Heading Typography Control', 'powerpack' ),
			],
		];
	}
}
