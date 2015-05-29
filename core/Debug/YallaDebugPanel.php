<?php

class YallaDebugPanel  extends Debug_Bar_Panel {

	protected $template_vars;

	/**
	 * Give the panel a title and set the enqueues.
	 */
	public function init() {
		$this->title( __( 'Template Vars', 'yalla-debug-bar-twig' ) );
	}

	public function set_template_vars($vars){
		$this->template_vars = $vars;

		return $vars;
	}

	/**
	 * Show the menu item in Debug Bar.
	 */
	public function prerender() {
		$this->set_visible( true );
	}

	/**
	 * Show the contents of the page.
	 */
	public function render() {

		if( empty($this->template_vars) ){
			echo "No vars found";
			return;
		}

		$this->prettyOutput($this->template_vars);
		
	}

	/**
	 * Indents a flat JSON string to make it more human-readable.
	 *
	 * @param string $json The original JSON string to process.
	 *
	 * @return string Indented version of the original JSON string.
	 */
	private function  prettyOutput($object, $indent = 0) {
		
		$space = '';
		$i = 0;
		while ($i < $indent) {
			$space .= '&emsp;&emsp;';
			$i++;
		}

		echo '<ol class="wpd-queries">';

		foreach($object as $key => $value){
			echo "<li>";
			echo "$space";
			echo "<span style=\"font-size: 14px !important;\">{$key}</span> : ";
			
			if( is_array($value) ){

				$this->prettyOutput($value, $indent + 1);
			}else{
				var_dump($value);
				//echo "$value";
			}

			echo "</li>";

		}
		echo '</ol>';

	}

}