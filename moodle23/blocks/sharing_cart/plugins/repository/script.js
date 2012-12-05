/**
 * Repository Script
 *
 * @author VERSION2 Inc.
 * @version $Id: script.js,v 1.2 2009/11/30 09:17:22 akiococom Exp $
 * @package repository
 */

/**
 * Upload material to repository
 *
 * @param DOMElement <a>
 */
sharing_cart_handler.prototype.repository_upload = function(a)
{
	window.open(this.getParam("block_root")
		+ "plugins/repository/upload.php?" + [
			"id="     + this.a2id(a),
			"course=" + this.getParam("course_id")
		].join("&"));
	return false;
};
