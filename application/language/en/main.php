<?php /*
	Copyright 2014-2015 Cédric Levieux, Jérémy Collot, ArmagNet

	This file is part of Congressus.

    Congressus is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Congressus is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Congressus.  If not, see <http://www.gnu.org/licenses/>.
*/

$lang["date_format"] = "m/d/Y";
$lang["time_format"] = "H:iA";
$lang["datetime_format"] = "the {date} at {time}";

$lang["common_validate"] = "Validate";
$lang["common_delete"] = "Delete";
$lang["common_fork"] = "Fork";
$lang["common_reject"] = "Reject";
$lang["common_connect"] = "Connect";
$lang["common_ask_for_modification"] = "Ask modification";

$lang["language_fr"] = "French";
$lang["language_en"] = "English";
$lang["language_de"] = "German";

$lang["congressus_title"] = "Congressus";

$lang["menu_language"] = "Language : {language}";
$lang["menu_index"] = "Home";
$lang["menu_tweet"] = "Tweet";
$lang["menu_history"] = "History";
$lang["menu_validation"] = "Validation";
$lang["menu_timelines"] = "Timelines";
$lang["menu_mytweets"] = "My tweets";
$lang["menu_myrights"] = "My rights";
$lang["menu_mypreferences"] = "My preferences";
$lang["menu_myaccounts"] = "My accounts";
$lang["menu_logout"] = "Log out";
$lang["menu_login"] = "Log in";

$lang["login_title"] = "Log in";
$lang["login_loginInput"] = "Identifier";
$lang["login_passwordInput"] = "Password";
$lang["login_button"] = "Log in";
$lang["login_rememberMe"] = "Remember me";
$lang["register_link"] = "or sign in";
$lang["forgotten_link"] = "I forgot my password";

$lang["breadcrumb_index"] = "Home";
$lang["breadcrumb_validation"] = "Validation";
$lang["breadcrumb_seeTweetValidation"] = "Current validation";
$lang["breadcrumb_history"] = "History";
$lang["breadcrumb_timelines"] = "Timelines";
$lang["breadcrumb_mypreferences"] = "My preferences";
$lang["breadcrumb_myaccounts"] = "My accounts";
$lang["breadcrumb_myrights"] = "My rights";
$lang["breadcrumb_mypage"] = "My page";
$lang["breadcrumb_register"] = "Sign in";
$lang["breadcrumb_activation"] = "Activation";
$lang["breadcrumb_forgotten"] = "I forgot my password";
$lang["breadcrumb_about"] = "About";

$lang["index_guide"] = "Congressus is an application letting you share a tweeter account with a group of users.
The tweets can or must be validated by other users before being published.";
$lang["index_accounts"] = "Accounts";
$lang["index_tweetPlaceholder"] = "tweet...";
$lang["index_tweetButton"] = "Tweet it";
$lang["index_supports_tweet"] = "Tweet";
$lang["index_supports_facebook"] = "Facebook";
$lang["index_cutTweets_legend"] = "Automatic cutting";
$lang["index_options_mediaInput"] = "Media";
$lang["index_options_cronDateInput"] = "Delayed departure";
$lang["index_options_cronDatePlaceholder"] = "yyyy-mm-dd hh:mm";
$lang["index_options_cronDateGuide"] = "Keep it blank if departure just after validation";
$lang["index_options_validationDurationInput"] = "Maximal validation duration";
$lang["index_options_secondaryAccounts"] = "Also send to";
// $lang["index_options_validationDurationPlaceholder"] = "yyyy-mm-dd hh:mm";
// $lang["index_options_validationDurationGuide"] = "Laisser vide si départ juste après validation";
$lang["anonymous_form_nicknameInput"] = "Nickname";
$lang["anonymous_form_mailInput"] = "Mail address (following purpose)";
$lang["anonymous_form_passwordInput"] = "Password";
$lang["anonymous_form_iamabot"] = "I'm a bot and i don't know how to uncheck a checkbox";
$lang["anonymous_form_legend"] = "Informations";

