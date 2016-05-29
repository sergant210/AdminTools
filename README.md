## AdminTools

It's a MODx Revolution Extra for administrators and developers. It adds some features:
- favorites elements to the elements tree (for each user individually);
- edited elements log (add a button to the topbar of the elements tree);
- ability to remember the last system settings filter parameters - namespace and area (for each user individually);
- ability to switch off check the permissions for users with sudo while building the tree. It reduces building time from 0,43s to 0,03s and amount of used memory from 6.5Mb to 5.2Mb (on my test site);
- added a system setting that switches off clearing the site cache when any resource is saving. MODX clears only the saved resource cache; 
- added a system setting for hiding component description at "Extras" menu;
- backend users can be authorized via email. Need to do some manipulations. 
- automatic log out the user if he is blocked or inactive.
- user notes. You can find it in the user menu next to other items (profile, messages, logout). Take a [look](http://modzone.ru/blog/2016/04/21/admintools-user-notes/).
- a tab "Resources" to the template form and a link to the template to the resource form.
- animation of the manager menu.

#### Setting the email authentication in the backend
* Create a new document with blank template and call the snippet "adminTools" in it. For example
```
<!DOCTYPE html>
<html lang="[[++cultureKey]]">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=[[++modx_charset]]" />
    <meta name="robots" content="noindex" />
    <base href="[[++site_url]]" />
    <title>[[*pagetitle]]</title>
</head>    
<body>
    [[!adminLogin]]
</body>
</html>
```
* Write the id of this document to the system setting **admintools_loginform_resource**.
* Switch the system setting **admintools_email_authorization** to TRUE.  

Now you can log in to the manager via email if you have the corresponding permission.

#### Remark
All these features can be switched off by the corresponding system setting. Some features are disabled by default.

To be continued.
