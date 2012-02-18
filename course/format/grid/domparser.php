<?php
/*
 * File: domparser.php
 * Author: 	Paul Krix
 * Date:	22/07/2010
 *
 * Inappropriately named as it is really a simple XML parser used to parse HTML elements into a tree. Only parses tag name and text nodes.
 *
 */

class Dom_Node {
	
	var $parent;
	var $children;
	var $name;
	var $type;
	var $contents;
	var $options; //for future use

	function init($name, $parent) {
		$this->name = $name;
		$this->parent = $parent;
		$this->type = "pair";
		$this->children = array();
	}
	
	function set_type($type) {
		$this->type = $type;
	}
	
	function get_name() {
		return $this->name;
	}

	function add_text_node($contents) {
		$text_node = new Dom_Node();
		$text_node->init("text", $this);
		$text_node->contents = $contents;
		$this->children[] = $text_node;
	}

	function add_child($node) {
		$this->children[] = $node;
	}
	
	function match_upwards($tag_name) { 
		//searches for an element higher in the tree that matches the current tag name.
		//used to find a closings tag's matching opening tag.
		if($this->parent == null) {
			return false;
		}
		if($this->is_tag_name($tag_name)) {
			return $this->parent;
		} else {
			return $this->parent->match_upwards($tag_name);
		}
	}
	
	function is_tag_name($tag_name) {
		if($this->name == $tag_name) {
			return true;
		}
		return false;
	}
	
	function get_children() {
		return $this->children;
	}
	
	function print_html() {
		//used for debugging. Prints html entities of the tree.
		if($this->name == "text") {
			echo $this->contents;
			return;
		}
		if($this->name == "root") {
			for($i = 0; $i < sizeof($this->children); $i++) {
				$this->children[$i]->print_html();
			}				
			return;
		}
		if($this->type == "single") {
			echo htmlentities("<" . $this->name . " />");
			return;
		}
		echo htmlentities("<" . $this->name . ">");
		for($i = 0; $i < sizeof($this->children); $i++) {
			$this->children[$i]->print_html();
		}		
		echo htmlentities("</" . $this->name .">");
	}
	
	function print_node($depth) {
		//used for debugging. prints indented element tag names
		for($j = 0; $j < $depth; $j++) {
			echo "-";
		}
		echo $this->name . "<br />";
		for($i = 0; $i < sizeof($this->children); $i++) {
			$this->children[$i]->print_node($depth+1);
		}
	}
}

class Dom_Parser {

	var $source;
	var $root_node;
	
	function init($html) {
		$this->source = $html;	
		$this->root_node = new Dom_Node();
		$this->root_node->init("root", null);
		$this->parse($this->source, $this->root_node);		
	}
	
	function get_tree() {
		return $this->root_node;
	}	
	
	function print_html() {
		$this->root_node->print_html();		
	}
	
	function print_tree() {
		$this->root_node->print_node(0);
	}

	function parse($source, $root) {
		//pointless, only here so function names make sense.
		$this->parse_tag($source, $root);
	}
	
	function parse_tag($source, $current_node) {
		//parses a tag, adds the tag into the element tree and then recursively calls itself 
		//to find the next tag.
		
		//Find the start of the next tag
		$tag_start = strpos($source, '<', 0);
		if($tag_start === false) {
			$this->add_final_text($source, $current_node);
			return true;
		}
				
		//Find the end of that tag		
		$tag_end = $this->find_tag_end($source, $tag_start+1);
		if($tag_end === false) {
			$this->add_final_text($source, $current_node);		
			return true;
		}		
		
		//Determine tag name
		$contents = substr($source, 0, $tag_start);
		$current_node->add_text_node($contents);
		
		$new_source = substr($source, $tag_end+1);
				
		if($source{$tag_start+1} == "/") {
			$tag_name = $this->get_tag_name($source, $tag_start+2, $tag_end);				
			//closing tag - change $current_node to the matching node in the tree
			$element = $current_node->match_upwards($tag_name);
			if($element === false) {
				return false;
			} 
			$this->parse_tag($new_source, $element);
			return true;
		}		
		
		$tag_name = $this->get_tag_name($source, $tag_start+1, $tag_end);		

		//Create tag element and add it to the tree
		$element = new Dom_Node();
		$element->init($tag_name, $current_node);
		$current_node->add_child($element);

		if($source{$tag_end+-1} == "/") {
			//contained tag - $current_node stays the same
			$element->set_type("single");
			$this->parse_tag($new_source, $current_node);
			return true;
		}		
		
		$this->parse_tag($new_source, $element);
	}
	
	function add_final_text($source, $current_node) {
		//Grabs the text between the final tag and the end of the html source
		if(strlen($source) > 0) {
			$current_node->add_text_node($source);
		}
	}
	
	function find_tag_end($html, $start) { //This finds the end of a tag, NOT a closing tag.
		//Finds the '>' part of a tag. Assumes $start is the character AFTER the '<' character
		//Returns the position of the end tag, or false if none is found
				
		$end_tag_pos = strpos($html, ">", $start);
		if($end_tag_pos === false) {
			return false;
		}	
		
		//Make sure the '>' isn't within quotes.	
		$quotes_end = $this->check_quotes($html, $start, $end_tag_pos);
		if($quotes_end > -1) {
			return $this->find_tag_end($html, $quotes_end+1);
		} else if($quotes_end === false) {
			return false;
		}
		return $end_tag_pos;
	}	

	function check_quotes($html, $start, $limit) {
		//Checks to see if there are open quotes between $start and $limit
		$single_s = strpos($html, '\'', $start);
		$double_s = strpos($html, '"', $start);	
		
		//quotes don't interfere
		if(	!$single_s && !$double_s 
			|| (!$single_s && $double_s > $limit)
			|| (!$double_s && $single_s > $limit)
			|| ($double_s > $limit && $single_s > $limit)
			) {
			return -1;
		}
		
		if(!$single_s || $double_s < $single_s) {
			return strpos($html, '"', $double_s+1);
		}
		
		if(!$double_s || $single_s < $double_s) {
			return strpos($html, '\'', $single_s+1);
		}
		
		return -1;
	}

	
	function get_tag_name($html, $start, $end) {
		//Finds a tags name. Assumes start is character AFTER the '<' character
	
		$space_pos = strpos($html, ' ', $start);
		$end_name_pos = $space_pos;
		if($space_pos === false || $end < $space_pos) {
			$end_name_pos = $end;	
		}
	
		$tag_name = substr($html, $start, ($end_name_pos - $start));
		return $tag_name;
	}	

}

function get_dom_tree($html) {
	//Accepts html source, returns a tree of Dom_Nodes
	$dp = new Dom_Parser();
	$dp->init($html);
	
	return $dp->get_tree();
}

?>
