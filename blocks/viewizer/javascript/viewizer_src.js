// This file is part of Viewizer block for Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * JS source for Viewizer
 *
 * @package    block
 * @subpackage viewizer
 * @copyright  2012 Tõnis Tartes <tonis.tartes@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$(document).ready(function() {
    
    //Define paths
    var basepath = window.location.protocol+'//'+window.location.hostname+'/';
    var dirpath = window.location.href;
    var indexpath = basepath+'my/index.php';
    
    //Show/Hide course details
    $('[class^=shdetails]').live('click', function() {
        
        var val = $(this).attr('value');
        var status = $('#course_'+val).css('display');
        
        if (status == 'none') {
            $('#course_'+val).show();  
            $('.shimage_'+val).attr('src', basepath+'/theme/image.php?theme=utstandard&image=myminus&component=block_viewizer');
       } else {
            $('#course_'+val).hide(); 
            $('.shimage_'+val).attr('src', basepath+'theme/image.php?theme=utstandard&image=myplus&component=block_viewizer');
        }
        
    });
    
    //Opener
    $('[class^=page]').live('click', function() {
        
        var val = $(this).attr('value');        
        
        $('.block_viewizer .content .viewizercourses').html('<div align="center"><img src="'+basepath+'/blocks/viewizer/pix/ajax-loader.gif"></div>');
        
        $.ajax({
            url: indexpath+'?page='+val,
            cache: false,
            success: function(result) {
                var dataload = $(result).find('.block_viewizer .content .viewizercourses').html();
                var paging = $(result).find('.block_viewizer .content #coursesbypage').html();
                $('.block_viewizer .content .viewizercourses').replaceWith('<div class="viewizercourses">'+dataload+'</div>'); 
                $('.block_viewizer .content #coursesbypage').replaceWith('<div id="coursesbypage">'+paging+'</div>'); 
            }
        });

    });
    
    
    /* Set important */
    $('[class^=viewizer_important]').live('click', function() {
        
        var val = $(this).attr('value'); 
        var action = $(this).attr('name');
        var currpage = $('.block_viewizer #coursesbypage .currpage').attr('value');
        var target = $('.block_viewizer .content').find('.viewizerimportant').html();
        var existing = []; 
        
        $('.block_viewizer .content .viewizerimportant .headingcontainer').each(function(index) {
            existing.push($(this).find('.viewizer_important').attr('value'));
        });

        if (action == 'add' && jQuery.inArray(val, existing) > -1) {
            alert('Already added!');
            return false;
        }
        
        if (target != null) {
            $('.block_viewizer .content .viewizerimportant').html('<div align="center"><img src="'+basepath+'/blocks/viewizer/pix/ajax-loader.gif"></div>');
        } else {
            $('.block_viewizer .content #viewizer_courses').prepend('<div class="box coursebox viewizerimportant"><div align="center"><img src="'+basepath+'/blocks/viewizer/pix/ajax-loader.gif"></div></div>');
        }
        
        $.ajax({
            type: 'POST',
            url: basepath+'blocks/viewizer/ajax.php',
            cache: false,
            data: 'mod=viewizer&action='+action+'&id='+val,
            success: function(data) {
                $.ajax({
                    url: indexpath+'?page='+currpage,
                    cache: false,
                    success: function(result) {
                        var dataload = $(result).find('.block_viewizer .content .viewizerimportant').html();
                        if (dataload == null) {
                            $('.block_viewizer .content .viewizerimportant').remove();                      
                        } else {
                            $('.block_viewizer .content .viewizerimportant').replaceWith('<div class="box coursebox viewizerimportant">'+dataload+'</div>'); 
                        }
                    }
                });
            }
        });

    });
});