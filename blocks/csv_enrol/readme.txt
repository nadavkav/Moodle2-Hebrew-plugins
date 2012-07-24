##Block CSV Enrol##

Description: This block allows teachers to enrol users into a course by allowing a teacher to upload an csv file with only email addresses.
After running the upload you can download a txt file that contains a log of the enrolled and failed users.

License: GNU General Public License http://www.gnu.org/copyleft/gpl.html

Usage:

The admin user must add this block to the Moodle course page
This block will only be visible for teachers.

Uploading a CSV:
Upload a file using the Manage uploaded files button,
After clicking this button the file manager appears.
Upload the CSV file and click on save changes, wait for Moodle to process it, the CSV gets processed immidiately!

When done you will get a txt file with the log of the enrol results.
[filename] = upload_[date]_[time]

[filename].csv : your uploaded CSV
[filename].log : info on number of adresses processed etc.