$lang["add_tweet_mail_subject"] = "[OTB] Tweet requested validation";
$lang["add_tweet_mail_content"] = "Hello {login},

You're in a list of validators of the account {account}, and, a tweet is waiting for you on Congressus here is the content :

{tweet}

You can directly validate this tweet by clicking on this link below :
{validationLink}

The @Congressus Team";
$lang["add_tweet_mail_only_a_retweet"] = "Retweet proposition of :";

$lang["ask_for_modification_mail_subject"] = "[OTB] Tweet modification request";
$lang["ask_for_modification_mail_content"] = "Hello {login},

You're the author of a tweet on the account {account}, and, a validator request you to modify it.

You can modify this tweet by clicking on this link below :
{validationUrl}

The @Congressus Team";

$lang["history_guide"] = "List of tweets that have been validated.";
$lang["history_button_validators"] = "Validators";
$lang["history_account_title"] = "Tweets History for <strong><em>{account}</em></strong>";
$lang["history_cron_datetime_format"] = "Won't be emitted before the {date} at {time}";
$lang["history_retweet_proposition"] = "This a retweet proposition of :";

$lang["validation_guide"] = "List of tweets waiting for validation.";
$lang["validation_account_title"] = "Tweets for <strong><em>{account}</em></strong> in validation";
$lang["validation_anonymous"] = "(anonymous)";
$lang["validation_tooltip_author_validation"] = "Author validation";
$lang["validation_tooltip_mine_validation"] = "My validation";
$lang["validation_tooltip_other_validation"] = "Validation from other users";
$lang["validation_cron_datetime_format"] = "Won't be emitted before the {date} at {time}";
$lang["validation_duration_remaining"] = "Remaining time before expiration : {duration}";
$lang["validation_ask_modification"] = "Modification requested";
$lang["validation_retweet_proposition"] = "This a retweet proposition of :";

$lang["do_validation_error"] = "Your validation failed (already done, tweet already sent or erased)";
$lang["do_validation_ok"] = "Your tweet validation has been taken into account";

$lang["timelines_guide"] = "Your different timelines";
$lang["timelines_account_title"] = "Tweets for <strong><em>{account}</em></strong>";
$lang["timelines_search_header"] = "Tweet search";
$lang["timelines_search_label"] = "Tweet";
$lang["timelines_search_placeholder"] = "tweet id or its url";
$lang["timelines_waiting_tweets"] = "See \${numberOfTweets} new Tweets";
$lang["timelines_waiting_tweet"] = "See 1 new Tweet";
$lang["property_retweet_by"] = "RT by \${tweet_user_name} @\${tweet_user_screen_name}";

$lang["mypreferences_guide"] = "Change my preferences.";
$lang["mypreferences_form_legend"] = "Configuration of your access";
$lang["mypreferences_form_passwordInput"] = "Password";
$lang["mypreferences_form_passwordPlaceholder"] = "the password of your connection";
$lang["mypreferences_form_languageInput"] = "Language";
$lang["mypreferences_form_mailInput"] = "Mail address";
$lang["mypreferences_form_notificationInput"] = "Validation notification";
$lang["mypreferences_form_notification_none"] = "None";
$lang["mypreferences_form_notification_mail"] = "By mail";
$lang["mypreferences_form_notification_simpledm"] = "By simple DM";
$lang["mypreferences_form_notification_dm"] = "By multiple DM";
$lang["mypreferences_validation_mail_empty"] = "The mail field can't be empty";
$lang["mypreferences_validation_mail_not_valid"] = "This mail is not a valid mail";
$lang["mypreferences_validation_mail_already_taken"] = "This mail is already taken";
$lang["mypreferences_save"] = "Save my preferences";

