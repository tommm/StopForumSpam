[![Xekko](http://xekko.co.uk/public/images/logo_xekko_color.png "Xekko Resources")](http://resources.xekko.co.uk "Xekko Resources")

## Stop Forum Spam for MyBB
This plugin is aimed to help combat the ever present war on spam, this enables you to check all new registrations to your forum against the stopforumspam.com database to see if they are known spammers. It can check their username, email and IP address.

It is very configurable so you can dial in exactly the level of scrutiny you want, you can choose whether or not to check the users email, IP and username and also whether all their details have to match those of a spammer or just one of their details.

### About
When a user registers on a forum their user details (chosen by the administrator) are sent to StopForumSpam.com for verification. Their details generate a Confidence level; the higher the level is the more likely they are to be a spammer. If the user's level is higher than the forum's minimum level they are denied registration.

The idea behind Confidence is to prevent ham - ham being an actual user that is in SFS by mistake or is of a low spam risk - as well as spam.

### Licence
© Tim Bell, Tom Moore, 2012 onwards.

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, visit [GNU General Public License](http://www.gnu.org/licenses/) for more information.

### Installing StopForumSpam for MyBB
#### Fresh Install
If you do not have StopForumSpam installed, simply upload the plugin to your forum root (ensuring the folder structure is followed) and install via your ACP plugin manager.

#### Upgrade
##### Upgrading to 1.4.0
If you're using StopForumSpam 1.2, first deactivate the existing plugin from your ACP. Upload the new plugin, overwriting all files. Go back to the ACP and reactivate the plugin. The upgrade process will start and all your existing settings will be carried forward into the new version.

##### Upgrading to 1.4.1
If you are already using 1.4.0, download the new version and upload to your forum overwriting all files. There is no need to deactivate or uninstall the plugin.

Once upgraded, you can safely remove the file *./inc/plugins/stopforumspam_acp.php*.

### Using StopForumSpam
Once installed, a new setting group is created called StopForumSpam Check; this can be found in the Configuration area in the ACP.

* **Check User Details** - enable whether to check username, email and/or ip address against SFS
* **Confidence Level** - the minimum confidence level for your forum; if a user generates a level higher than this, they will be denied registration
* **Log Denials** - when switched to *yes*, this will log all users that have been denied registration in ./sfs_log.php
* **Failsafe** - when switched to *yes*, and SFS fails to respond to a registration check, the user will still be allowed to register

#### Confidence Level
According to SFS, Confidence is the possibility that the user is a spammer. Users who have mistakenly been added to SFS, or just happen to share the same username (for example), will typically generate a low Confidence level of up to 20%. Hardened spammers tend to have higher levels of 40% or more.

After upgrading to 1.4, if you being to experience more spam than usual, lower the Confidence level until you find a happy medium (i.e. don't just whack to 0%). A level of 0% will deny any user who features in SFS's database (which is the same functionality of 1.2).

#### Language Support
1.4 supports multiple languages via MyBB's Language system. If you have more than one language pack installed, be sure to copy ./inc/languages/english/stopforumspam.lang.php to each of them and translate as required. The *Check User Details* setting values are translated in the language file. For other settings, be sure to use the default method for translating settings.

### Support
Please visit [Xekko Resources](http://resources.xekko.co.uk/forum-11.html "Visit Xekko Resources") or [MyBB Community Forums](http://community.mybb.com) for support.