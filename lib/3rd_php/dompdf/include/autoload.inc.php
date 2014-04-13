<?php
/**
 * @package dompdf
 * @link    http://dompdf.github.com/
 * @author  Benj Carson <benjcarson@digitaljunkies.ca>
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

/**
 * DOMPDF autoload function
 *
 * If you have an existing autoload function, add a call to this function
 * from your existing __autoload() implementation.
 *
 * @param string $class
 */
function DOMPDF_autoload($class) {
	$DOMPDF_CLASSES=array (
		'Absolute_Positioner' => 'include/absolute_positioner.cls.php',
		'Abstract_Renderer' => 'include/abstract_renderer.cls.php',
		'Attribute_Translator' => 'include/attribute_translator.cls.php',
		'Block_Frame_Decorator' => 'include/block_frame_decorator.cls.php',
		'Block_Frame_Reflower' => 'include/block_frame_reflower.cls.php',
		'Block_Positioner' => 'include/block_positioner.cls.php',
		'Block_Renderer' => 'include/block_renderer.cls.php',
		'Cached_PDF_Decorator' => 'include/cached_pdf_decorator.cls.php',
		'Canvas' => 'include/canvas.cls.php',
		'Canvas_Factory' => 'include/canvas_factory.cls.php',
		'Cellmap' => 'include/cellmap.cls.php',
		'CPDF_Adapter' => 'include/cpdf_adapter.cls.php',
		'CSS_Color' => 'include/css_color.cls.php',
		'DOMPDF' => 'include/dompdf.cls.php',
		'DOMPDF_Exception' => 'include/dompdf_exception.cls.php',
		'DOMPDF_Image_Exception' => 'include/dompdf_image_exception.cls.php',
		'Fixed_Positioner' => 'include/fixed_positioner.cls.php',
		'Font_Metrics' => 'include/font_metrics.cls.php',
		'Frame' => 'include/frame.cls.php',
		'FrameList' => 'include/frame.cls.php',
		'FrameListIterator' => 'include/frame.cls.php',
		'FrameTreeList' => 'include/frame.cls.php',
		'FrameTreeIterator' => 'include/frame.cls.php',
		'Frame_Decorator' => 'include/frame_decorator.cls.php',
		'Frame_Factory' => 'include/frame_factory.cls.php',
		'Frame_Reflower' => 'include/frame_reflower.cls.php',
		'Frame_Tree' => 'include/frame_tree.cls.php',
		'GD_Adapter' => 'include/gd_adapter.cls.php',
		'Image_Cache' => 'include/image_cache.cls.php',
		'Image_Frame_Decorator' => 'include/image_frame_decorator.cls.php',
		'Image_Frame_Reflower' => 'include/image_frame_reflower.cls.php',
		'Image_Renderer' => 'include/image_renderer.cls.php',
		'Inline_Frame_Decorator' => 'include/inline_frame_decorator.cls.php',
		'Inline_Frame_Reflower' => 'include/inline_frame_reflower.cls.php',
		'Inline_Positioner' => 'include/inline_positioner.cls.php',
		'Inline_Renderer' => 'include/inline_renderer.cls.php',
		'Javascript_Embedder' => 'include/javascript_embedder.cls.php',
		'Line_Box' => 'include/line_box.cls.php',
		'List_Bullet_Frame_Decorator' => 'include/list_bullet_frame_decorator.cls.php',
		'List_Bullet_Frame_Reflower' => 'include/list_bullet_frame_reflower.cls.php',
		'List_Bullet_Image_Frame_Decorator' => 'include/list_bullet_image_frame_decorator.cls.php',
		'List_Bullet_Positioner' => 'include/list_bullet_positioner.cls.php',
		'List_Bullet_Renderer' => 'include/list_bullet_renderer.cls.php',
		'Null_Frame_Decorator' => 'include/null_frame_decorator.cls.php',
		'Null_Frame_Reflower' => 'include/null_frame_reflower.cls.php',
		'Null_Positioner' => 'include/null_positioner.cls.php',
		'Page_Cache' => 'include/page_cache.cls.php',
		'Page_Frame_Decorator' => 'include/page_frame_decorator.cls.php',
		'Page_Frame_Reflower' => 'include/page_frame_reflower.cls.php',
		'PDFLib_Adapter' => 'include/pdflib_adapter.cls.php',
		'PHP_Evaluator' => 'include/php_evaluator.cls.php',
		'Positioner' => 'include/positioner.cls.php',
		'Renderer' => 'include/renderer.cls.php',
		'Style' => 'include/style.cls.php',
		'Stylesheet' => 'include/stylesheet.cls.php',
		'Table_Cell_Frame_Decorator' => 'include/table_cell_frame_decorator.cls.php',
		'Table_Cell_Frame_Reflower' => 'include/table_cell_frame_reflower.cls.php',
		'Table_Cell_Positioner' => 'include/table_cell_positioner.cls.php',
		'Table_Cell_Renderer' => 'include/table_cell_renderer.cls.php',
		'Table_Frame_Decorator' => 'include/table_frame_decorator.cls.php',
		'Table_Frame_Reflower' => 'include/table_frame_reflower.cls.php',
		'Table_Row_Frame_Decorator' => 'include/table_row_frame_decorator.cls.php',
		'Table_Row_Frame_Reflower' => 'include/table_row_frame_reflower.cls.php',
		'Table_Row_Group_Frame_Decorator' => 'include/table_row_group_frame_decorator.cls.php',
		'Table_Row_Group_Frame_Reflower' => 'include/table_row_group_frame_reflower.cls.php',
		'Table_Row_Group_Renderer' => 'include/table_row_group_renderer.cls.php',
		'Table_Row_Positioner' => 'include/table_row_positioner.cls.php',
		'TCPDF_Adapter' => 'include/tcpdf_adapter.cls.php',
		'Text_Frame_Decorator' => 'include/text_frame_decorator.cls.php',
		'Text_Frame_Reflower' => 'include/text_frame_reflower.cls.php',
		'Text_Renderer' => 'include/text_renderer.cls.php',
		'Cpdf' => 'lib/class.pdf.php',
		'HTML5_Data' => 'lib/html5lib/Data.php',
		'HTML5_InputStream' => 'lib/html5lib/InputStream.php',
		'HTML5_Parser' => 'lib/html5lib/Parser.php',
		'HTML5_Tokenizer' => 'lib/html5lib/Tokenizer.php',
		'HTML5_TreeBuilder' => 'lib/html5lib/TreeBuilder.php',
	);
	$filename = DOMPDF_DIR . "/" . $DOMPDF_CLASSES[$class];
	//$filename = DOMPDF_INC_DIR . "/" . mb_strtolower($class) . ".cls.php";

	if ( is_file($filename) ) {
		include_once $filename;
	}
}

