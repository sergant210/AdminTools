[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)

## AdminTools

It's a MODx Revolution Extra for administrators and developers. It adds some features:
- favorite elements to the elements tree (for each user individually);
- the log of the edited elements (add a button to the topbar of the elements tree);
- ability to remember the last system settings filter parameters - namespace and area (for each user individually);
- ability to switch off check the permissions for users while building the tree. It reduces building time from 0,43s to 0,03s and amount of used memory from 6.5Mb to 5.2Mb (on my test site);
- a system setting which disables clearing the site cache while saving the resource, MODX clears only the current resource cache; 
- a checkbox "Create cache" to the resource form which allows to save the resource to the cache when you save it.
- a system setting for hiding component description at "Extras" menu;
- backend users can log in via email or user name. Need to do some manipulations. 
- automatic log out the user if he is blocked or inactive.
- user notes. You can find it in the user menu next to other items (profile, messages, logout). Take a [look](http://modzone.ru/blog/2016/04/21/admintools-user-notes/).
- a tab "Resources" to the template form and a link to the template to the resource form.
- animation of the manager menu to prevent misclick.
- alternative permissions for resources (added "Permissions" tab to the resource form).
- plugin table with bound events.
- the tree sidebar can be placed either on the left or on the right side.
- 2 color themes with ability to customize them or add custom one.
- ability to load custom style and javascript files in the manager.
- permissions for package actions.
- disable setting the authenticated user from "mgr" context for a guest user on a website.
- ability to lock the admin panel without logging out by timeout or manually.
- unread messages indicator.

### Setting the email authentication in the backend
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

**IMPORTANT!**  
Only user with the same user-agent and ip can log in using the authentication link.

### Alternative permissions for resources
Use it to restrict access to the pages of the site following the rules: 
* Permissions for everyone have the lowest priority.
* Permissions for user groups have a higher priority. This permissions are applied from top to bottom of the group list. To change the order of application use the priority field. 
* User permissions have the highest priority.  

To close pages from guests add the permission on the resource page for the guests with 'deny' value.

### Color themes
By default available 2 themes - "dark" and "purple". But you can create your own. Copy the purple theme file - **assets/components/admintools/css/mgr/themes/purple.css** and give it the name of your theme. For example, green.css. Change the theme class '.purple-theme' on the '.green-theme' within. Tune it as needed. Set the system setting **admintools_theme** to "green".

### Custom style and javascript files
This feature is designed to add custom style and js files to the manager interface. You can customize the manager interface as you wish. Use the corresponding system settings - **admintools_custom_css** and **admintools_custom_js**. If you want to add multiple files, separate them by commas.  
For example, if you want to add nice scroll to the tree sidebar you need to add two js files - jquery and nicescroll. Download the [nicescroll](//code.google.com/archive/p/jquery-nicescroll/downloads) library and save it in the *assets/components/admintools/js/mgr/custom/* folder. Then open it and add to the end next code
```
Ext.onReady(
    function() {
        $("#modx-leftbar-tabpanel").parent('div').niceScroll({zindex:1000});
    }
);
```  
Now set the value `//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js,{adminToolsJs}custom/jquery.nicescroll.js` to the **admintools_custom_js** system setting. Enjoy.  
![](https://file.modx.pro/files/b/2/b/b2bbc6344dabb41d546abf2486a066ae.png)
\* Use `{adminToolsJs}` instead of *assets/components/admintools/js/mgr/*, `{adminToolsCss}` instead of *assets/components/admintools/css/mgr/*.

### Package actions
This feature allows to prohibit certain actions with packages. To do it you need to specify the "admintools_package_actions" system setting according to the format:
```$javascript
// Javascript object format
{packageName1: {action1:false, action2:'Special message for the action2', action3:false, message:"Default message!"},
packageName2, {action1:"You can't do it!"}}
```
The package name is case sensitive.
For example, you can prevent the manager from deleting the "Ace" package.
```$javascript
{Ace: {remove:false, message:"You can't do it! This is a very important package."}}
// is equivalent to
{Ace: {remove:"You can't do it!"}}
```
Allow only viewing the details of the package.
```$javascript
{Ace: {details:true, all:"This action is prohibited."}}
```

**Available actions:**
- install
- reinstall
- uninstall
- update
- remove
- checkupdate
- details
- all
- message

### Lock the admin panel
![Lock admin panel](https://modzone.ru/assets/images/articles/223/admintools_lock_en2.jpg)
![Lock admin panel](https://modzone.ru/assets/images/articles/223/admintools_lock_en.jpg)

#### Remark
All these features can be switched off by the corresponding system setting. Some features are disabled by default.

[Russian documentation](https://modzone.ru/documentation/admintools.html).
