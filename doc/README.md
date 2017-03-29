# Contact Us: 
## Amendment to Handle Zendesk Failures


### Overview

This change introduces the following behaviour:

* Tickets sent to the Zendesk API are now retried a maximum of 3 times. 
This is accomplished through the addition of a new table (pt_zendesk) and a cron job to retry.  

* In the event of failure, the exception message is logged to the pt_zendesk table. No changes have been made to the 
 Zendesk API client, and the exception messages are as they always were.

* After 3 failures, an email is sent to the site's administrator with a notification of the situation.
 
* The Collected Information list has been amended to show the status of the eZ -> Zendesk transmission. 
 A new view has been added to manually trigger a resend, if the Zendesk transmission is stalled or has failed. 


### Installation

* Set up the new pt_zendesk table: execute sql/mysql/mysql.sql

* Add the **contact_us** extension to the **admin** siteaccess (to see the new "Zendesk API" column in the Collected Information list page).

* If a non-administator user is to access the Collected Information list page (which includes the new Zendesk API column)
and/or the Resend to Zendesk view, it may be necessary to grant **read** access on the **contact_us** module to 
appropriate user roles.

* Regenerate autoload arrays and clear caches.


### Notes

* The cronjob must be executed from mb_global or pt_global. If it is executed from mb_admin or pt_admin, 
blank emails may arrive. The cronjob is set up to run under the "frequent" group.

* Emails are currently sent **to** the email address listed in site.ini, under 

````
[InformationCollectionSettings] 
EmailReceiver=....
````

 If this setting is not provided, falls back to 
 
 ````
 [MailSettings] 
 AdminEmail=...
 ````
 
 