// If SPL autoload functions are available (PHP >= 5.1.2)
if ( function_exists("spl_autoload_register") ) {
	$autoload = "DOMPDF_autoload";
	$funcs = spl_autoload_functions();

	// No functions currently in the stack.
	if ( !DOMPDF_AUTOLOAD_PREPEND || $funcs === false ) {
		spl_autoload_register($autoload);
	}

	// If PHP >= 5.3 the $prepend argument is available
	else if ( PHP_VERSION_ID >= 50300 ) {
		spl_autoload_register($autoload, true, true);
	}

	else {
		// Unregister existing autoloaders...
		$compat = (PHP_VERSION_ID <= 50102 && PHP_VERSION_ID >= 50100);

		foreach ($funcs as $func) {
			if (is_array($func)) {
				// :TRICKY: There are some compatibility issues and some
				// places where we need to error out
				$reflector = new ReflectionMethod($func[0], $func[1]);
				if (!$reflector->isStatic()) {
					throw new Exception('This function is not compatible with non-static object methods due to PHP Bug #44144.');
				}

				// Suprisingly, spl_autoload_register supports the
				// Class::staticMethod callback format, although call_user_func doesn't
				if ($compat) $func = implode('::', $func);
			}

			spl_autoload_unregister($func);
		}

		// Register the new one, thus putting it at the front of the stack...
		spl_autoload_register($autoload);

		// Now, go back and re-register all of our old ones.
		foreach ($funcs as $func) {
			spl_autoload_register($func);
		}

		// Be polite and ensure that userland autoload gets retained
		if ( function_exists("__autoload") ) {
			spl_autoload_register("__autoload");
		}
	}
}

else if ( !function_exists("__autoload") ) {
	/**
	 * Default __autoload() function
	 *
	 * @param string $class
	 */
	function __autoload($class) {
		DOMPDF_autoload($class);
	}
}