$lang["myaccounts_guide"] = "Set my accounts.";
$lang["myaccounts_newaccount_form_legend"] = "New account configuration";
$lang["myaccounts_existingaccount_form_legend"] = "Account configuration for <em>{account}</em>";
$lang["myaccounts_account_form_nameInput"] = "Account name";
$lang["myaccounts_account_form_anonymousPermitted"] = "Anonymous tweet proposition permitted";
$lang["myaccounts_account_form_anonymousPasswordInput"] = "Anonymous password";
$lang["myaccounts_account_form_validationScoreInput"] = "Tweet score validation";
$lang["myaccounts_twitter_form_legend"] = "Twitter Configuration";
$lang["myaccounts_twitter_form_apiKeyInput"] = "API Key";
$lang["myaccounts_twitter_form_apiSecretInput"] = "API Secret";
$lang["myaccounts_twitter_form_accessTokenInput"] = "Access Token";
$lang["myaccounts_twitter_form_accessTokenSecretInput"] = "Access Token Secret";
$lang["myaccounts_facebook_page_form_legend"] = "Facebook Page Configuration";
$lang["myaccounts_facebook_page_form_pageIdInput"] = "Page id";
$lang["myaccounts_facebook_page_form_fpAccessTokenInput"] = "Page Access Token";
$lang["myaccounts_facebook_page_form_applicationIdInput"] = "Application Id";
$lang["myaccounts_facebook_page_form_applicationSecretKeyInput"] = "Application Secret Key";
$lang["myaccounts_facebook_page_form_shortLiveUserAccessTokenInput"] = "Short live User Access Token";
$lang["myaccounts_facebook_page_form_createFacebookPageAccessTokenButton"] = "Create a Page Access Token";
$lang["myaccounts_administrators_form_legend"] = "Administrators management";
$lang["myaccounts_administrators_form_addUserInput"] = "User";
$lang["myaccounts_validators_form_legend"] = "Validators management";
$lang["myaccounts_validators_form_groupNameInput"] = "Group name";
$lang["myaccounts_validators_form_groupScoreInput"] = "Score";
$lang["myaccounts_validators_form_addUserInput"] = "Utilisateur";
$lang["myaccounts_validators_form_deleteGroupInput"] = "Delete group";
$lang["myaccounts_validators_form_addGroupInput"] = "Add groupe";
$lang["myaccount_button_testTwitter"] = "Test";
$lang["myaccount_add"] = "Add this account";
$lang["myaccount_save"] = "Save the parameters";

$lang["myrights_guide"] = "A rights review.";
$lang["myrights_scores_legend"] = "My possible validations";
$lang["myrights_scores_no_score"] = "You have no power of validation";
$lang["myrights_scores_my_score"] = "Your validation power";
$lang["myrights_scores_validation_score"] = "The needed validation points";
$lang["myrights_administration_legend"] = "My administrated accounts";
$lang["myrights_scores_no_adminstation"] = "You have no power of administration";

$lang["mypage_guide"] = "This a page compiling your statistics";
$lang["mypage_tweets_legend"] = "My tweets";
$lang["mypage_validations_legend"] = "My validations";
$lang["mypage_scores_legend"] = "My scores";
$lang["mypage_tweet_and_validations_chart_legend"] = "My tweets and validations in time";
$lang["mypage_tweet_and_validations_chart_axisY"] = "Quantity";
$lang["mypage_score_chart_axisY"] = "Score";
$lang["mypage_tweet_and_validations_chart_axisX"] = "Date";
$lang["mypage_tweet_and_validations_chart_formatDate"] = "MM/DD/YYYY";
$lang["mypage_tweet_and_validations_chart_jsFormatDate"] = "(date.getMonth() < 9 ? '0' : '') + (date.getMonth() + 1) + '/' + (date.getDate() < 10 ? '0' : '') + date.getDate() + '/' + date.getFullYear()";

$lang["property_tweet"] = "Tweet";
$lang["property_author"] = "Author";
$lang["property_date"] = "Date";
$lang["property_validators"] = "Validators";
$lang["property_validation"] = "Validation";
$lang["property_actions"] = "Actions";
$lang["property_supports"] = "Supports";

