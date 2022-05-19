# contact-dedupe
This project is a simple (pretty dumb) PHP script that imports an Outlook contact CSV export, It creates an output CSV that can be re-imported into Outlook.
The logic merges on unique First name and Last name (which admittedly can cause incorrect merges) 

To merge and de-deplicate Outlook contacts go through these steps:
1) Export contacts to CSV
2) Edit the `dedupe.php` to use the file name of the CSV created in step 1 and add the output file name
3) Run `php dedupe.php`
4) In Outlook, delete all your contacts
5) In Outlook import contacts using the CSV defined in step 2 
