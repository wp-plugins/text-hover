<?php

class Text_Hover_Test extends WP_UnitTestCase {

	function setUp() {
		parent::setUp();
		$this->set_option();
	}

	function tearDown() {
		parent::tearDown();

		remove_filter( 'c2c_text_hover',                array( $this, 'add_text_to_hover' ) );
		remove_filter( 'c2c_text_hover_once',           '__return_true' );
		remove_filter( 'c2c_text_hover_case_sensitive', '__return_false' );
		remove_filter( 'c2c_text_hover_comments',       '__return_true' );
		remove_filter( 'c2c_text_hover_filters',        array( $this, 'add_custom_filter' ) );
	}


	/**
	 *
	 * DATA PROVIDERS
	 *
	 */


	public static function get_default_filters() {
		return array(
			array( 'the_content' ),
			array( 'get_the_excerpt' ),
			array( 'widget_text' ),
		);
	}


	/**
	 *
	 * HELPER FUNCTIONS
	 *
	 */

	function text_hovers( $term = '' ) {
		$text_to_hover = array(
			'WP' => 'WordPress',
			"coffee2code" => 'Plugin developer',
			'Matt Mullenweg' => 'Co-Founder of WordPress',
			'blank' => '',
		);

		if ( ! empty( $term ) ) {
			$text_to_hover = isset( $text_to_hover[ $term ] ) ? $text_to_hover[ $term ] : '';
		}

		return $text_to_hover;
	}

	function set_option( $settings = array() ) {
		$defaults = array(
			'text_to_hover' => $this->text_hovers(),
			'case_sensitive' => true,
		);
		$settings = wp_parse_args( $settings, $defaults );
		c2c_TextHover::get_instance()->update_option( $settings, true );
	}

	function text_hover( $text ) {
		return c2c_TextHover::get_instance()->text_hover( $text );
	}

	function expected_text( $term ) {
		$hover_text = $this->text_hovers( $term );
		if ( empty( $hover_text ) ) {
			$hover_text = $this->text_hovers( strtolower( $term ) );
		}
		return "<acronym title='$hover_text'>$term</acronym>";
	}

	function add_text_to_hover( $text_to_hover ) {
		$text_to_hover = (array) $text_to_hover;
		$text_to_hover['bbPress'] = 'Forum Software';
		return $text_to_hover;
	}

	function add_custom_filter( $filters ) {
		$filters[] = 'custom_filter';
		return $filters;
	}


	/**
	 *
	 * TESTS
	 *
	 */


	function test_hovers_text() {
		$expected = $this->expected_text( 'coffee2code' );

		$this->assertEquals( $expected,                     $this->text_hover( 'coffee2code' ) );
		$this->assertEquals( "ends with $expected",         $this->text_hover( 'ends with coffee2code' ) );
		$this->assertEquals( "ends with period $expected.", $this->text_hover( 'ends with period coffee2code.' ) );
		$this->assertEquals( "$expected starts",            $this->text_hover( 'coffee2code starts' ) );

		$this->assertEquals( $this->expected_text( 'Matt Mullenweg' ), $this->text_hover( 'Matt Mullenweg' ) );
	}

	function test_hovers_single_term_multiple_times() {
		$expected = $this->expected_text( 'coffee2code' );

		$this->assertEquals( "$expected  $expected  $expected", $this->text_hover( 'coffee2code  coffee2code  coffee2code' ) );
	}

	function test_does_not_hover_substrings() {
		$this->assertEquals( 'xcoffee2code',  $this->text_hover( 'xcoffee2code' ) );
		$this->assertEquals( 'ycoffee2codey', $this->text_hover( 'ycoffee2codey' ) );
		$this->assertEquals( 'coffee2codez',  $this->text_hover( 'coffee2codez' ) );
	}

	function test_empty_hover_does_nothing() {
		$this->assertEquals( 'blank', $this->text_hover( 'blank' ) );
	}

	function test_hovers_with_case_sensitivity_by_default() {
		$expected = $this->expected_text( 'coffee2code' );

		$this->assertEquals( $expected,     $this->text_hover( 'coffee2code' ) );
		$this->assertEquals( 'Coffee2code', $this->text_hover( 'Coffee2code' ) );
		$this->assertEquals( 'COFFEE2CODE', $this->text_hover( 'COFFEE2CODE' ) );
	}

