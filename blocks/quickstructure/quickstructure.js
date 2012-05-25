
// initialisation call from labels_form.php
// set up event handlers for dealing with advanced painting of colours onto 
// swatches and sections names
function init_painting(Y){
    Y.use('dom',function(){
        Y.all(".pickerb").on('focus', function(e){
            var col = e.target.get('value');
            var str = e.target.get('id');
            name=str.replace('colb','name');    
            Y.one("#"+name).setStyle("background", col);    
        });        
        Y.one("#colb").on('focus', function(e){
            var col = e.target.get('value'); 
            Y.all(".section").setStyle("background", col);
            
            var str = e.target.get('id');
            name=str.replace('colb','name');
            Y.all(".pickerb").each(function(node){
                nn=node.get('id');
                Y.one("#"+nn).set('value',col);
                Y.one("#colpicked_"+nn).setStyle("background", col);
            });   
        });        
        Y.all(".pickerf").on('focus', function(e){
            var col = e.target.get('value'); 
            var str = e.target.get('id');
            name=str.replace('colf','name');
            Y.one("#"+name).setStyle("color", col);    
        });        
        Y.one("#colf").on('focus', function(e){
            var col = e.target.get('value'); 
            Y.all(".section").setStyle("color", col);   
            
            var str = e.target.get('id');
            name=str.replace('colf','name');
            Y.all(".pickerf").each(function(node){
                nn=node.get('id');
                Y.one("#"+nn).set('value',col);
                Y.one("#colpicked_"+nn).setStyle("background", col);
            });   
               
        });
           
    });
}

// initialisation call from block - setup menu if required and event handlers
function quickstructuremenu (Y, code){
    Y.use('dom',function(){
        Y.all('.right.side').addClass('qs_hidden');
        // menu mode toggler
        Y.one('#qs_showmenu').on('click',
            function(e){
                var themenu = Y.one('#qs_topmenu');
                menumode = Y.one('#qs_showmenu').get('checked');
                if(menumode){
                  themenu.removeClass('qs_hidden'); 
                }else{
                  themenu.addClass('qs_hidden');
                }    
            }
        );
        
        var holder = Y.one('#maincontent');
        var menu = Y.Node.create(code);
        holder.insert(menu,'after');
         
        
        // folding mode toggler
        Y.one('#qs_folding').on('click',
            function(e){
                Y.all(".section.main").each(function(node){ 
                    node.removeClass('qs_hidden');
                });
                folding = Y.one('#qs_folding').get('checked');
                if(folding){
                    Y.all('.qs_fold').each(function(node){
                        node.set('href','#qs_topmenu');
                    })
                }else{
                    Y.all('.qs_fold').each(function(node){
                        node.set('href','#section-'+parse_id(node.get('id')));
                    });
                };
            }
        );
        
        // initialise
        Y.all('.qs_fold').each(function(node){
                        node.set('href','#qs_topmenu');
        });         
        Y.one('#qs_showmenu').set('checked','checked');
        // moodle 2 tries to stop quickstructure style!
        Y.all('.no-overflow').removeClass('no-overflow');    
        
        // set up event handlers for folding links
        Y.all('.qs_fold').on('click',function(e){
           qs_fold(Y,parse_id(e.currentTarget.get('id')));              
        });
    });
}

function qs_unfold(Y){
    Y.all(".section.main").each(function(node){ 
            node.removeClass('qs_hidden');
    });    
    var navbar = Y.one("#qs_navbar");
    if(navbar){navbar.remove();}
}

// handles a foldin event (attached to click event of menu image links and link in block))
function qs_fold(Y,section){
   section = parseInt(section);
   folding = Y.one('#qs_folding').get('checked');
   menu = Y.one('#qs_showmenu').get('checked');
   if(folding){
        // hide all sections 
        Y.all(".section.main").each(function(node){ 
            node.addClass('qs_hidden');
        });
        // show required section
        Y.one('#section-'+section).removeClass('qs_hidden');

        // if no navbar make one
        var navbar = Y.one("#qs_navbar");
        if(!navbar){
              nxt = '<div id="qs_next" >'+ 'next' + ' &gt;&gt;</div>';
              prv = '<div id="qs_prev">&lt;&lt; ' + 'prev' + '</div>';
              all = '<div id="qs_all">View All</div>';
              cont = '<div width=100% id="qs_navbar" class="qs_menu" style=\'color:#888;font-size:1em;\'>' + prv + nxt + all + '</div>';
              var topics = Y.one('ul.topics');
              var nav = Y.Node.create(cont);
              topics.insert(nav,'after');
              
              // unfold view all event handler doesn't change
              Y.one('#qs_all').on('click',function(e){
                   qs_unfold(Y);              
              });
        }
        // label and set prev link handler
        p=section-1;
        var nd = Y.one('#section-'+p+' .qs_header');
        if(nd){
            text = nd.get('innerHTML');
            if(text=='')text='Section '+p;
            text = '&lt;&lt; ' + text;
        }else{
            text = '';
        }
        Y.one('#qs_prev').setContent(text).detach('click').on('click',function(e){
           qs_fold(Y,p);              
        })    
        
        // label and set next link handler
        n=section+1;
        var nd = Y.one('#section-'+n+' .qs_header');
        if(nd){
            text = nd.get('innerHTML');
            if(text=='')text='Section '+n;
            text = text + ' &gt;&gt;';
        }else{
            text = '';
        }
        Y.one('#qs_next').setContent(text).detach('click').on('click',function(e){
           qs_fold(Y,n);              
        });
        
   }
}  
function parse_id(id){
    var bits=id.split('_');
    return bits[bits.length-1];    
}



