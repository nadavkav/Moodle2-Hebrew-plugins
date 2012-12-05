Steps to install team assignments type to a 2.1 Moodle installation
====================================================================

1) unzip team-assignment-type.zip at the moodle root or manually copy the team directory
   to <moodle root>/mod/assignment/type/

2) log in to your moodle site as Admin user.

3) visit the Admin notifications page.

4) run admin notifications. Two tables ('assignment_team' and 'assignment_team_student') will be installed.

IMPORTANT NOTE!
===============
Note that due to Moodle's new policy of enforcing a new naming convention on databases tables, the team
assignment table names have been changed from "team" to "assignment_team" and from "team_student" to 
"assignment_team_student". When updating to this new version new tables WILL be created and 
if there are tables with the old names they will no longer be used.
If you wish to retrieve data from old tables you may run the SQL scripts below:

INSERT INTO mdl_assignment_team SELECT * FROM mdl_team;
INSERT INTO mdl_assignment_team_student SELECT * FROM mdl_team_student;
   
NOTE ON UPDATING LANGUAGEFILES
==============================
When there is a update on language files, the language files in the cache have to be removed from moodledata in order to 
refresh.

Turnitin integration
====================
Some Moodle sites may be using the Turnitin integration developed by Catalyst IT Ltd. 
See (http://moodle.org/mod/data/view.php?d=13&rid=1562). If so, then it is likely that they
will want to use Turnitin with the Team assignment type. Optional, Turnitin code 
has been added to the Team assignment type in the following functions:

* function print_team_answer($teamid)
* function print_user_files($userid=0, $return=false, $teamid)
* function upload_file()
* function finalize()

If you wish to enable Turnitin integration then you must:
1. Install the Turnitin integration module
2. Uncomment the code in the above functions
3. Replace the function assignment_get_tii_file_info($file) in <moodle root>/mod/assignment/lib.php with
   the new version in assignment-lib.patch (available to download from the Lightwork site)
   
** Note that the team assignment Turnitin integration will only work if your Moodle installation has
   a valid Turnitin license and the Catalyst IT Ltd Turnitin integration module has been installed