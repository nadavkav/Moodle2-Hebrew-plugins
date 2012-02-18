<?php
/*
 * File: domiterator.php
 * Author: 	Paul Krix
 * Date:	22/07/2010
 *
 * Requires domparser.php. Iterates over a DOM-like tree (depth-wise tree traversal, currently no breadthwise traversal).
 *
 */

class Dom_Iterator {

	var $current_node;
	var $path;
	var $idx;
	
	function init($root) {
		$this->current_node = $root;
		$this->idx = 0;
		$this->path = array();
	}
	
	function get_next() { //returns next dom element in the tree
		$cur = $this->current_node;
		$children = $cur->get_children();
		$num_children = sizeof($children);
		
		if($this->idx >= $num_children) {
			if(sizeof($this->path) == 0) {
				return false;
			}		
			$this->current_node = $cur->parent;
			$this->idx = array_pop($this->path);
			return $this->get_next();
		}
		$this->current_node = $children[$this->idx];
		array_push($this->path, $this->idx+1);
		$this->idx = 0;
		return $this->current_node;
	}
	
	function get_next_sibling() { //returns the next sibling in the tree, skipping any children of the current element.
		$parent = $this->current_node->parent;
		if($parent === false) {
			return false;
		}
		$children = $parent->get_children();		
		$this->idx = array_pop($this->path);
		$this->current_node = $children[$this->idx];		
		array_push($this->path, $this->idx+1);		
		$this->idx = 0;
		return $this->current_node;
	}
	
}

?>