$lang["register_guide"] = "Welcome to the register page of Congressus";
$lang["register_form_legend"] = "Configuration of your access";
$lang["register_form_loginInput"] = "Login";
$lang["register_form_loginHelp"] = "Preferably use your Twitter ID if you want to receive notifications on Twitter";
$lang["register_form_mailInput"] = "Mail address";
$lang["register_form_passwordInput"] = "Password";
$lang["register_form_passwordHelp"] = "Your password doesn't have to inevitably contain strange characters, but it should preferably be long and memorizable";
$lang["register_form_confirmationInput"] = "Password confirmation";
$lang["register_form_languageInput"] = "Language";
$lang["register_form_iamabot"] = "I'm a bot and i don't know how to uncheck a checkbox";
$lang["register_form_notificationInput"] = "Validation notification";
$lang["register_form_notification_none"] = "None";
$lang["register_form_notification_mail"] = "By mail";
$lang["register_form_notification_simpledm"] = "By simple DM";
$lang["register_form_notification_dm"] = "By multiple DM";
$lang["register_success_title"] = "Successful sign in";
$lang["register_success_information"] = "Your registration is done.
<br>You will soon receive a mail with a link to click letting you activate your account.";
$lang["register_mail_subject"] = "[OTB] Registration mail";
$lang["register_mail_content"] = "Hello {login},

It seems that you registered yourself on Congressus. To confirm your registration, please click the link below :
{activationUrl}

The @Congressus Team";
$lang["register_save"] = "Sign in";
$lang["register_validation_user_empty"] = "The user field can't be empty";
$lang["register_validation_user_already_taken"] = "This username is already taken";
$lang["register_validation_mail_empty"] = "The mail field can't be empty";
$lang["register_validation_mail_not_valid"] = "This mail is not a valid mail";
$lang["register_validation_mail_already_taken"] = "This mail is already taken";
$lang["register_validation_password_empty"] = "The password field can't be empty";

$lang["activation_guide"] = "Welcome on the activation screen of your user account";
$lang["activation_title"] = "Activation status";
$lang["activation_information_success"] = "The activation of your user account succeeded. You can now <a id=\"connectButton\" href=\"#\">sign-in</a> yourself.";
$lang["activation_information_danger"] = "The activation of your user account failed.";

$lang["forgotten_guide"] = "You forgot your password, welcome on the page that will let you recover your access";
$lang["forgotten_form_legend"] = "Access retrieving";
$lang["forgotten_form_mailInput"] = "Mail address";
$lang["forgotten_save"] = "Send me a mail !";
$lang["forgotten_success_title"] = "Recory in progress";
$lang["forgotten_success_information"] = "An email has been sent.<br>This email contains a new password. Be sure to change it as soon as possible.";
$lang["forgotten_mail_subject"] = "[PERSONAE] I Forgot my password";
$lang["forgotten_mail_content"] = "Hello,

It seems that you forgot your password on Congressus. Your new password is {password} .
Please change it as soon as you are connected.

The @Congressus Team";

$lang["okTweet"] = "Your tweet is gone in validation";
$lang["koTweet"] = "Problem in the handling of your tweet";
$lang["okDeleteTweet"] = "Your tweet has been deleted";
$lang["okAskForModificationTweet"] = "A modification has been asked for this tweet";
$lang["okValidateTweet"] = "Your tweet validation has been taken into account";
$lang["okRejectTweet"] = "Your tweet rejection has been taken into account";
$lang["okFinalValidateTweet"] = "Your tweet validation has been taken into account, and the tweet has been completly validated";
$lang["error_cant_change_password"] = "The password change failed";
$lang["ok_operation_success"] = "Succeeded operation";
$lang["error_passwords_not_equal"] = "Your password and its confirmation are different";
$lang["error_cant_send_mail"] = "Congressus can not send mail to your mail address";
$lang["error_cant_register"] = "Congressus can not process your registration";
$lang["error_cant_delete_files"] = "Congressus can not delete delete installation files";
$lang["error_cant_connect"] = "Impossible to connect to the database";
$lang["error_database_already_exists"] = "The database already exists";
$lang["error_database_dont_exist"] = "The database does not exist";
$lang["error_login_ban"] = "Your IP has been blocked for 10mn.";
$lang["error_login_bad"] = "Vérifier vos identifiants, l'identification a échouée.";
$lang["ok_twitter_success"] = "The Twitter configuration works";
$lang["error_twitter_cant_authenticate"] = "The Twitter configuration doesn't work, verify the differents connection parameters";
$lang["error_media_typeError"] = "The uploaded file must be an image";
$lang["error_media_sizeError"] = "The uploaded file is too large (maximum size : <span id='maxSize'></span>)";
$lang["error_media_defaultError"] = "An error occurred in the processing of a file upload, please try again later";

