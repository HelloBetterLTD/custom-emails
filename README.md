# Custom Editable Emails for Silverstripe

### Installation

Use composer to install the module. 

`composer require silverstripers/custom-emails`

### Configuration

Define your emails first in YAML files and run a dev build. The emails objects will be 
created in the CMS (Siteconfig). You can have multiple emails defined with different identifiers.

Once you go in the CMS and configure them you can start sending emails. 

```
---
name: notifications-config
---
SilverStripers\CustomEmails\Dev\Injector:
  definitions:
    EMAIL_IDENTIFIER:
      name: 'Title of the email'
      dynamic: true # for emails which doesnt need a to address
      template: '' # Silverstripe template file to use when rendering emails. 
      arguments: # merge tags
        - Name
        - Email
```

###Sending emails

To send an email you can use the Processor classs.

```
use SilverStripers\CustomEmails\Model\NotificationEmail;

$processor = NotificationEmail::get_processor(
    'EMAIL_IDENTIFIER',
    'to@email.com',
    'from@email.com',
    [
        'Name' => 'Name',
        'Email' => 'test@test.com'
    ]
);
$processor->setAttachments([
    'file_name' => $content
]);
$processor->send();
```

The functions above can be used as this. 

```
use SilverStripers\CustomEmails\Model\NotificationEmail;

NotificationEmail::get_processor('EMAIL_IDENTIFIER')
    ->setTo('to@email.com')
    ->setFrom('from@email.com')
    ->setData([]) // data as you specify on your merge tags
    ->setAttachments([
        'file_name' => $content
    ])->send();

```
