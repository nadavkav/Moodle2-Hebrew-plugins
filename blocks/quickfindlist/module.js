M.block_quickfindlist = {

    sesskey: null,

    init: function(Y, roleid, userfields, url, courseformat, courseid, sesskey) {
        this.Y = Y;
        if (this.instances === undefined) {
            this.instances = new Array();
        }
        this.sesskey = sesskey;

        var instance = {
            'roleid': roleid,
            'userfields': userfields,
            'url': url,
            'courseformat': courseformat,
            'courseid': courseid,
            'progress': Y.one('#quickfindprogress'+roleid),
            'xhr': null
        }
        this.instances[roleid] = instance;
        Y.on('keyup', this.search_on_type, '#quickfindlistsearch'+roleid);
        Y.on('submit', this.search_on_submit, '#quickfindform'+roleid);
    },

    search_on_type: function(e) {
        var searchstring = e.target.get('value');
        var roleid = /[\-0-9]+/.exec(e.target.get('id'))[0];
        M.block_quickfindlist.search(searchstring, roleid);
    },

    search_on_submit: function(e) {
        e.preventDefault();
        var roleid = /[\-0-9]+/.exec(e.target.get('id'))[0];
        var searchstring = e.target.getById('quickfindlistsearch'+roleid).value;
        M.block_quickfindlist.search(searchstring, roleid);
    },

    search: function(searchstring, roleid) {

        var Y = this.Y;
        var instance = this.instances[roleid];

        uri = M.cfg.wwwroot+'/blocks/quickfindlist/quickfind.php';
        if (instance.xhr != null) {
            instance.xhr.abort();
        }
        instance.progress.setStyle('visibility', 'visible');
        instance.xhr = Y.io(uri, {
            data: 'role='+roleid
                +'&name='+searchstring
                +'&courseformat='+instance.courseformat
                +'&courseid='+instance.courseid
                +'&sesskey='+this.sesskey,
            context: this,
            on: {
                success: function(id, o) {
                    var response = Y.JSON.parse(o.responseText);
                    var instance = this.instances[response.roleid];
                    var list = Y.Node.create('<ul />');
                    for (p in response.people) {
                        var userstring = instance.userfields.replace('[[firstname]]', response.people[p].firstname);
                        userstring = userstring.replace('[[lastname]]', response.people[p].lastname);
                        userstring = userstring.replace('[[username]]', response.people[p].username);
                        li = Y.Node.create('<li><a href="'+instance.url+'&id='+response.people[p].id+'">'+userstring+'</a></li>');
                        list.appendChild(li);
                    }
                    instance.progress.setStyle('visibility', 'hidden');
                    Y.one('#quickfindlist'+roleid).replace(list);
                    list.set('id', 'quickfindlist'+roleid);
                },
                failure: function(id, o) {
                    if (o.statusText != 'abort') {
                        var instance = this.currentinstance;
                        instance.progress.setStyle('visibility', 'hidden');
                        if (o.statusText !== undefined) {
                            instance.listcontainer.set('innerHTML', o.statusText);
                        }
                    }
                }
            }
        });
    }
}