	function test_hovers_once_via_setting() {
		$expected = $this->expected_text( 'coffee2code' );
		$this->test_hovers_single_term_multiple_times();
		$this->set_option( array( 'replace_once' => true ) );

		$this->assertEquals( "$expected coffee2code coffee2code", $this->text_hover( 'coffee2code coffee2code coffee2code' ) );
	}

	function test_hovers_once_via_filter() {
		$expected = $this->expected_text( 'coffee2code' );
		$this->test_hovers_single_term_multiple_times();
		add_filter( 'c2c_text_hover_once', '__return_true' );

		$this->assertEquals( "$expected coffee2code coffee2code", $this->text_hover( 'coffee2code coffee2code coffee2code' ) );
	}

	function test_hovers_with_case_insensitivity_via_setting() {
		$this->test_hovers_with_case_sensitivity_by_default();
		$this->set_option( array( 'case_sensitive' => false ) );

		$this->assertEquals( $this->expected_text( 'coffee2code' ), $this->text_hover( 'coffee2code' ) );
		$this->assertEquals( $this->expected_text( 'Coffee2code' ), $this->text_hover( 'Coffee2code' ) );
		$this->assertEquals( $this->expected_text( 'COFFEE2CODE' ), $this->text_hover( 'COFFEE2CODE' ) );
	}

	function test_hoves_with_case_insensitivity_via_filter() {
		$this->test_hovers_with_case_sensitivity_by_default();
		add_filter( 'c2c_text_hover_case_sensitive', '__return_false' );

		$this->assertEquals( $this->expected_text( 'coffee2code' ), $this->text_hover( 'coffee2code' ) );
		$this->assertEquals( $this->expected_text( 'Coffee2code' ), $this->text_hover( 'Coffee2code' ) );
		$this->assertEquals( $this->expected_text( 'COFFEE2CODE' ), $this->text_hover( 'COFFEE2CODE' ) );
	}

	function test_hovers_term_added_via_filter() {
		$this->assertEquals( 'bbPress', $this->text_hover( 'bbPress' ) );
		$expected = "<acronym title='Forum Software'>bbPress</acronym>";
		add_filter( 'c2c_text_hover', array( $this, 'add_text_to_hover' ) );

		$this->assertEquals( $expected, $this->text_hover( 'bbPress' ) );
	}

	function test_hover_does_not_apply_to_comments_by_default() {
		$this->assertEquals( 'coffee2code', apply_filters( 'get_comment_text', 'coffee2code' ) );
		$this->assertEquals( 'coffee2code', apply_filters( 'get_comment_excerpt', 'coffee2code' ) );
	}

	function test_hover_applies_to_comments_via_setting() {
		$expected = $this->expected_text( 'coffee2code' );
		$this->test_hover_does_not_apply_to_comments_by_default();
		$this->set_option( array( 'text_hover_comments' => true ) );

		$this->assertEquals( $expected, apply_filters( 'get_comment_text', 'coffee2code' ) );
		$this->assertEquals( $expected, apply_filters( 'get_comment_excerpt', 'coffee2code' ) );
	}

	function test_hover_applies_to_comments_via_filter() {
		$expected = $this->expected_text( 'coffee2code' );
		$this->test_hover_does_not_apply_to_comments_by_default();
		add_filter( 'c2c_text_hover_comments', '__return_true' );

		$this->assertEquals( $expected, apply_filters( 'get_comment_text', 'coffee2code' ) );
		$this->assertEquals( $expected, apply_filters( 'get_comment_excerpt', 'coffee2code' ) );
	}

	/**
	 * @dataProvider get_default_filters
	 */
	function test_hover_applies_to_default_filters( $filter ) {
		$expected = $this->expected_text( 'coffee2code' );

		$this->assertGreaterThan( 0, strpos( apply_filters( $filter, 'a coffee2code' ), $expected ) );
	}

	function test_hover_applies_to_custom_filter_via_filter() {
		$this->assertEquals( 'coffee2code', apply_filters( 'custom_filter', 'coffee2code' ) );

		add_filter( 'c2c_text_hover_filters', array( $this, 'add_custom_filter' ) );

		c2c_TextHover::get_instance()->register_filters(); // Plugins would typically register their filter before this originally fires

		$this->assertEquals( $this->expected_text( 'coffee2code' ), apply_filters( 'custom_filter', 'coffee2code' ) );
	}

}