$lang["install_guide"] = "Welcome on the installation page of Congressus.";
$lang["install_tabs_database"] = "Database";
$lang["install_tabs_mail"] = "Mail";
$lang["install_tabs_application"] = "Application";
$lang["install_tabs_final"] = "Finalization";
$lang["install_tabs_license"] = "License";
$lang["install_database_form_legend"] = "Database access configuration";
$lang["install_database_hostInput"] = "Host";
$lang["install_database_hostPlaceholder"] = "database server host";
$lang["install_database_portInput"] = "Port";
$lang["install_database_portPlaceholder"] = "database server port";
$lang["install_database_loginInput"] = "Login";
$lang["install_database_loginPlaceholder"] = "Connection identifier";
$lang["install_database_loginHelp"] = "<em>Root</em> is avoided";
$lang["install_database_passwordInput"] = "Password";
$lang["install_database_passwordPlaceholder"] = "Connection password";
$lang["install_database_databaseInput"] = "Database";
$lang["install_database_databasePlaceholder"] = "database name";
$lang["install_database_operations"] = "Operations";
$lang["install_database_saveButton"] = "Save configuration";
$lang["install_database_pingButton"] = "Ping";
$lang["install_database_createButton"] = "Create";
$lang["install_database_deployButton"] = "Deploy";
$lang["install_mail_form_legend"] = "Mail access configuration";
$lang["install_mail_hostInput"] = "Host";
$lang["install_mail_hostPlaceholder"] = "Mail server host";
$lang["install_mail_portInput"] = "Port";
$lang["install_mail_portPlaceholder"] = "Mail server port";
$lang["install_mail_usernameInput"] = "Username";
$lang["install_mail_usernamePlaceholder"] = "Connection identifier";
$lang["install_mail_passwordInput"] = "Password";
$lang["install_mail_passwordPlaceholder"] = "Connection password";
$lang["install_mail_fromMailInput"] = "Sender address";
$lang["install_mail_fromMailPlaceholder"] = "Sender address";
$lang["install_mail_fromNameInput"] = "Sender name";
$lang["install_mail_fromNamePlaceholder"] = "sender name";
$lang["install_mail_testMailInput"] = "Test address";
$lang["install_mail_testMailPlaceholder"] = "not saved";
$lang["install_mail_operation"] = "Operations";
$lang["install_mail_saveButton"] = "Save configuration";
$lang["install_mail_pingButton"] = "Ping";
$lang["install_application_form_legend"] = "Application url";
$lang["install_application_baseUrlInput"] = "Application base url";
$lang["install_application_cronEnabledInput"] = "Allow sending deferred tweet";
$lang["install_application_cronEnabledHelp"] = "Please add in your cron table the following command <pre>* * * * * cd {path} && php do_cron.php</pre>";
$lang["install_application_saltInput"] = "Salt";
$lang["install_application_saltPlaceholder"] = "Application salt for ciphering and hashing";
$lang["install_application_defaultLanguageInput"] = "Default language";
$lang["install_application_operation"] = "Operations";
$lang["install_application_saveButton"] = "Save configuration";
$lang["install_autodestruct_guide"] = "You have tested everything, everything configured ? Then clicking <em>autodestruction</em> to remove this installer.";
$lang["install_autodestruct"] = "Autodestruction";

$lang["about_footer"] = "About";
$lang["congressus_footer"] = "<a href=\"https://www.congressus.net/\" target=\"_blank\">Congressus</a> is an application provided by <a href=\"https://www.partipirate.org\" target=\"_blank\">Parti Pirate</a>";
?>