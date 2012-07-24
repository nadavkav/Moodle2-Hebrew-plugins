moodle-block_viewizer
================
DESCRIPTION:
This is a moodle 2.x block, providing alternative view for MyMoodle.
It is build up on the default Course Overview block, adding just some extra functionality and speed for those
users who are enrolled in many courses and the default MyMoodle page loads slowly.

The default course limit is set to 10.
So when user has less then 10 courses, all courses will be shown with updates from course modules.
If the user has more than 10 courses, courses will be splitted up as pages and updates wont be shown anymore from all courses.
The user has to turn on editing and then can select from which courses the updates will be shown.
If there are courses selected as important then user sees only those courses on MyMoodle page.

NB! Keep in mind, that you can only add this block once to your MyMoodle page.
Anywhere else it is not possible to add this block. It wont even show up in the list.
There might be some problems displaying remote courses(MNet). 

This block also includes Jquery(jquery-1.7.1.min.js).
 

FEATURES:
+AJAX Paging
+AJAX Course marking
+Welcome profile information
+Ability to limit courses per by admin setting
+Courses are in alphabetic order
+Marked courses appear in the same order as they are marked
+Hidden courses are placed last in the list


COMPATIBLITY:
Moodle 2.x should work fine.
Tested in: 
*Moodle 2.1
*Moodle 2.2
Not tested in:
*Moodle 2.0


INSTALLATION:
1. Download block from this git repository - https://github.com/t6nis/moodle2-viewizer
2. Upload it to your Moodle installation, into blocks/ folder
3. Visit Admin > Notifications

THEN THE MOST IMPORTANT PART!
4. Go to Users > Accounts > Profile fields
5. Create a new profile field - Text Input
6. !IMPORTANT! Short name MUST BE: viewizerimportantcourses
7. Name: Whatever you like(example: Important courses)
8. !IMPORTANT! Who is this field visible to? : Not Visible


Thats it! You´re good to go!